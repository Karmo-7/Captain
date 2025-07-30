<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Profile extends Model
{
    use HasFactory;


    protected $fillable =
     [
    'user_id',
    'first_name',
    'last_name',
    'birthdate',
    'address',
    'phone_number',
    'gender',
    'height',
    'weight',
    'emergency_contact_information',
    'injuries',
    'positions_played',
    'notable_achievements',
    'years_of_experience',
    'previous_teams',
    'extra_notes',
    'avatar',
    'national_id',

];

    public function user(){
        return $this->belongsTo(User::class);
    }

     public function team()
    {
        return $this->belongsTo(\Modules\Team\Entities\Team::class);
    }
}
