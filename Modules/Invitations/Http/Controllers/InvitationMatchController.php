<?php

namespace Modules\Invitations\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Invitations\Entities\InvitationMatch;
use Modules\Invitations\Enums\MatchStatus;

class InvitationMatchController extends Controller
{
    // public function index()
    // {
    //     $matches = InvitationMatch::all();
    //     return response()->json([
    //         'status' => true,
    //         'status_code' => 200,
    //         'message' => 'All invitations retrieved successfully',
    //         'data' => $matches
    //     ]);
    // }

    public function index()
{
    $matches = InvitationMatch::whereNotNull('league_id')->get();

    return response()->json([
        'status' => true,
        'status_code' => 200,
        'message' => 'Invitations with league_id retrieved successfully',
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


public function teamsByLeague($leagueId)
{
    // جلب كل الدعوات الخاصة بالدوري المحدد
    $matches = InvitationMatch::with(['senderTeam', 'receiverTeam'])
        ->where('league_id', $leagueId)
        ->get();

    if ($matches->isEmpty()) {
        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'No teams found for this league',
            'data' => []
        ]);
    }

    // جمع الفرق بدون تكرار
    $teams = collect();
    foreach ($matches as $match) {
        if ($match->senderTeam) {
            $teams->push($match->senderTeam);
        }
        if ($match->receiverTeam) {
            $teams->push($match->receiverTeam);
        }
    }

    $teams = $teams->unique('id')->values(); // إزالة التكرارات

    return response()->json([
        'status' => true,
        'status_code' => 200,
        'message' => 'Teams participating in the league retrieved successfully',
        'data' => $teams
    ]);
}


public function approve($id)
    {
        $match = InvitationMatch::find($id);
        if (!$match) {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => 'Invitation match not found',
                'data' => null
            ]);
        }

        if ($match->status === MatchStatus::APPROVED) {
            return response()->json([
                'status' => false,
                'status_code' => 400,
                'message' => 'Invitation match already approved',
                'data' => $match
            ]);
        }

        $match->status = MatchStatus::APPROVED;
        $match->save();

        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Invitation match approved successfully',
            'data' => $match
        ]);
    }

    /**
     * Reject a match invitation
     */
    public function reject($id)
    {
        $match = InvitationMatch::find($id);
        if (!$match) {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => 'Invitation match not found',
                'data' => null
            ]);
        }

        if ($match->status === MatchStatus::REJECTED) {
            return response()->json([
                'status' => false,
                'status_code' => 400,
                'message' => 'Invitation match already rejected',
                'data' => $match
            ]);
        }

        $match->status = MatchStatus::REJECTED;
        $match->save();

        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Invitation match rejected successfully',
            'data' => $match
        ]);
    }

    /**
     * Mark a match as played
     */
   public function markAsPlayed(Request $request, $id)
{
    $match = InvitationMatch::find($id);

    if (!$match) {
        return response()->json([
            'status' => false,
            'status_code' => 404,
            'message' => 'Invitation match not found',
            'data' => null
        ]);
    }

    if ($match->status === 'played') {
        return response()->json([
            'status' => false,
            'status_code' => 400,
            'message' => 'Match already marked as played',
            'data' => null
        ]);
    }

    $request->validate([
        'results' => 'required|array|min:1',
        'results.*.team_id' => 'required|exists:teams,id',
        'results.*.goals_scored' => 'required|integer|min:0',
        'results.*.is_winnerstatus' => 'required|boolean',
    ]);

    $resultsData = $request->results;

    foreach ($resultsData as $result) {
        \Modules\Invitations\Entities\MatchResult::create([
            'invitation_match_id' => $match->id,
            'team_id' => $result['team_id'],
            'goals_scored' => $result['goals_scored'],
            'is_winnerstatus' => $result['is_winnerstatus'] ? 1 : 0
        ]);
    }

    $match->update(['status' => 'played']);

    return response()->json([
        'status' => true,
        'status_code' => 200,
        'message' => 'Match marked as played and results recorded successfully',
        'data' => $match->load('matchResults') // eager load results
    ]);
}




}
