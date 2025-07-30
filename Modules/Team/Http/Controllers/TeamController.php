<?php

namespace Modules\Team\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Team\Entities\Team;
use Illuminate\Support\Facades\Storage;


class TeamController extends Controller
{
    // ✅ عرض كل الفرق
    public function index()
    {
        $teams = Team::with(['captain', 'sport', 'profiles'])->get();
        return $this->successResponse($teams, 'Teams fetched successfully');
    }

    // ✅ إنشاء فريق
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'captin_id' => 'required|exists:users,id',
            'sport_id' => 'required|exists:sports,id',
             'logo' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);
       $existingTeam = Team::where('captin_id', $data['captin_id'])->first();
       if ($existingTeam) {
        return $this->errorResponse('This captain already has a team', 400);
    }
    $existingTeam = Team::where('captin_id', $data['captin_id'])->first();
    if ($existingTeam) {
        return $this->errorResponse('This captain already has a team', 400);
    }

    // رفع الصورة
    if ($request->hasFile('logo')) {
        $data['logo'] = $request->file('logo')->store('logos', 'public');
    }

        $team = Team::create($data);

        return $this->successResponse($team, 'Team created successfully', 201);
    }

    // ✅ عرض فريق معيّن
    public function show($id)
    {
        $team = Team::with(['captain', 'sport', 'profiles'])->find($id);

        if (!$team) {
            return $this->errorResponse('Team not found', 404);
        }

        return $this->successResponse($team, 'Team fetched successfully');
    }

    // ✅ تعديل فريق
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
    // حذف الصورة القديمة إذا موجودة
    if ($team->logo && Storage::disk('public')->exists($team->logo)) {
        Storage::disk('public')->delete($team->logo);
    }
    // حفظ الصورة الجديدة
    $data['logo'] = $request->file('logo')->store('logos', 'public');
}

        $team->update($data);

        return $this->successResponse($team->fresh(), 'Team updated successfully');
    }

    // ✅ حذف فريق
    public function destroy($id)
    {
        $team = Team::find($id);

        if (!$team) {
            return $this->errorResponse('Team not found', 404);
        }

        $team->delete();

        return $this->successResponse(null, 'Team deleted successfully');
    }

    // ✅ دالة للرد الناجح
    protected function successResponse($data, $message = 'Operation successful', $code = 200)
    {
        return response()->json([
            'status' => true,
            'status_code' => $code,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    // ✅ دالة للرد بالفشل
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
