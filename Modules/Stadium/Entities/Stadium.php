<?php

namespace Modules\Stadium\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Facilities\Entities\Facility;
use Modules\Sport\Entities\Sport;

class Stadium extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','sport_id','name','location','description','photos','Length','Width','owner_number'];
    protected $table='stadiums';

    protected $casts = [
        'photos' => 'array',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function sport(){
        return $this->belongsTo(Sport::class);
    }

    public function facility(){
        return $this->hasMany(Facility::class);
    }

    protected static function newFactory()
    {
        return \Modules\Stadium\Database\factories\StadiumFactory::new();
    }


}
