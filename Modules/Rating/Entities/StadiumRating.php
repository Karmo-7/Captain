<?php
namespace Modules\Rating\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Stadium\Entities\Stadium;
use App\Models\User;

class StadiumRating extends Model
{
    use HasFactory;

    protected $fillable = ['stadium_id', 'user_id', 'rating', 'review'];

    public function stadium()
    {
        return $this->belongsTo(Stadium::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
