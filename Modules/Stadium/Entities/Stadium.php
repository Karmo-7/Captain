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

 public function slots()
    {
        return $this->hasMany(StadiumSlot::class, 'stadium_id', 'id');
    }

    protected $fillable = ['user_id','sport_id','name','location','description','photos','Length','Width','owner_number','start_time','end_time','price','deposit','duration','latitude', 'longitude'];
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
      //  return \Modules\Stadium\Database\factories\StadiumFactory::new();
    }


}
