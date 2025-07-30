<?php

namespace Modules\Invitations\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Invitations\Entities\MatchResult;

class MatchResultController extends Controller
{
    // ✅ دوال الرد الموحد
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
        $data = MatchResult::all();
        return $this->successResponse($data, 'All match results retrieved successfully');
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'goals_scored' => 'required|string|max:45',
                'is_winnerstatus' => 'required|string|max:45',
                'team_id' => 'required|exists:teams,id',
                'invitation_match_id' => 'required|exists:invitation_matches,id',
            ]);

            $matchResult = MatchResult::create($data);
            return $this->successResponse($matchResult, 'Match result created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function show($id)
    {
        $matchResult = MatchResult::find($id);
        if (!$matchResult) {
            return $this->errorResponse('Match result not found', 404);
        }

        return $this->successResponse($matchResult, 'Match result retrieved successfully');
    }

    public function update(Request $request, $id)
    {
        $matchResult = MatchResult::find($id);
        if (!$matchResult) {
            return $this->errorResponse('Match result not found', 404);
        }

        $matchResult->update($request->all());
        return $this->successResponse($matchResult, 'Match result updated successfully');
    }

    public function destroy($id)
    {
        $deleted = MatchResult::destroy($id);
        if (!$deleted) {
            return $this->errorResponse('Match result not found or not deleted', 404);
        }

        return $this->successResponse(null, 'Match result deleted successfully', 204);
    }
}
