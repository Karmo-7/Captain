<?php

namespace Modules\Team\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Team\Entities\Team;
use Illuminate\Support\Facades\Storage;


class TeamController extends Controller
{

    public function index()
    {
        $teams = Team::with(['captain', 'sport', 'profiles'])->get();
        return $this->successResponse($teams, 'Teams fetched successfully');
    }


    public function store(Request $request)
{
    $data = $request->validate([
        'name' => 'required|string|max:255',
        'captin_id' => 'required|exists:users,id',
        'sport_id' => 'required|exists:sports,id',
        'logo' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
    ]);

    // التحقق أن الكابتن ما عنده فريق
    $existingTeam = Team::where('captin_id', $data['captin_id'])->first();
    if ($existingTeam) {
        return $this->errorResponse('This captain already has a team', 400);
    }

    // حفظ صورة الشعار إذا وجدت
    if ($request->hasFile('logo')) {
        $data['logo'] = $request->file('logo')->store('logos', 'public');
    }

    // إنشاء الفريق
    $team = Team::create($data);

    // تحديث البروفايل وربط الكابتن بالفريق
    $profile = \App\Models\Profile::where('user_id', $data['captin_id'])->first();
    if ($profile) {
        $profile->update(['team_id' => $team->id]);
    }

    return $this->successResponse($team, 'Team created successfully', 201);
}



    public function show($id)
    {
        $team = Team::with(['captain', 'sport', 'profiles'])->find($id);

        if (!$team) {
            return $this->errorResponse('Team not found', 404);
        }

        return $this->successResponse($team, 'Team fetched successfully');
    }


    public function update(Request $request, $id)
    {
        $team = Team::find($id);

        if (!$team) {
            return $this->errorResponse('Team not found', 404);
        }

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'captin_id' => 'sometimes|exists:users,id',
            'sport_id' => 'sometimes|exists:sports,id',
             'logo' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);
       if ($request->hasFile('logo')) {

    if ($team->logo && Storage::disk('public')->exists($team->logo)) {
        Storage::disk('public')->delete($team->logo);
    }

    $data['logo'] = $request->file('logo')->store('logos', 'public');
}

        $team->update($data);

        return $this->successResponse($team->fresh(), 'Team updated successfully');
    }

    public function destroy($id)
    {
        $team = Team::find($id);

        if (!$team) {
            return $this->errorResponse('Team not found', 404);
        }

        $team->delete();

        return $this->successResponse(null, 'Team deleted successfully');
    }


    protected function successResponse($data, $message = 'Operation successful', $code = 200)
    {
        return response()->json([
            'status' => true,
            'status_code' => $code,
            'message' => $message,
            'data' => $data
        ], $code);
    }


    protected function errorResponse($message = 'Something went wrong', $code = 400)
    {
        return response()->json([
            'status' => false,
            'status_code' => $code,
            'message' => $message,
            'data' => null
        ], $code);
    }

    public function teamStats($teamId)
    {
        $team = Team::with(['matchResults.invitationMatch.league'])->findOrFail($teamId);

        $leagues = $team->matchResults->map(fn($matchResult) => $matchResult->invitationMatch->league)
            ->unique('id')->values();

        $winsCount = $team->matchResults->where('is_winnerstatus', 'winner')->count();

        return response()->json([
            'team' => $team->name,
            'leagues' => $leagues,
            'wins_count' => $winsCount,
        ]);
    }
}
