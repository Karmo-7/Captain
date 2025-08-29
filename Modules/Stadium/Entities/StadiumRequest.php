<?php

namespace Modules\Stadium\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Sport\Entities\Sport;

class StadiumRequest extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'sport_id', 'name', 'location', 'description', 'photos',
     'Length', 'Width', 'owner_number','start_time','end_time','status','admin_notes','price',
     'deposit','duration','latitude', 'longitude'];

    protected $casts = [
        'photos' => 'array',
        'sport_id' => 'integer',
        'owner_number' => 'integer',
        'duration' => 'integer',
        'price' => 'float',
        'deposit' => 'float',
        'latitude' => 'float',
        'longitude' => 'float',
    ];
    public function user(){
        return $this->belongsTo(User::class);
    }

    public function sport(){
        return $this->belongsTo(Sport::class);
    }

    public function leagues()
{
    return $this->hasMany(\Modules\Invitations\Entities\League::class, 'stadium_id');
}



    protected static function newFactory()
    {
       // return \Modules\Stadium\Database\factories\StadiumRequestFactory::new();
    }
}
