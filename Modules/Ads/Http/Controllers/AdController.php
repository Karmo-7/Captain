<?php

namespace Modules\Ads\Http\Controllers;

use Modules\Ads\Entities\Ad;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdController extends Controller
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

    public function index()
    {
        $ads = Ad::with('user')->get();
        return $this->successResponse($ads, 'Ads retrieved successfully');
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'title' => 'required|string',
                'description' => 'nullable|string',
                'type' => 'required|in:search_for_user,search_for_team',
                'callback_url' => 'required|url'
            ]);
            $data['user_id'] = auth()->id();

            $ad = Ad::create($data);
            return $this->successResponse($ad, 'Ad created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function show(Ad $ad)
    {
        $ad->load('user');
        return $this->successResponse($ad, 'Ad retrieved successfully');
    }

    public function update(Request $request, Ad $ad)
    {
        try {
            $data = $request->validate([
                'title' => 'sometimes|string',
                'description' => 'nullable|string',
                'type' => 'sometimes|in:search_for_user,search_for_team',
                'callback_url' => 'sometimes|url'
            ]);

            $ad->update($data);
            return $this->successResponse($ad, 'Ad updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function destroy(Ad $ad)
    {
        $ad->delete();
        return response()->json([
            'status' => true,
            'status_code' => 204,
            'message' => 'Ad deleted successfully',
            'data' => null
        ], 204);
    }
}
