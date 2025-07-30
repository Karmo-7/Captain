<?php

namespace Modules\Invitations\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class League extends Model
{
    use HasFactory;
 protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'price',
        'prize',
        'status',
        'created_by',
    ];


     public function creator()
{
    return $this->belongsTo(\App\Models\User::class, 'created_by');
}

  // كل الدعوات المرتبطة بالدوري
    public function invitations()
    {
        return $this->hasMany(\Modules\Invitations\Entities\Team_Ownerinv::class, 'league_id');
    }

    protected static function newFactory()
    {
        //return \Modules\Invitations\Database\factories\LeagueFactory::new();
    }
}
