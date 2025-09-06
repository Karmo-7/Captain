<?php

namespace Modules\Reports\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Reports\Entities\Report;
use Modules\Reports\Services\ReportNotificationService;
use Modules\Reports\Services\ReportService;

class ReportsController extends Controller
{
    protected function successResponse($data, $message = 'Operation successful', $code = 200)
    {
        return response()->json([
            'status'      => true,
            'status_code' => $code,
            'message'     => $message,
            'data'        => $data
        ], $code);
    }

    protected function errorResponse($message = 'Something went wrong', $code = 400, $data = null)
    {
        return response()->json([
            'status'      => false,
            'status_code' => $code,
            'message'     => $message,
            'data'        => $data
        ], $code);
    }

    // إنشاء تقرير جديد
    public function store(Request $request)
    {
        $validated = $request->validate([
            'player_id' => 'required|exists:users,id',
            'reason'    => 'required|string|max:255',
        ]);

        $validated['stadium_owner_id'] = auth()->id();
        $validated['status'] = 'pending';

        $existingReport = Report::where('stadium_owner_id', auth()->id())
            ->where('player_id', $request->player_id)
            ->where('reason', $request->reason)
            ->whereIn('status', ['pending', 'notified', 'banned'])
            ->first();

        if ($existingReport) {
            return $this->errorResponse(
                'لقد قمت بإنشاء تقرير سابق ضد هذا اللاعب لنفس السبب، ولا يمكنك تكرار التقرير',
                409,
                $existingReport
            );
        }

        $admin = User::role('admin')->first();
        if (!$admin) {
            return $this->errorResponse('No admin found to assign the report', 404);
        }

        $validated['admin_id'] = $admin->id;

        $report = Report::create($validated);

        // إشعار Admin
        ReportNotificationService::sendReportCreatedNotification($report);

        $report = Report::with(['player', 'stadiumOwner', 'admin'])->find($report->id);

        return $this->successResponse($report, 'Report created successfully', 201);
    }

    // جلب جميع التقارير
    public function index()
    {
        $user = auth()->user();

        $reports = Report::with(['player', 'stadiumOwner', 'admin'])
            ->where('stadium_owner_id', $user->id)
            ->get();

        return $this->successResponse($reports, 'Reports retrieved successfully for the current user');
    }

    // تحديث حالة التقرير (notified / banned / resolved / warning)
    public function updateStatus(Request $request, Report $report)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,notified,banned,resolved,warning'
        ]);

        $report->update(['status' => $validated['status']]);

        switch ($validated['status']) {
            case 'notified':
                // إشعار صاحب الملعب أن التقرير تمت مراجعته
                ReportNotificationService::sendStadiumOwnerReportReviewedNotification($report);
                break;
            case 'banned':
                $report->player->update(['is_banned' => true]);
                ReportNotificationService::sendPlayerBannedNotification($report);
                break;
            case 'warning':
                ReportNotificationService::sendPlayerWarningNotification($report);
                break;
        }

        return $this->successResponse([
            'report' => $report
        ], 'Report status updated successfully');
    }

    // فحص حالة حظر اللاعب
    public function checkBanStatus($playerId)
    {
        $player = User::find($playerId);

        if (!$player) {
            return $this->errorResponse('Player not found', 404);
        }

        return $this->successResponse([
            'player_id' => $player->id,
            'is_banned' => (bool) $player->is_banned
        ], 'Player ban status retrieved successfully');
    }

    public function banPlayer($reportId)
    {
        $player = ReportService::banPlayerFromReport($reportId);

        return $this->successResponse([
            'player_id' => $player->id,
            'is_banned' => $player->is_banned
        ], 'Player has been banned successfully');
    }

    public function unbanPlayer($reportId)
    {
        $player = ReportService::unbanPlayerFromReport($reportId);

        return $this->successResponse([
            'player_id' => $player->id,
            'is_banned' => $player->is_banned
        ], 'Player has been unbanned successfully');
    }
}
