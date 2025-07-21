<?php

namespace Modules\Stadium\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Stadium extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','sport_id','name','location','description','photos','Length','Width','owner_number'];

    protected static function newFactory()
    {
        return \Modules\Stadium\Database\factories\StadiumFactory::new();
    }
}
