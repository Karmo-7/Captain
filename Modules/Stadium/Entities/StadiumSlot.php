<?php

namespace Modules\Stadium\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StadiumSlot extends Model
{
    use HasFactory;
protected $table = 'stadium_slots';
    protected $primaryKey = 'id';

    protected $fillable = [
        'start_time',
        'end_time',
        'stadium_id',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];


    public function stadium()
    {
        return $this->belongsTo(Stadium::class);
    }
    protected static function newFactory()
    {
       // return \Modules\Stadium\Database\factories\StadiumSlotFactory::new();
    }
}
