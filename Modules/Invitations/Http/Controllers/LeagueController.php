<?php

namespace Modules\Invitations\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Invitations\Entities\League;

class LeagueController extends Controller
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
        $data = League::all();
        return $this->successResponse($data, 'All leagues retrieved successfully');
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'price' => 'required|numeric',
                'prize' => 'required|string',
                'status' => 'required|in:pending,active,finished',
            ]);

            $data['created_by'] = auth()->id();
            $league = League::create($data);

            return $this->successResponse($league, 'League created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function show($id)
    {
        $league = League::find($id);
        if (!$league) {
            return $this->errorResponse('League not found', 404);
        }

        return $this->successResponse($league, 'League retrieved successfully');
    }

    public function update(Request $request, $id)
    {
        $league = League::find($id);
        if (!$league) {
            return $this->errorResponse('League not found', 404);
        }

        if ($league->created_by !== auth()->id() && !auth()->user()->hasRole('stadium_owner')) {
        return $this->errorResponse('Unauthorized', 403);
    }

        $league->update($request->all());
        return $this->successResponse($league, 'League updated successfully');
    }

    public function destroy($id)
    {
         // جلب الدوري
    $league = League::find($id);

    if (!$league) {
        return $this->errorResponse('League not found', 404);
    }

    // التحقق من المالك أو الدور
    if ($league->created_by !== auth()->id() && !auth()->user()->hasRole('stadium_owner')) {
        return $this->errorResponse('Unauthorized', 403);
    }

    // تنفيذ الحذف
    $league->delete();

    return $this->successResponse(null, 'League deleted successfully');
    }
}
