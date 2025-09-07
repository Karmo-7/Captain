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
        $userId = auth()->id();
        $validated = $request->validated();
        $validated['user_id'] = $userId;

        // جِب الحجز مع معلومات الاستاد وصاحب الاستاد
        $booking = StadiumSlotBooking::with(['stadium.user'])->findOrFail($request->stadium_slot_booking_id);

        // إذا الدفع مكتمل، ممنوع إعادة إنشاء دفعة
        if ($booking->payment_status === 'completed') {
            return response()->json([
                'status' => false,
                'message' => 'This booking already processed payment.',
            ], 409);
        }

        // التحقق من قيمة الدفعة بحسب نوع الدفع
        $price = (float) $booking->stadium->price;
        $deposit = (float) $booking->stadium->deposit;
        $alreadyPaid = (float) $booking->amount_paid;
        $remaining = $price - $alreadyPaid;
        $amount = (float) $request->amount;

        if ($booking->payment_type === 'full') {
            if ($amount != $price) {
                return response()->json([
                    'status' => false,
                    'message' => 'Full payment must equal stadium price: ' . $price,
                ], 422);
            }
        } elseif ($booking->payment_type === 'deposit') {
            if ($alreadyPaid == 0) {
                if ($amount != $deposit) {
                    return response()->json([
                        'status' => false,
                        'message' => 'First payment must equal deposit: ' . $deposit,
                    ], 422);
                }
            } else {
                if ($amount != $remaining) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Final payment must equal the remaining amount: ' . $remaining,
                    ], 422);
                }
            }
        }

        $stadiumOwner = $booking->stadium->user; // ممكن يكون null بحسابات تجريبية
        $player = auth()->user();

        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));

            // أنشئ Stripe Customer للّاعب إذا غير موجود
            if (empty($player->stripe_customer_id)) {
                $customer = \Stripe\Customer::create([
                    'email' => $player->email,
                ]);
                $player->update(['stripe_customer_id' => $customer->id]);
            }

            // إنشاء PaymentIntent (لـ PaymentSheet على التطبيق)
            $params = [
                'amount' => (int) round($amount * 100), // بالسنت
                'currency' => 'usd',
                'customer' => $player->stripe_customer_id,
                'payment_method_types' => ['card'], // متوافق مع PaymentSheet
                'metadata' => [
                    'booking_id' => (string) $booking->id,
                    'user_id' => (string) $userId,
                ],
            ];

            // في حال كان صاحب الملعب لديه حساب Stripe متصل، فعّل تحويل الحصة
            if (!empty($stadiumOwner) && !empty($stadiumOwner->stripe_account_id)) {
                $params['transfer_data'] = [
                    'destination' => $stadiumOwner->stripe_account_id,
                ];
                $params['on_behalf_of'] = $stadiumOwner->stripe_account_id;
                // (اختياري) عمولة المنصّة:
                // $params['application_fee_amount'] = 200; // بالسنت
            }

            $pi = PaymentIntent::create($params);

            // سجّل الدفعية بقاعدة البيانات كـ pending (الوضع الطبيعي ينتظر Webhook)
            $payment = Payment::create([
                'stadium_slot_booking_id' => $booking->id,
                'amount' => $amount,
                'status' => 'pending',
                'transaction_id' => $pi->id,
                'expires_at' => now()->addMinutes(10),
            ]);

            // أضف payment_id للـ metadata ليسهّل ربط الويبهوك (حتى لو شغّلنا المحاكاة)
            try {
                PaymentIntent::update($pi->id, [
                    'metadata' => [
                        'booking_id' => (string) $booking->id,
                        'payment_id' => (string) $payment->id,
                        'user_id' => (string) $userId,
                    ],
                ]);
            } catch (\Exception $e) {
                \Log::warning('[STRIPE] Failed to update PI metadata', ['e' => $e->getMessage()]);
            }

            // ========= MOCK MODE (بدون Webhook) =========
            $mock = filter_var(env('PAYMENT_MOCK_MODE', false), FILTER_VALIDATE_BOOLEAN);
            if ($mock) {
                // اعتبر الدفع ناجح فوراً وحدّث DB
                $payment->status = 'succeeded';
                $payment->save();

                $paidBefore = (float) $booking->amount_paid;
                $newPaid = $paidBefore + $amount;

                $booking->amount_paid = number_format($newPaid, 2, '.', '');
                $booking->payment_status = ($booking->payment_type === 'full' || $newPaid >= $price)
                    ? 'completed'
                    : 'partial';
                $booking->save();

                \Log::info('[PAY][MOCK] Marked succeeded', [
                    'booking_id' => $booking->id,
                    'payment_id' => $payment->id,
                    'amount' => $amount,
                    'status' => $booking->payment_status,
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Payment mocked as succeeded.',
                    'data' => [
                        'booking' => $booking,
                        'payment' => $payment,
                        'client_secret' => $pi->client_secret, // بيضل ممكن تستخدمه للـ UI
                        'mocked' => true,
                    ],
                ], 201);
            }
            // ========= END MOCK =========

            // الوضع الحقيقي: رجّع client_secret والتحديث يصير عبر Webhook
            return response()->json([
                'status' => true,
                'message' => 'Payment initiated, waiting for confirmation.',
                'data' => [
                    'booking' => $booking,
                    'payment' => $payment,
                    'client_secret' => $pi->client_secret,
                    'mocked' => false,
                ],
            ], 201);

        } catch (\Exception $e) {
            // في حال فشل إنشاء الـ PI، يمكنك إلغاء الحجز (حسب لوجيكك)
            $booking->update(['status' => 'cancelled']);
            \Log::error('[PAY][ERROR] ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function forceComplete(Request $request)
    {
        $data = $request->validate([
            'booking_id' => 'required|integer|exists:stadium_slot_bookings,id',
            'transaction_id' => 'nullable|string',   // pi_...
            'amount' => 'required|numeric|min:0.5',
        ]);

        $booking = StadiumSlotBooking::with('stadium')->findOrFail($data['booking_id']);
        $amount = (float) $data['amount'];

        // لقَط آخر Payment مربوط بالحجز (أو حسب transaction_id إن وصلتنا)
        $paymentQuery = Payment::where('stadium_slot_booking_id', $booking->id)->orderByDesc('id');
        if (!empty($data['transaction_id'])) {
            $paymentQuery->where('transaction_id', $data['transaction_id']);
        }
        $payment = $paymentQuery->first();

        if (!$payment) {
            // لو ما لقينا Payment (نادرًا)، أنشئ واحدًا سريعًا
            $payment = Payment::create([
                'stadium_slot_booking_id' => $booking->id,
                'amount' => $amount,
                'status' => 'pending',
                'transaction_id' => $data['transaction_id'] ?? null,
                'expires_at' => now()->addMinutes(10),
            ]);
        }

        // عدّل حالة الدفع إلى succeeded + صحّح المبلغ
        $payment->update([
            'status' => 'succeeded',
            'amount' => $amount,
        ]);

        // حدّث الحجز
        $price = (float) $booking->stadium->price;
        $paidNew = (float) $booking->amount_paid + $amount;

        $newStatus = 'pending';
        if ($booking->payment_type === 'full' || $paidNew >= $price) {
            $newStatus = 'completed';
        } elseif ($paidNew > 0) {
            $newStatus = 'partial';
        }

        $booking->update([
            'amount_paid' => number_format($paidNew, 2, '.', ''),
            'payment_status' => $newStatus,
        ]);

        \Log::info('[PAY][FORCE] Updated booking via force-complete', [
            'booking_id' => $booking->id,
            'payment_id' => $payment->id,
            'tx' => $payment->transaction_id,
            'amount' => $amount,
            'status' => $newStatus,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Payment forced as succeeded.',
            'data' => [
                'booking' => $booking,
                'payment' => $payment,
            ],
        ]);
    }

}

// class PaymentController extends Controller
// {
//         public function pay(PaymentRequest $request)
//         {
//             $user_id = auth()->id();
//             $validated = $request->validated();
//             $validated['user_id'] = $user_id;

//             $booking = StadiumSlotBooking::with('stadium')->findOrFail($request->stadium_slot_booking_id);

//         // إذا الحجز مدفوع بالكامل
//         if ($booking->payment_status === 'completed') {
//             return response()->json([
//                 'status' => false,
//                 'message' => 'This booking already processed payment.',
//             ], 409);
//         }

//         // حساب المبلغ المتبقي
//         $remainingAmount = $booking->stadium->price - $booking->amount_paid;

//         // التحقق من نوع الدفع
//         if ($booking->payment_type === 'full') {
//             if ($request->amount != $booking->stadium->price) {
//                 return response()->json([
//                     'status' => false,
//                     'message' => 'Full payment must equal stadium price: ' . $booking->stadium->price,
//                 ], 422);
//             }
//         } elseif ($booking->payment_type === 'deposit') {
//             if ($booking->amount_paid == 0) {
//                 // أول دفعة: لازم مساوية للعربون
//                 if ($request->amount != $booking->stadium->deposit) {
//                     return response()->json([
//                         'status' => false,
//                         'message' => 'First payment must equal deposit: ' . $booking->stadium->deposit,
//                     ], 422);
//                 }
//             } else {
//                 // دفعة ثانية: لازم تغطي المبلغ المتبقي
//                 if ($request->amount != $remainingAmount) {
//                     return response()->json([
//                         'status' => false,
//                         'message' => 'Final payment must equal the remaining amount: ' . $remainingAmount,
//                     ], 422);
//                 }
//             }
//         }
//         $stadiumOwner = $booking->stadium->user;
//         $player=auth()->user();

//         try {
//             Stripe::setApiKey(env('STRIPE_SECRET'));
//             // إنشاء PaymentIntent
//             $paymentIntent = PaymentIntent::create([
//                 'amount' => $request->amount * 100, // المبلغ بالسنت
//                 'currency' => 'usd',
//                 'customer' => $player->stripe_customer_id,
//                 'payment_method_types' => ['card'],
//                 'transfer_data' => [
//                     'destination' => $stadiumOwner->stripe_account_id,
//                 ],
//                 'on_behalf_of' => $stadiumOwner->stripe_account_id,

//             ]);



//             // تسجيل الدفع بقاعدة البيانات
//             $payment = Payment::create([
//                 'stadium_slot_booking_id' => $booking->id,
//                 'amount' => $request->amount,
//                 'status' => 'pending',
//                 'transaction_id' => $paymentIntent->id,
//                 'expires_at' => now()->addMinutes(1),
//             ]);

//             return response()->json([
//                 'status' => true,
//                 'message' => 'Payment initiated, waiting for confirmation.',
//                 'data' => [
//                     'booking' => $booking,
//                     'payment' => $payment,
//                     'client_secret' => $paymentIntent->client_secret,
//                 ],
//             ], 201);

//         } catch (\Exception $e) {
//             // فشل الإنشاء → نلغي الحجز
//             $booking->update(['status' => 'cancelled']);
//             return response()->json([
//                 'status' => false,
//                 'message' => $e->getMessage(),
//             ], 500);
//         }
//     }




// }
