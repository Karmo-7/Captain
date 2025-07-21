<?php

namespace Modules\Stadium\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StadiumRequest extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'sport_id', 'name', 'location', 'description', 'photos', 'Length', 'Width', 'owner_number','status','admin_notes'];

    protected $casts = [
        'photos' => 'array',
    ];
    protected static function newFactory()
    {
        return \Modules\Stadium\Database\factories\StadiumRequestFactory::new();
    }
}
