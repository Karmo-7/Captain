<?php

namespace Modules\Sport\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class requestsport extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'=>'required|string|max:255|unique:sports',
            'photo'=>'required|image|mimes:jpeg,png,jpg|max:2048',
            'max_players_per_team'=>'required|integer|min:0',
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
