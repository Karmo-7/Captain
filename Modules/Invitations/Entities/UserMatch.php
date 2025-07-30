<?php

namespace Modules\Invitations\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserMatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'match_invitation_id',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function invitationMatch()
    {
        return $this->belongsTo(InvitationMatch::class, 'match_invitation_id');
    }
}
