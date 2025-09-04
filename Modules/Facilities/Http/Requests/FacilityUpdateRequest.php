<?php

namespace Modules\Facilities\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FacilityUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'stadium_id' => 'exists:stadiums,id',
            'name' => [
                'in:Toilets,Reception,Buffet,Cafeteria,Sports Equipment,Locker Rooms,First Aid Room,Parking,Wi-Fi,Spectator Seats,Display Screen,Sound System,Night Lighting'
            ],
            'quantity' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
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
