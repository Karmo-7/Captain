<?php

namespace Modules\Invitations\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MatchResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'goals_scored',
        'is_winnerstatus',
        'team_id',
        'invitation_match_id'
    ];

    public function team()
    {
        return $this->belongsTo(\Modules\Team\Entities\Team::class);
    }

    public function invitationMatch()
    {
        return $this->belongsTo(InvitationMatch::class, 'invitation_match_id');
    }
}
