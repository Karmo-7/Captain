<?php

namespace Modules\Facilities\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Stadium\Entities\Stadium;

class Facility extends Model
{
    use HasFactory;

    protected $fillable = ['stadium_id','name','quantity','description','photos'];


    public function stadium(){
        return $this->belongsTo(Stadium::class);
    }
    protected $casts = [
        'photos' => 'array',
    ];
    

    protected static function newFactory()
    {
        return \Modules\Facilities\Database\factories\FacilityFactory::new();
    }
}
