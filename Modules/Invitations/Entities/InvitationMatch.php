<?php

namespace Modules\Invitations\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Invitations\Entities\UserMatch;



class InvitationMatch extends Model
{
    use HasFactory;

     protected $table = 'invitation_matches';
   // protected $primaryKey = 'invitation_id';

    protected $fillable = [
        'proposed_date',
        'status',
        'sent_at',
        'sender_team_id',
        'receiver_team_id',
        'stadium_id',
        'slot_id',
        'league_id',
    ];

    // 🔁 العلاقات

    public function senderTeam()
    {
        return $this->belongsTo(\Modules\Team\Entities\Team::class, 'sender_team_id');
    }

    public function receiverTeam()
    {
        return $this->belongsTo(\Modules\Team\Entities\Team::class, 'receiver_team_id');
    }

    public function stadium()
    {
        return $this->belongsTo(\Modules\Stadium\Entities\Stadium::class, 'stadium_id');
    }

    public function slot()
    {
        return $this->belongsTo(\Modules\Stadium\Entities\StadiumSlot::class, 'slot_id');
    }

    public function league()
    {
        return $this->belongsTo(League::class, 'league_id');
    }

    public function userMatches()
{
    return $this->hasMany(UserMatch::class, 'match_invitation_id');
}
public function matchResults()
    {
        return $this->hasMany(MatchResult::class, 'invitation_match_id');
    }

    protected static function newFactory()
    {
      //  return \Modules\Invitations\Database\factories\InvitationMatchFactory::new();
    }
}
