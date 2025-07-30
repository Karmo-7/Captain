<?php

namespace Modules\Invitations\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Team_Ownerinv extends Model
{
    use HasFactory;

    protected $table = 'team_ownerinv';

    protected $fillable = [
        'status',
        'sent_at',
        'team_id',
        'owner_id',
        'league_id',
        'is_team'
    ];

    // Relationships

    public function team()
    {
        return $this->belongsTo(\Modules\Team\Entities\Team::class, 'team_id');
    }

    public function owner()
    {
        return $this->belongsTo(\App\Models\User::class, 'owner_id');
    }

    public function league()
    {
        return $this->belongsTo(League::class, 'league_id');
    }

    // Check who sent the invitation using is_team
    public function isSentByTeam(): bool
    {
        return $this->is_team == 1;
    }

    public function isSentByOwner(): bool
    {
        return $this->is_team == 0;
    }

    // Actual sender
    public function actualSender()
    {
        if ($this->isSentByTeam()) {
            return $this->team; // Team is the sender
        } elseif ($this->isSentByOwner()) {
            return $this->owner; // Owner is the sender
        }
        return null;
    }

    // Actual receiver
    public function actualReceiver()
    {
        if ($this->isSentByTeam()) {
            return $this->owner; // Owner is receiver
        } elseif ($this->isSentByOwner()) {
            return $this->team; // Team is receiver
        }
        return null;
    }

    // Scopes
    public function scopeSentByOwner($query, $ownerId)
    {
        return $query->where('owner_id', $ownerId)
                     ->where('is_team', 0);
    }

    public function scopeReceivedByOwner($query, $ownerId)
    {
        return $query->where('owner_id', $ownerId)
                     ->where('is_team', 1);
    }

    public function scopeSentByTeam($query, $teamId)
    {
        return $query->where('team_id', $teamId)
                     ->where('is_team', 1);
    }

    public function scopeReceivedByTeam($query, $teamId)
    {
        return $query->where('team_id', $teamId)
                     ->where('is_team', 0);
    }
}
