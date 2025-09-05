<?php

namespace Modules\Stadium\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Stadium\Entities\Stadium;
use Modules\Invitations\Entities\League;

class StadiumSearchController extends Controller
{
    public function search(Request $request)
    {
        $sportId = $request->sport_id;
        $query   = $request->search;

        // ====== فلترة الملاعب ======
        $stadiums = Stadium::with('sport')
            ->when($sportId, fn($q) => $q->where('sport_id', $sportId))
            ->when($query, fn($q) => $q->where('name', 'like', "%$query%"))
            ->get();

        $stadiumIds = $stadiums->pluck('id');

        // ====== جلب الدوريات المرتبطة بالملاعب المفلترة ======
$leagues = League::with('stadium.sport')
    ->when($sportId, fn($q) => $q->whereHas('stadium', fn($sq) => $sq->where('sport_id', $sportId)))
    ->get();




        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Search results fetched successfully',
            'data' => [
                'stadiums' => $stadiums,
                'leagues'  => $leagues,
            ]
        ]);
    }
}
