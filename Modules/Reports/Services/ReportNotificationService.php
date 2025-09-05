<?php

namespace Modules\Reports\Services;

use Modules\Notifications\Entities\Notification;
use Modules\Reports\Entities\Report;

class ReportNotificationService
{
    /**
     * Send a notification when a new report is created
     */
    public static function sendReportCreatedNotification(Report $report): Notification
    {
        return Notification::create([
            'title'           => 'You have been reported',
            'description'     => "Reason: {$report->reason}",
            'user_id'         => $report->player_id,
            'type'            => Notification::TYPE['PLAYER_REPORTED'],
            'notifiable_type' => Report::class,
            'notifiable_id'   => $report->id,
        ]);
    }

    /**
     * Send a notification when the player has been notified about the report
     */
    public static function sendPlayerNotifiedNotification(Report $report): Notification
    {
        return Notification::create([
            'title'           => 'Your report has been reviewed',
            'description'     => "The player has been notified regarding the report.",
            'user_id'         => $report->stadium_owner_id,
            'type'            => Notification::TYPE['PLAYER_NOTIFIED'],
            'notifiable_type' => Report::class,
            'notifiable_id'   => $report->id,
        ]);
    }

    /**
     * Send a notification when the player is banned
     */
    public static function sendPlayerBannedNotification(Report $report): Notification
    {
        return Notification::create([
            'title'           => 'You have been banned',
            'description'     => "You have been banned due to a report from the stadium owner.",
            'user_id'         => $report->player_id,
            'type'            => Notification::TYPE['PLAYER_BANNED'],
            'notifiable_type' => Report::class,
            'notifiable_id'   => $report->id,
        ]);
    }
    public static function sendPlayerUnbannedNotification(Report $report): Notification
{
    return Notification::create([
        'title'           => 'تم رفع الحظر عنك',
        'description'     => 'لقد تم رفع الحظر عنك بناءً على تقرير مالك الملعب.',
        'user_id'         => $report->player_id,
        'type'            => 'player_unbanned', // يمكنك إضافة نوع جديد في Notification::TYPE
        'notifiable_type' => Report::class,
        'notifiable_id'   => $report->id,
    ]);
}

}
