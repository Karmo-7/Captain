<?php

namespace Modules\Ads\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ad extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'user_id', 'type', 'callback_url'
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
