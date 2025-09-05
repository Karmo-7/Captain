<?php

namespace Modules\Invitations\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Team_Usesrinv extends Model
{
    use HasFactory;

    protected $table = 'team_usesrinv';

    protected $primaryKey = 'id';

    protected $fillable = [
        'status',
        'sent_at',
        'team_id',
        'receiver_id',
        'is_team',  // add this so you can mass assign it
    ];

    // Relationships

    // Sender: if is_team = 1, sender is team (team_id), else sender is user (team_id is null)
    public function sender()
    {
        if ($this->is_team) {

            return $this->belongsTo(\Modules\Team\Entities\Team::class, 'team_id');
        } else {

            return $this->belongsTo(\App\Models\User::class, 'team_id');
        }
    }

    // Receiver: if is_team = 1, receiver is user (receiver_id), else receiver is team (receiver_id)
    public function receiver()
    {
        if ($this->is_team) {
            return $this->belongsTo(\App\Models\User::class, 'receiver_id');
        } else {
            return $this->belongsTo(\Modules\Team\Entities\Team::class, 'receiver_id');
        }
    }

    // Check if invitation is sent by team
    public function isSentByTeam(): bool
    {
        return $this->is_team == 1;
    }

    // Check if invitation is sent by user
    public function isSentByUser(): bool
    {
        return $this->is_team == 0;
    }

    // Actual sender model instance
    public function actualSender()
    {
        if ($this->isSentByTeam()) {
            return $this->sender; // Team
        } elseif ($this->isSentByUser()) {
            return $this->sender; // User
        }
        return null;
    }

    // Actual receiver model instance
    public function actualReceiver()
    {
        if ($this->isSentByTeam()) {
            return $this->receiver; // User
        } elseif ($this->isSentByUser()) {
            return $this->receiver; // Team
        }
        return null;
    }

    // Scopes

    // Invitations sent by a user (is_team = 0, team_id = user id)
    public function scopeSentByUser($query, $userId)
    {
        return $query->where('receiver_id', $userId)
                     ->where('is_team', 0);
    }

    // Invitations received by a user (is_team = 1, receiver_id = user id)
    public function scopeReceivedByUser($query, $userId)
    {
        return $query->where('receiver_id', $userId)
                     ->where('is_team', 1);
    }

    // Invitations sent by a team (is_team = 1, team_id = team id)
    public function scopeSentByTeam($query, $teamId)
    {
        return $query->where('team_id', $teamId)
                     ->where('is_team', 1);
    }

    // Invitations received by a team (is_team = 0, receiver_id = team id)
    public function scopeReceivedByTeam($query, $teamId)
    {
        return $query->where('team_id', $teamId)
                     ->where('is_team', 0);
    }

    protected static function newFactory()
    {
      //  return \Modules\Invitations\Database\factories\TeamUsesrinvFactory::new();
    }
}
