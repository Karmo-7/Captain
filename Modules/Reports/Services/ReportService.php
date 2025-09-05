<?php

namespace Modules\Reports\Services;

use Modules\Reports\Entities\Report;
use App\Models\User;

class ReportService
{
    public static function banPlayerFromReport(int $reportId)
    {
        $report = Report::findOrFail($reportId);
        $player = User::findOrFail($report->player_id);

        // تحديث حالة الحظر
        $player->is_banned = true;
        $player->save();

        // إرسال إشعار الحظر
        ReportNotificationService::sendPlayerBannedNotification($report);

        return $player;
    }

    public static function unbanPlayerFromReport(int $reportId)
{
    $report = Report::findOrFail($reportId);
    $player = User::findOrFail($report->player_id);

    // تحديث حالة الحظر إلى false
    $player->is_banned = false;
    $player->save();

    // هنا يمكنك إرسال إشعار لفك الحظر إذا أردت
     ReportNotificationService::sendPlayerUnbannedNotification($report);

    return $player;
}

}
