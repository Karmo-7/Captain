<?php

namespace Modules\Book\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'stadium_slot_id' => 'required|exists:stadium_slots,id',
            'stadium_id' => 'required|exists:stadiums,id',
            'date' => 'required|date|after_or_equal:today',
            'payment_type' => 'required|string|in:full,deposit',
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
