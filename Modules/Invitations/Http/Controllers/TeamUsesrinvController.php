<?php

namespace Modules\Invitations\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Invitations\Entities\Team_Usesrinv;

class TeamUsesrinvController extends Controller
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
        $data = Team_Usesrinv::all();
        return $this->successResponse($data, 'All invitations retrieved successfully');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'status' => 'nullable|in:pending,accepted,declined',
                'sent_at' => 'nullable|string|max:45',
                'team_id' => 'required|integer',
                'receiver_id' => 'required|integer',
                'is_team' => 'required|boolean',
            ]);

            $invitation = Team_Usesrinv::create($request->all());
            return $this->successResponse($invitation, 'Invitation created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function show($id)
    {
        $inv = Team_Usesrinv::find($id);
        if (!$inv) {
            return $this->errorResponse('Invitation not found', 404);
        }
        return $this->successResponse($inv, 'Invitation retrieved successfully');
    }

    public function update(Request $request, $id)
    {
        $inv = Team_Usesrinv::find($id);
        if (!$inv) {
            return $this->errorResponse('Invitation not found', 404);
        }

        $request->validate([
            'status' => 'nullable|in:pending,accepted,declined',
            'sent_at' => 'nullable|string|max:45',
            'team_id' => 'sometimes|integer',
            'receiver_id' => 'sometimes|integer',
            'is_team' => 'sometimes|boolean',
        ]);

        $inv->update($request->all());

        return $this->successResponse($inv, 'Invitation updated successfully');
    }

    public function destroy($id)
    {
        $deleted = Team_Usesrinv::destroy($id);

        if (!$deleted) {
            return $this->errorResponse('Invitation not found or not deleted', 404);
        }

        return $this->successResponse(null, 'Invitation deleted successfully');
    }

    // Invitations sent by user
    public function invitationsSentByUser($userId)
    {
        $data = Team_Usesrinv::sentByUser($userId)->get();
        return $this->successResponse($data, 'Invitations sent by user retrieved successfully');
    }

    // Invitations received by user
    public function invitationsReceivedByUser($userId)
    {
        $data = Team_Usesrinv::receivedByUser($userId)->get();
        return $this->successResponse($data, 'Invitations received by user retrieved successfully');
    }

    // Invitations sent by team
    public function invitationsSentByTeam($teamId)
    {
        $data = Team_Usesrinv::sentByTeam($teamId)->get();
        return $this->successResponse($data, 'Invitations sent by team retrieved successfully');
    }

    // Invitations received by team
    public function invitationsReceivedByTeam($teamId)
    {
        $data = Team_Usesrinv::receivedByTeam($teamId)->get();
        return $this->successResponse($data, 'Invitations received by team retrieved successfully');
    }
}
