<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
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
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birthdate' => 'required|date',
            'address' => 'required|string',
            'phone_number' => 'required|unique:profiles,phone_number,integer',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'nationality' => 'required|string',
            'gender' => 'required|in:male,female',
            'height' => 'required|integer|min:0',
            'weight' => 'required|integer|min:0',
            'Sport' => 'required|string',
            'emergency_contact_information' => 'nullable|string',
            'injuries' => 'nullable|string',
            'positions_played' => 'required|string',
            'notable_achievements' => 'nullable|string',
            'years_of_experience' => 'required|integer|min:0',
            'previous_teams' => 'nullable|string',
            'extra_notes' => 'nullable|string',


        ];
    }
}
