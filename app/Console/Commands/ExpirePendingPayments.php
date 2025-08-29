<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Book\Entities\Payment;
use Carbon\Carbon;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class ExpirePendingPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire pending payments after 10 minutes and cancel them on Stripe';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $payments = Payment::where('status', 'pending')
            ->where('expires_at', '<', $now)
            ->get();

        // Stripe secret key
        Stripe::setApiKey(env('STRIPE_SECRET'));

        foreach ($payments as $payment) {
            // أوقف الدفع في DB
            $payment->update(['status' => 'failed']);
            $payment->booking->update(['status' => 'cancelled']);
            // أوقف الـ PaymentIntent من Stripe
            try {
                $intent = PaymentIntent::retrieve($payment->transaction_id);
                $intent->cancel();
                $this->info("PaymentIntent {$payment->transaction_id} cancelled on Stripe.");
            } catch (\Exception $e) {
                \Log::error("Stripe cancel failed for {$payment->transaction_id}: " . $e->getMessage());
            }
        }

        $this->info($payments->count() . ' pending payments expired.');
    }
}
