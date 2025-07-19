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
            'phone_number' => 'string|unique:profiles,phone_number',
            'gender' => 'in:male,female',
            'height' => 'integer|min:0',
            'weight' => 'integer|min:0',
            'sport'=>'string',
            'emergency_contact_information' => 'string',
            'injuries' => 'string',
            'positions_played' => 'string',
            'notable_achievements' => 'string',
            'years_of_experience' => 'integer|min:0',
            'previous_teams' => 'string',
            'extra_notes' => 'string',
            'avatar' => 'image|mimes:jpg,jpeg,png,webp|max:2048',

        ];
    }
}
