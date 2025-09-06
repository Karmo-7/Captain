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
    /**
     * Respnses: نجاح
     */
    protected function successResponse($data, $message = 'Operation successful', $code = 200)
    {
        return response()->json([
            'status'      => true,
            'status_code' => $code,
            'message'     => $message,
            'data'        => $data
        ], $code);
    }

    /**
     * Responses: خطأ
     */
    protected function errorResponse($message = 'Something went wrong', $code = 400, $data = null)
    {
        return response()->json([
            'status'      => false,
            'status_code' => $code,
            'message'     => $message,
            'data'        => $data
        ], $code);
    }

    /**
     * إنشاء تقرير جديد
     */
public function store(Request $request)
{
    // ✅ تحقق من صحة البيانات
    $validated = $request->validate([
        'player_id' => 'required|exists:users,id',
        'reason'    => 'required|string|max:255',
    ]);

    $validated['stadium_owner_id'] = auth()->id(); // مالك الملعب
    $validated['status'] = 'pending';

    // ✅ التحقق من وجود تقرير سابق لنفس اللاعب ولنفس السبب
    $existingReport = Report::where('stadium_owner_id', auth()->id())
        ->where('player_id', $request->player_id)
        ->where('reason', $request->reason)
        ->whereIn('status', ['pending', 'notified', 'banned']) // لو التقرير مفتوح أو معلق → نمنع التكرار
        ->first();

    if ($existingReport) {
        return $this->errorResponse(
            'لقد قمت بإنشاء تقرير سابق ضد هذا اللاعب لنفس السبب، ولا يمكنك تكرار التقرير',
            409,
            $existingReport
        );
    }

    // ✅ اختيار أي Admin موجود تلقائياً باستخدام Spatie Roles
    $admin = User::role('admin')->first();
    if (!$admin) {
        return $this->errorResponse('No admin found to assign the report', 404);
    }

    $validated['admin_id'] = $admin->id;

    // ✅ إنشاء التقرير الجديد
    $report = Report::create($validated);

    // إرسال إشعار عند إنشاء التقرير
    ReportNotificationService::sendReportCreatedNotification($report);

    // جلب التقرير مع العلاقات
    $report = Report::with(['player', 'stadiumOwner', 'admin'])->find($report->id);

    return $this->successResponse($report, 'Report created successfully', 201);
}




    /**
     * جلب جميع التقارير
     */
   public function index()
{
    $user = auth()->user();

    $reports = Report::with(['player', 'stadiumOwner', 'admin'])
        ->where('stadium_owner_id', $user->id)
        ->get();

    return $this->successResponse($reports, 'Reports retrieved successfully for the current user');
}

    /**
     * تحديث حالة التقرير
     */
    public function updateStatus(Request $request, Report $report)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,notified,banned,resolved'
        ]);

        $report->update(['status' => $validated['status']]);

        // إذا تم إشعار اللاعب
        if ($validated['status'] === 'notified') {
            ReportNotificationService::sendPlayerNotifiedNotification($report);
        }

        // إذا تم حظر اللاعب
        if ($validated['status'] === 'banned') {
            $report->player->update(['is_banned' => true]);
            ReportNotificationService::sendPlayerBannedNotification($report);
        }

        return $this->successResponse([
            'report' => $report,
           // 'player' => $report->player
        ], 'Report status updated successfully');
    }

    /**
     * فحص حالة حظر اللاعب
     */
    public function checkBanStatus($playerId)
    {
        $player = User::find($playerId);

        if (!$player) {
            return response()->json([
                'status' => false,
                'message' => 'Player not found',
                'is_banned' => null
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Player ban status retrieved successfully',
            'player_id' => $player->id,
            'is_banned' => (bool) $player->is_banned
        ]);
    }

      public function banPlayer($reportId)
    {
        $player = ReportService::banPlayerFromReport($reportId);

        return response()->json([
            'status' => true,
            'message' => 'Player has been banned successfully',
            'player_id' => $player->id,
            'is_banned' => $player->is_banned,
        ]);
    }

    public function unbanPlayer($reportId)
{
    $player = ReportService::unbanPlayerFromReport($reportId);

    return response()->json([
        'status' => true,
        'message' => 'Player has been unbanned successfully',
        'player_id' => $player->id,
        'is_banned' => $player->is_banned,
    ]);
}

}
