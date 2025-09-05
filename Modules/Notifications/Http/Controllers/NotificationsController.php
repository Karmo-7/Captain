<?php

namespace Modules\Notifications\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Notifications\Entities\Notification;
use Modules\Notifications\Services\NotificationService;

class NotificationsController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();
        $notifications = NotificationService::getUserNotifications($userId);
        return response()->json(['status' => true, 'data' => $notifications]);
    }

    public function unread(Request $request)
    {
        $userId = auth()->id();
        $notifications = NotificationService::getUserNotifications($userId, true);
        return response()->json(['status' => true, 'data' => $notifications]);
    }

    public function markAsRead($id)
    {
        $success = NotificationService::markAsRead($id);
        return response()->json(['status' => $success]);
    }

    public function markAllAsRead()
    {
        $userId = auth()->id();
        $count = NotificationService::markAllAsRead($userId);
        return response()->json(['status' => true, 'updated_count' => $count]);
    }
}
