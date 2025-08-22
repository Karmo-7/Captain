<?php
namespace Modules\Rating\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Rating\Entities\StadiumRating;
use Modules\Stadium\Entities\Stadium;

class StadiumRatingController extends Controller
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
                'stadium_id' => 'required|exists:stadiums,id',
                'rating' => 'required|integer|min:1|max:5',
                'review' => 'nullable|string'
            ]);

            $data['user_id'] = auth()->id();

            $rating = StadiumRating::updateOrCreate(
                ['stadium_id' => $data['stadium_id'], 'user_id' => $data['user_id']],
                $data
            );

            return $this->successResponse($rating, 'Rating saved successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function index(Stadium $stadium)
    {
        try {
            $ratings = $stadium->ratings()->with('user')->get();
            $avg = $stadium->ratings()->avg('rating');

            return $this->successResponse(['average' => $avg, 'ratings' => $ratings]);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}

