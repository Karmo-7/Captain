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

public function averageRating()
{
    return $this->ratings()->avg('rating');


}
 public function ratings()
    {
        return $this->hasMany(\Modules\Rating\Entities\FacilityRating::class, 'facility_id');
    }
    protected static function newFactory()
    {
       // return \Modules\Facilities\Database\factories\FacilityFactory::new();
    }

}
