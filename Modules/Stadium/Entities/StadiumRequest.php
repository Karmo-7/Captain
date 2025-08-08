<?php

namespace Modules\Stadium\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Sport\Entities\Sport;

class StadiumRequest extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'sport_id', 'name', 'location', 'description', 'photos', 'Length', 'Width', 'owner_number','start_time','end_time','status','admin_notes','price','deposit','duration'];

    protected $casts = [
        'photos' => 'array',
    ];
    public function user(){
        return $this->belongsTo(User::class);
    }

    public function sport(){
        return $this->belongsTo(Sport::class);
    }



    protected static function newFactory()
    {
       // return \Modules\Stadium\Database\factories\StadiumRequestFactory::new();
    }
}
