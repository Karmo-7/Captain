<?php

namespace Modules\Stadium\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Stadium extends Model
{
    use HasFactory;
      protected $table = 'stadiums';

    protected $fillable = ['user_id','sport_id','name','location','description','photos','Length','Width','owner_number','start_time','end_time',];

 public function slots()
    {
        return $this->hasMany(StadiumSlot::class, 'stadium_id', 'id');
    }


    protected static function newFactory()
    {
      //  return \Modules\Stadium\Database\factories\StadiumFactory::new();
    }
}
