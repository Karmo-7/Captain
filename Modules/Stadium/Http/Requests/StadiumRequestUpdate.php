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
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'Length' => 'numeric|min:1',
            'Width' => 'numeric|min:1',
            'owner_number' => 'numeric|digits_between:7,15',
            'start_time' => 'date_format:H:i',
            'end_time' => 'date_format:H:i|after:start_time',
            'price' => 'numeric|min:0',
            'deposit' => 'numeric|min:0',
            'duration' => 'numeric|min:1',
            'latitude' => 'numeric|between:-90,90',
            'longitude' => 'numeric|between:-180,180',
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
