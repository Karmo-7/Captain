<?php

namespace Modules\Invitations\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Invitations\Entities\Team_Ownerinv;

class TeamOwnerinvController extends Controller
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
        $data = Team_Ownerinv::all();
        return $this->successResponse($data, 'All owner invitations retrieved successfully');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'status' => 'nullable|in:pending,accepted,declined',
                'sent_at' => 'nullable|string|max:45',
                'team_id' => 'required|integer',
                'league_id' => 'required|integer',
                'is_team' => 'required|boolean'
            ]);

            $invitation = Team_Ownerinv::create([
                'status' => $request->status ?? 'pending',
                'sent_at' => $request->sent_at,
                'team_id' => $request->team_id,
                'league_id' => $request->league_id,
                'owner_id' => auth()->id(),
                'is_team' => $request->is_team,
            ]);

            return $this->successResponse($invitation, 'Owner invitation created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function show($id)
    {
        $inv = Team_Ownerinv::find($id);
        if (!$inv) {
            return $this->errorResponse('Owner invitation not found', 404);
        }

        return $this->successResponse($inv, 'Owner invitation retrieved successfully');
    }

    public function update(Request $request, $id)
    {
        $inv = Team_Ownerinv::find($id);
        if (!$inv) {
            return $this->errorResponse('Owner invitation not found', 404);
        }

        $request->validate([
            'status' => 'nullable|in:pending,accepted,declined',
            'sent_at' => 'nullable|string|max:45',
            'team_id' => 'sometimes|integer',
            'league_id' => 'sometimes|integer',
            'owner_id' => 'sometimes|integer',
            'is_team' => 'sometimes|boolean',
        ]);

        $inv->update($request->all());
        return $this->successResponse($inv, 'Owner invitation updated successfully');
    }

    public function destroy($id)
    {
        $deleted = Team_Ownerinv::destroy($id);

        if (!$deleted) {
            return $this->errorResponse('Owner invitation not found or not deleted', 404);
        }

        return $this->successResponse(null, 'Owner invitation deleted successfully');
    }

    // Invitations sent by owner
    public function invitationsSentByOwner($ownerId)
    {
        $data = Team_Ownerinv::sentByOwner($ownerId)->get();
        return $this->successResponse($data, 'Invitations sent by owner retrieved successfully');
    }

    // Invitations received by owner
    public function invitationsReceivedByOwner($ownerId)
    {
        $data = Team_Ownerinv::receivedByOwner($ownerId)->get();
        return $this->successResponse($data, 'Invitations received by owner retrieved successfully');
    }

    // Invitations sent by team
    public function invitationsSentByTeam($teamId)
    {
        $data = Team_Ownerinv::sentByTeam($teamId)->get();
        return $this->successResponse($data, 'Invitations sent by team retrieved successfully');
    }

    // Invitations received by team
    public function invitationsReceivedByTeam($teamId)
    {
        $data = Team_Ownerinv::receivedByTeam($teamId)->get();
        return $this->successResponse($data, 'Invitations received by team retrieved successfully');
    }

      // ✅ Approve owner invitation
    public function approveInvitation(Request $request, $id)
    {
        $invitation = Team_Ownerinv::find($id);

        if (!$invitation) {
            return $this->errorResponse('Invitation not found', 404);
        }

        if ($invitation->status === 'accepted') {
            return $this->errorResponse('Invitation already accepted', 400);
        }

        $invitation->update(['status' => 'accepted']);

        // If the invitation is sent to a team owner and accepted
        if ($invitation->is_team && $invitation->owner_id) {
            $profile = Profile::where('user_id', $invitation->owner_id)->first();
            if (!$profile) {
                return $this->errorResponse('Profile not found for the invited owner', 404);
            }

            // Link owner to the team
            $profile->update(['team_id' => $invitation->team_id]);
        }

        return $this->successResponse($invitation, 'Owner invitation approved and linked successfully');
    }

    // ✅ Reject owner invitation
    public function rejectInvitation($id)
    {
        $invitation = Team_Ownerinv::find($id);

        if (!$invitation) {
            return $this->errorResponse('Invitation not found', 404);
        }

        if ($invitation->status === 'declined') {
            return $this->errorResponse('Invitation already declined', 400);
        }

        $invitation->update(['status' => 'declined']);

        return $this->successResponse($invitation, 'Owner invitation rejected successfully');
    }


}
