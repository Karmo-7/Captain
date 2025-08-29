<?php

namespace App\Models;
use App\Models\Profile;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Modules\Book\Entities\Book;
use Modules\Invitations\Entities\Team_Usesrinv;
use Modules\Stadium\Entities\Stadium;
use Modules\Stadium\Entities\StadiumRequest;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasRoles,Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [

        'email',
        'password',
        'stripe_ready',
        'stripe_account_id',
        'stripe_customer_id'

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    public function profile(){
        return $this->hasOne(Profile::class);
    }

     public function teamAsCaptain()
    {
        return $this->hasOne(\Modules\Team\Entities\Team::class, 'captin_id');
    }

    public function leagues()
{
    return $this->hasMany(\Modules\Invitations\Entities\League::class, 'created_by');
}

 // الدعوات التي استلمها هذا المستخدم من فرق
    public function receivedInvitations()
    {
        return $this->hasMany(Team_Usesrinv::class, 'receiver_id');
    }

    // الطلبات التي أرسلها هذا المستخدم للفرق (طلبات انضمام)
    public function sentJoinRequests()
    {
        return $this->hasMany(Team_Usesrinv::class, 'receiver_id');
    }

    // كل الدعوات المرتبطة بالمالك
    public function teamInvitations()
    {
        return $this->hasMany(\Modules\Invitations\Entities\Team_Ownerinv::class, 'owner_id');
    }

    public function userMatches()
    {
    return $this->hasMany(\Modules\Invitations\Entities\UserMatch::class, 'user_id');

    }
    public function ads()
    {
        return $this->hasMany(\Modules\Ads\Entities\Ad::class);
    }
    public function stadiumRequest(){
        return $this->hasMany(StadiumRequest::class);
    }

    public function stadiums(){
        return $this->hasMany(Stadium::class);
    }






public function stadiumRatings()
{
    return $this->hasMany(\Modules\Rating\Entities\stadiumRating::class);
}

public function facilityRatings()
{
    return $this->hasMany(\Modules\Rating\Entities\FacilityRating::class);
}


 // Reports created by stadium_owner
    public function reportsCreated()
    {
        return $this->hasMany(\Modules\Reports\Entities\Report::class, 'stadium_owner_id');
    }

    // Reports where this user is reported (player)
    public function reportsReceived()
    {
        return $this->hasMany(\Modules\Reports\Entities\Report::class, 'player_id');
    }

    // Reports handled by admin
    public function reportsHandled()
    {
        return $this->hasMany(\Modules\Reports\Entities\Report::class, 'admin_id');
    }

}
