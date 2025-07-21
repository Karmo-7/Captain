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
            'Sport' => 'required|string',
            'phone_number' => 'required|unique:profiles|numeric|digits_between:7,15',
            'gender' => 'required|in:male,female',
            'height' => 'required|integer|min:0',
            'weight' => 'required|integer|min:0',
            'positions_played' => 'required|string',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'years_of_experience' => 'required|integer|min:0',
            'notable_achievements' => 'nullable|string',
            'previous_teams' => 'nullable|string',


        ];
    }
}
