<?php

namespace Modules\Team\Entities;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Invitations\Entities\Team_Usesrinv;

class Team extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'captin_id', 'sport_id', 'logo'];

    protected static function newFactory()
    {

    }
public function profiles()
{
    return $this->hasMany(Profile::class, 'team_id', 'team_id');
}

public function captain()
{
    return $this->hasOne(User::class, 'id', 'captin_id');
}

public function sport()
    {
        return $this->belongsTo(\Modules\Sport\Entities\Sport::class, 'sport_id');
    }

    // الدعوات التي أرسلها هذا الفريق للمستخدمين
    public function sentInvitations()
    {
        return $this->hasMany(Team_Usesrinv::class, 'team_id');
    }

    // الطلبات التي استلمها هذا الفريق من مستخدمين (طلبات انضمام)
    public function receivedJoinRequests()
    {
        return $this->hasMany(Team_Usesrinv::class, 'team_id');
    }

    // دعوات أرسلها الفريق أو استلمها
    public function ownerInvitations()
    {
        return $this->hasMany(\Modules\Invitations\Entities\Team_Ownerinv::class, 'team_id');
    }

      public function matchResults()
    {
        return $this->hasMany(\Modules\Invitations\Entities\MatchResult::class, 'team_id');
    }

    protected $appends = ['logo_url'];

public function getLogoUrlAttribute()
{
    return $this->logo ? asset('storage/' . $this->logo) : null;
}


}
