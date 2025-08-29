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
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'birthdate' => 'nullable|date',
            'address' => 'nullable|string',
            'Sport' => 'nullable|string',
            'phone_number' => 'nullable|numeric|digits_between:7,15|unique:profiles,phone_number,' . $this->id,
            'gender' => 'nullable|in:male,female',
            'height' => 'nullable|integer|min:0',
            'weight' => 'nullable|integer|min:0',
            'positions_played' => 'nullable|string',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'years_of_experience' => 'nullable|integer|min:0',
            'notable_achievements' => 'nullable|string',
            'previous_teams' => 'nullable|string',
        ];
    }

}
