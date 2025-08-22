<?php
namespace Modules\Reports\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Report extends Model
{
    protected $fillable = [
        'player_id', 'stadium_owner_id', 'admin_id', 'reason', 'status'
    ];

    public function player() {
        return $this->belongsTo(User::class, 'player_id');
     }

    public function stadiumOwner() {
        return $this->belongsTo(User::class, 'stadium_owner_id');
    }

    public function admin(){

    return $this->belongsTo(User::class, 'admin_id');
 }
}
