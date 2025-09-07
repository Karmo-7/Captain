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

    // تحديث حالة التقرير تلقائياً
    $report->status = 'banned';
    $report->save();

    // إرسال إشعار الحظر
    ReportNotificationService::sendPlayerBannedNotification($report);

    return $player;
}



   public static function unbanPlayerFromReport(int $reportId)
{
    $report = Report::findOrFail($reportId);
    $player = User::findOrFail($report->player_id);

    $player->is_banned = false;
    $player->save();

    // تحديث التقرير تلقائياً إذا كان مرتبط بالحظر
    if($report->status === 'unbanned') {
        $report->status = 'resolved';
        $report->save();
    }

    ReportNotificationService::sendPlayerUnbannedNotification($report);

    return $player;
}


public static function warnPlayerFromReport(int $reportId)
{
    $report = Report::findOrFail($reportId);
    $player = User::findOrFail($report->player_id);

    // تحديث حالة التقرير إلى warning
    $report->status = 'warning';
    $report->save();

    // إرسال إشعار تنبيه
    ReportNotificationService::sendPlayerWarningNotification($report);

    return $report;
}


}
