<?php

namespace Modules\Sport\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class updaterequestsport extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'string|max:255|unique:sports',
            'photo' => 'image|mimes:jpeg,png,jpg|max:2048',
            'max_players_per_team' => 'integer|min:0',
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
