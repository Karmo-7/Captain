<?php

namespace Modules\Reports\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Reports\Entities\Report;
use Illuminate\Support\Facades\Auth;
use Modules\Reports\Notifications\PlayerNotified;

class ReportsController extends Controller
{
    // Unified API responses
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

    // Create report by stadium owner
    public function store(Request $request)
    {
        $data = $request->all();
        $data['stadium_owner_id'] = auth()->id(); // يسجل من أرسل التقرير
        $report = Report::create($data);

        return $this->successResponse($report, 'Report created successfully', 201);
    }

    // Get all reports
    public function index()
    {
        $reports = Report::with(['player','stadiumOwner'])->get();
        return $this->successResponse($reports, 'All reports retrieved successfully');
    }

    // Update report status
    public function updateStatus(Request $request, Report $report)
    {
        $data = $request->all();
        $report->update($data);

        if(isset($data['status']) && $data['status'] === 'notified') {
            $report->player->notify(new PlayerNotified($report));
        }

        if(isset($data['status']) && $data['status'] === 'banned') {
            $report->player->update(['is_banned' => true]);
        }

        return $this->successResponse($report, 'Report status updated successfully');
    }
}
