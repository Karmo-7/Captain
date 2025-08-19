<?php

namespace Modules\Invitations\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Invitations\Entities\InvitationMatch;

class InvitationMatchController extends Controller
{
    public function index()
    {
        $matches = InvitationMatch::all();
        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'All invitations retrieved successfully',
            'data' => $matches
        ]);
    }

   public function store(Request $request)
{
    $data = $request->validate([
        'proposed_date' => 'nullable|string|max:45',
        'status' => 'nullable|string|max:45',
        'sent_at' => 'nullable|string|max:45',
        'sender_team_id' => 'nullable|integer',
        'receiver_team_id' => 'nullable|integer',
        'stadium_id' => 'required|integer',
        'slot_id' => 'required|integer',
        'league_id' => 'nullable|integer|exists:leagues,id',
    ]);

    // تحقق من ملكية الفريق المرسل فقط إذا تم توفيره
    if (!empty($data['sender_team_id'])) {
        $senderTeam = \Modules\Team\Entities\Team::find($data['sender_team_id']);

        if (!$senderTeam || $senderTeam->captin_id !== auth()->id()) {
            return response()->json([
                'status' => false,
                'status_code' => 403,
                'message' => 'You are not allowed to send an invitation with this team',
                'data' => null
            ]);
        }
    }

    $match = InvitationMatch::create($data);

    return response()->json([
        'status' => true,
        'status_code' => 201,
        'message' => 'Invitation match created successfully',
        'data' => $match
    ]);
}
    public function show($id)
    {
        $match = InvitationMatch::find($id);
        if (!$match) {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => 'Invitation not found',
                'data' => null
            ]);
        }

        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Invitation retrieved',
            'data' => $match
        ]);
    }

    public function update(Request $request, $id)
    {
        $match = InvitationMatch::find($id);
        if (!$match) {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => 'Invitation not found',
                'data' => null
            ]);
        }

        $data = $request->validate([
            'proposed_date' => 'nullable|string|max:45',
            'status' => 'nullable|string|max:45',
            'sent_at' => 'nullable|string|max:45',
            'sender_team_id' => 'sometimes|integer',
            'receiver_team_id' => 'sometimes|integer',
            'stadium_id' => 'sometimes|integer',
            'slot_id' => 'sometimes|integer',
            'league_id' => 'sometimes|integer',
        ]);

        $match->update($data);

        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Invitation updated successfully',
            'data' => $match->fresh()
        ]);
    }

    public function destroy($id)
    {
        $match = InvitationMatch::find($id);
        if (!$match) {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => 'Invitation not found',
                'data' => null
            ]);
        }

        $match->delete();

        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Invitation deleted successfully',
            'data' => null
        ]);
    }


public function storeWithLeagueCheck(Request $request)
{
    $data = $request->validate([
        'proposed_date'     => 'nullable|string|max:45',
        'status'            => 'nullable|string|max:45',
        'sent_at'           => 'nullable|string|max:45',
        'sender_team_id'    => 'required|integer',
        'receiver_team_id'  => 'required|integer',
        'stadium_id'        => 'required|integer',
        'slot_id'           => 'required|integer',
        'league_id'         => 'required|integer|exists:leagues,id',
    ]);

    // تأكد إنو الدوري موجود
    $league = \Modules\Invitations\Entities\League::find($data['league_id']);
    if (!$league) {
        return response()->json([
            'status' => false,
            'status_code' => 404,
            'message' => 'League not found',
            'data' => null
        ]);
    }

    // إجبار تخزين الفريقين ضمن هاد الدوري
    $data['sender_team_id']   = $request->sender_team_id;
    $data['receiver_team_id'] = $request->receiver_team_id;
    $data['league_id']        = $league->id;

    $match = InvitationMatch::create($data);

    return response()->json([
        'status' => true,
        'status_code' => 201,
        'message' => 'Invitation match created successfully and linked to the given league',
        'data' => $match
    ]);
}

public function getByLeague($leagueId)
{
    $matches = InvitationMatch::with(['senderTeam', 'receiverTeam'])
        ->where('league_id', $leagueId)
        ->get();

    if ($matches->isEmpty()) {
        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'No invitation matches found for this league',
            'data' => []
        ]);
    }

    return response()->json([
        'status' => true,
        'status_code' => 200,
        'message' => 'Invitation matches for league retrieved successfully',
        'data' => $matches
    ]);
}


}
