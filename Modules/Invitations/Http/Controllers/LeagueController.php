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

    // public function index()
    // {
    //     $data = League::all();
    //     return $this->successResponse($data, 'All leagues retrieved successfully');
    // }
public function index()
{
    // جلب الدوريات مع الملعب والرياضة (فقط الاسم)
    $data = League::with([
        'stadium.sport' => function ($query) {
            $query->select('id', 'name'); // بس ID + الاسم
        }
    ])->get();

    // تجهيز النتيجة
    $data = $data->map(function ($league) {
        return [
            'id' => $league->id,
            'name' => $league->name,
            'description' => $league->description,
            'start_date' => $league->start_date,
            'end_date' => $league->end_date,
            'price' => $league->price,
            'prize' => $league->prize,
            'status' => $league->status,
            'created_by' => $league->created_by,
            'stadium' => $league->stadium, // كل بيانات الملعب
            'sport_name' => optional($league->stadium->sport)->name, // فقط الاسم
        ];
    });

    return $this->successResponse($data, 'All leagues retrieved successfully');
}




    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string',
                'description' => 'nullable|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'price' => 'required|numeric',
                'prize' => 'required|string',
                'status' => 'required|in:pending,active,finished',
                'stadium_id' => 'required|exists:stadiums,id',
            ]);

            $stadium = \Modules\Stadium\Entities\Stadium::find($data['stadium_id']);
            if ($stadium->user_id !== auth()->id()) {
             return $this->errorResponse('You do not own this stadium', 403);
}

            $data['created_by'] = auth()->id();
            $league = League::create($data);

            return $this->successResponse($league, 'League created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function show($id)
{
    $league = League::with(['stadium.sport' => function ($q) {
        $q->select('id', 'name');
    }])->find($id);

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

        if ($league->stadium->user_id !== auth()->id()) {
    return $this->errorResponse('You do not own this stadium', 403);
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
    if ($league->stadium->user_id !== auth()->id()) {
    return $this->errorResponse('You do not own this stadium', 403);
}

    // التحقق من المالك أو الدور
    if ($league->created_by !== auth()->id() && !auth()->user()->hasRole('stadium_owner')) {
        return $this->errorResponse('Unauthorized', 403);
    }

    // تنفيذ الحذف
    $league->delete();

    return $this->successResponse(null, 'League deleted successfully');
    }



public function myLeagues(Request $request)
{
    // بناء الكويري حسب المستخدم الحالي
    $query = League::where('created_by', auth()->id());

    // فلترة اختيارية حسب الحالة
    if ($request->has('status')) {
        $query->where('status', $request->status);
    }

    $leagues = $query->get();

    if ($leagues->isEmpty()) {
        return $this->errorResponse('No leagues found for the current user', 404);
    }

    return $this->successResponse($leagues, 'Your leagues retrieved successfully');
}



public function leaguesByStadium(Request $request, $stadium_id)
{
    // جلب الدوريات الخاصة بالملعب المحدد وحالته approved
    $leagues = League::where('stadium_id', $stadium_id)
                     ->where('status', 'approved')
                     ->get();

    if ($leagues->isEmpty()) {
        return $this->errorResponse('No approved leagues found for this stadium', 404);
    }

    return $this->successResponse($leagues, 'Approved leagues for the stadium retrieved successfully');
}




}
