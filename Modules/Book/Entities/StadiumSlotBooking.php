<?php

namespace Modules\Book\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Stadium\Entities\Stadium;
use Modules\Stadium\Entities\StadiumSlot;

class StadiumSlotBooking extends Model
{
    use HasFactory;

    protected $fillable = ['stadium_slot_id', 'stadium_id', 'user_id', 'date', 'status', 'payment_status','payment_type','amount_paid'];

    protected static function newFactory(): mixed
    {
        return \Modules\Book\Database\factories\StadiumSlotBookingFactory::new();
    }
    public function slot()
    {
        return $this->belongsTo(StadiumSlot::class, 'stadium_slot_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function stadium()
    {
        return $this->belongsTo(Stadium::class, 'stadium_id');
    }
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
