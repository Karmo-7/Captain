<?php

namespace Modules\Book\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Book\Entities\Payment;
use Stripe\Stripe;
use Stripe\Account;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sig_header,
                env('STRIPE_WEBHOOK_SECRET')
            );
        } catch (\Exception $e) {
            \Log::error('Stripe webhook error: ' . $e->getMessage());
            return response('Invalid payload', 400);
        }

        $paymentIntent = $event->data->object ?? null;

        if (!$paymentIntent) {
            return response('No payment intent found', 400);
        }

        $payment = Payment::where('transaction_id', $paymentIntent->id)->first();

        if (!$payment) {
            return response('Payment not found', 200);
        }
        \Log::info('Stripe event type: ' . $event->type);

        switch ($event->type) {
            case 'payment_intent.succeeded':
                $payment->update(['status' => 'succeeded']);
                $booking = $payment->booking;

                $newAmountPaid = $booking->amount_paid + $payment->amount;
                if ($newAmountPaid >= $booking->stadium->price) {
                    $booking->update([
                        'amount_paid' => $newAmountPaid,
                        'payment_status' => 'completed',
                    ]);
                } else {
                    $booking->update([
                        'amount_paid' => $newAmountPaid,
                        'payment_status' => 'partial',
                    ]);
                }
                \Log::info('Payment updated to succeeded for ID: ' . $payment->id);

                break;

            case 'payment_intent.payment_failed':
                $payment->update(['status' => 'failed']);
                $payment->booking->update(['status' => 'cancelled']);
                break;

            case 'payment_intent.canceled':
                $payment->update(['status' => 'failed']);
                $payment->booking->update(['status' => 'cancelled']);
                break;

            default:
                \Log::info("Unhandled Stripe event: {$event->type}");
                break;
        }

        return response('Webhook handled', 200);
    }
    public function refreshOnboarding()
    {
        return response()->json([
            'message' => 'Onboarding process was refreshed. Please try again from your app.'
        ]);
    }

    public function handleOnboardingReturn(Request $request)
    {
        $accountId = $request->query('account'); // أرسل account ID في الرابط

        if (!$accountId) {
            return response()->json(['message' => 'Missing account ID'], 400);
        }

        $user = User::where('stripe_account_id', $accountId)->first();

        if (!$user) {
            return response()->json(['message' => 'User or Stripe account not found'], 404);
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));
        $account = Account::retrieve($user->stripe_account_id);

        if ($account->charges_enabled && $account->payouts_enabled) {
            $user->update(['stripe_ready' => true]);
            return response()->json([
                'message' => 'Stripe onboarding completed successfully!',
                'status' => 'ready'
            ]);
        }

        return response()->json([
            'message' => 'Stripe account not ready yet. Please complete all steps.',
            'status' => 'pending'
        ], 400);
    }
}
