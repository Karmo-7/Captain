<?php

namespace Modules\Book\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = ['stadium_slot_booking_id', 'method', 'amount', 'status', 'transaction_id','expires_at'];



    public function booking()
    {
        return $this->belongsTo(StadiumSlotBooking::class, 'stadium_slot_booking_id');
    }

    protected static function newFactory()
    {
        return \Modules\Book\Database\factories\PaymentFactory::new();
    }
}
