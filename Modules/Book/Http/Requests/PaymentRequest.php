<?php

namespace Modules\Book\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class paymentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'stadium_slot_booking_id' => 'required|exists:stadium_slot_bookings,id',
            'amount' => 'required|numeric|min:1',
            'status' => 'in:pending,succeeded,failed',
            'transaction_id' => 'nullable|string', // إذا Stripe رجع Transaction ID
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
