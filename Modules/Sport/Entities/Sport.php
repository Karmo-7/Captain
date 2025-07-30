<?php

namespace Modules\Sport\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Stadium\Entities\Stadium;
use Modules\Stadium\Entities\StadiumRequest;

class Sport extends Model
{
    use HasFactory;


    protected $fillable = ['name','photo','max_player_per_team'];

    public function stadiumRequests(){
        return $this->hasMany(StadiumRequest::class);
    }

    public function stadiums(){
        return $this->hasMany(Stadium::class);
    }



    protected static function newFactory()
    {
       // return \Modules\Sport\Database\factories\SportFactory::new();
    }

     public function teams()
    {
        return $this->hasMany(\Modules\Team\Entities\Team::class);
    }
}
