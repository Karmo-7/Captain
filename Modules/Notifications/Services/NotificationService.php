<?php

namespace Modules\Notifications\Services;

use Modules\Notifications\Entities\Notification;
use Illuminate\Database\Eloquent\Model;

class NotificationService
{
    public static function send(
        int $userId,
        string $title,
        ?string $description,
        string $type,
        ?Model $model = null
    ): Notification {
        return Notification::create([
            'title' => $title,
            'description' => $description,
            'user_id' => $userId,
            'type' => $type,
            'notifiable_type' => $model ? get_class($model) : null,
            'notifiable_id' => $model ? $model->id : null,
        ]);
    }

    public static function getUserNotifications(int $userId, bool $onlyUnread = false)
    {
        $query = Notification::with('notifiable')->where('user_id', $userId)->latest();
        return $onlyUnread ? $query->unread()->get() : $query->get();
    }

    public static function markAsRead(int $notificationId): bool
    {
        $notification = Notification::find($notificationId);
        if (!$notification) return false;
        $notification->update(['read_at' => now()]);
        return true;
    }

    public static function markAllAsRead(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
