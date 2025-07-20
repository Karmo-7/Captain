<?php

namespace Modules\Sport\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sport extends Model
{
    use HasFactory;

    protected $fillable = ['name','photo'];

    

    protected static function newFactory()
    {
        return \Modules\Sport\Database\factories\SportFactory::new();
    }
}
