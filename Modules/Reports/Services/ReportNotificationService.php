<?php

namespace Modules\Reports\Services;

use Modules\Notifications\Entities\Notification;
use Modules\Reports\Entities\Report;

class ReportNotificationService
{
    /**
     * إشعار Admin عند إنشاء تقرير جديد
     */
    public static function sendReportCreatedNotification(Report $report): Notification
    {
        return Notification::create([
            'title'           => 'New Player Report',
            'description'     => "Player ID: {$report->player_id}, Reason: {$report->reason}",
            'user_id'         => $report->admin_id,  // الآن يرسل للإدمن
            'type'            => Notification::TYPE['PLAYER_REPORTED'],
            'notifiable_type' => Report::class,
            'notifiable_id'   => $report->id,
        ]);
    }

    /**
     * إشعار صاحب الملعب بأن تقريره تمت مراجعته
     */
    public static function sendStadiumOwnerReportReviewedNotification(Report $report): Notification
    {
        return Notification::create([
            'title'           => 'Report Reviewed',
            'description'     => "Your report against player ID {$report->player_id} has been reviewed by the Admin.",
            'user_id'         => $report->stadium_owner_id,
            'type'            => 'report_reviewed', // يمكن إضافة نوع جديد في Notification::TYPE
            'notifiable_type' => Report::class,
            'notifiable_id'   => $report->id,
        ]);
    }

    /**
     * إشعار اللاعب عند حظر الحساب
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

    /**
     * إشعار اللاعب عند رفع الحظر
     */
    public static function sendPlayerUnbannedNotification(Report $report): Notification
    {
        return Notification::create([
            'title'           => 'تم رفع الحظر عنك',
            'description'     => 'تم رفع الحظر عنك بناءً على قرار الإدارة.',
            'user_id'         => $report->player_id,
            'type'            => Notification::TYPE['PLAYER_UNBANNED'],
            'notifiable_type' => Report::class,
            'notifiable_id'   => $report->id,
        ]);
    }

    /**
     * إشعار اللاعب بتنبيه دون حظر
     */
    public static function sendPlayerWarningNotification(Report $report): Notification
    {
        return Notification::create([
            'title'           => 'Warning',
            'description'     => "You received a warning regarding your behavior reported by the stadium owner.",
            'user_id'         => $report->player_id,
            'type'            => 'PLAYER_WARNING', // أضف هذا النوع في Notification::TYPE
            'notifiable_type' => Report::class,
            'notifiable_id'   => $report->id,
        ]);
    }
}
