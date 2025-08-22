<?php
namespace Modules\Rating\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Rating\Entities\FacilityRating;
use Modules\Facilities\Entities\Facility;

class FacilityRatingController extends Controller
{
    protected function successResponse($data, $message = 'Operation successful', $code = 200)
    {
        return response()->json([
            'status' => true,
            'status_code' => $code,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function errorResponse($message = 'Something went wrong', $code = 400, $data = null)
    {
        return response()->json([
            'status' => false,
            'status_code' => $code,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'facility_id' => 'required|exists:facilities,id',
                'rating' => 'required|integer|min:1|max:5',
                'review' => 'nullable|string'
            ]);

            $data['user_id'] = auth()->id();

            $rating = FacilityRating::updateOrCreate(
                ['facility_id' => $data['facility_id'], 'user_id' => $data['user_id']],
                $data
            );

            return $this->successResponse($rating, 'Rating saved successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function index(Facility $facility)
    {
        try {
            $ratings = $facility->ratings()->with('user')->get();
            $avg = $facility->ratings()->avg('rating');

            return $this->successResponse(['average' => $avg, 'ratings' => $ratings]);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}

