<?php

namespace Modules\Stadium\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StadiumRequestUpdate extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'sport_id' => 'exists:sports,id',
            'name' => 'string|max:255',
            'location' => 'string|max:255',
            'description' => 'string',
            'Length' => 'numeric|min:1',
            'Width' => 'numeric|min:1',
            'owner_number' => 'numeric|digits_between:7,15',
            'price' => 'string|max:255',
            'price' => 'required|numeric|min:0',
             'deposit' => 'required|numeric|min:0',
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
