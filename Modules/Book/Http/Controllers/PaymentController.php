<?php

namespace Modules\Book\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Book\Entities\Payment;
use Modules\Book\Entities\StadiumSlotBooking;
use Modules\Book\Http\Requests\paymentRequest;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Carbon\Carbon;

class PaymentController extends Controller
{
        public function pay(PaymentRequest $request)
        {
            $user_id = auth()->id();
            $validated = $request->validated();
            $validated['user_id'] = $user_id;

            $booking = StadiumSlotBooking::with('stadium')->findOrFail($request->stadium_slot_booking_id);

        // إذا الحجز مدفوع بالكامل
        if ($booking->payment_status === 'completed') {
            return response()->json([
                'status' => false,
                'message' => 'This booking already processed payment.',
            ], 409);
        }

        // حساب المبلغ المتبقي
        $remainingAmount = $booking->stadium->price - $booking->amount_paid;

        // التحقق من نوع الدفع
        if ($booking->payment_type === 'full') {
            if ($request->amount != $booking->stadium->price) {
                return response()->json([
                    'status' => false,
                    'message' => 'Full payment must equal stadium price: ' . $booking->stadium->price,
                ], 422);
            }
        } elseif ($booking->payment_type === 'deposit') {
            if ($booking->amount_paid == 0) {
                // أول دفعة: لازم مساوية للعربون
                if ($request->amount != $booking->stadium->deposit) {
                    return response()->json([
                        'status' => false,
                        'message' => 'First payment must equal deposit: ' . $booking->stadium->deposit,
                    ], 422);
                }
            } else {
                // دفعة ثانية: لازم تغطي المبلغ المتبقي
                if ($request->amount != $remainingAmount) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Final payment must equal the remaining amount: ' . $remainingAmount,
                    ], 422);
                }
            }
        }
        $stadiumOwner = $booking->stadium->user;
        $player=auth()->user();
        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));
            // إنشاء PaymentIntent
            $paymentIntent = PaymentIntent::create([
                'amount' => $request->amount * 100, // المبلغ بالسنت
                'currency' => 'usd',
                'customer' => $player->stripe_customer_id,
                'payment_method_types' => ['card'],
                'transfer_data' => [
                    'destination' => $stadiumOwner->stripe_account_id,
                ],
                'on_behalf_of' => $stadiumOwner->stripe_account_id,
                // إذا بدك تضيف عمولة للمنصة
                // 'application_fee_amount' => 200,
            ]);

            // تسجيل الدفع بقاعدة البيانات
            $payment = Payment::create([
                'stadium_slot_booking_id' => $booking->id,
                'amount' => $request->amount,
                'status' => 'pending',
                'transaction_id' => $paymentIntent->id,
                'expires_at' => now()->addMinutes(1),
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Payment initiated, waiting for confirmation.',
                'data' => [
                    'booking' => $booking,
                    'payment' => $payment,
                    'client_secret' => $paymentIntent->client_secret,
                ],
            ], 201);

        } catch (\Exception $e) {
            // فشل الإنشاء → نلغي الحجز
            $booking->update(['status' => 'cancelled']);
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }




}
