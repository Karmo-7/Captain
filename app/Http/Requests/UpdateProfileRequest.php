<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => 'string|max:255',
            'last_name' => 'string|max:255',
            'birthdate' => 'date',
            'address' => 'string',
            'Sport' => 'string',
            'phone_number' => 'unique:profiles|numeric|digits_between:7,15',
            'gender' => 'in:male,female',
            'height' => 'integer|min:0',
            'weight' => 'integer|min:0',
            'positions_played' => 'string',
            'avatar' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'years_of_experience' => 'integer|min:0',
            'notable_achievements' => 'string',
            'previous_teams' => 'string',



        ];
    }
}
