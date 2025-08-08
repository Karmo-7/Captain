<?php

namespace Modules\Stadium\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StadiumRequestForm extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'sport_id' => 'required|exists:sports,id',
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'description' => 'required|string',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048', // لكل صورة
            'Length' => 'required|numeric|min:1',
            'Width' => 'required|numeric|min:1',
            'owner_number' => 'required|numeric|digits_between:7,15',
            'start_time' => 'required|string|max:45',
            'end_time' => 'required|string|max:45',
             'price' => 'string|max:255',
            'price' => 'required|numeric|min:0',
             'deposit' => 'required|numeric|min:0',,
        ];
    }

    public function messages(): array
    {
        return [
            'photos.*.image' => 'Each file must be an image.',
            'photos.*.max' => 'Each image must not exceed 2MB.',
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
