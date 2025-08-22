<?php
namespace Modules\Rating\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Facilities\Entities\Facility;
use App\Models\User;

class FacilityRating extends Model
{
    use HasFactory;

    protected $fillable = ['facility_id', 'user_id', 'rating', 'review'];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
