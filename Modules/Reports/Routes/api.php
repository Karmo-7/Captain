<?php

use Illuminate\Support\Facades\Route;
use Modules\Reports\Http\Controllers\ReportsController;
use Modules\Notifications\Http\Controllers\NotificationsController;

// Reports Routes
Route::prefix('reports')->middleware(['auth:api'])->group(function () {

    // Stadium owner creates a report
    Route::post('/', [ReportsController::class, 'store']);

    // Admin fetches all reports
    Route::get('/', [ReportsController::class, 'index']);

    // Admin updates report status (notified / banned)
    Route::patch('/{report}/status', [ReportsController::class, 'updateStatus']);
      // Ban player
    Route::post('/{reportId}/ban', [ReportsController::class, 'banPlayer']);
     Route::post('/{reportId}/unban', [ReportsController::class, 'unbanPlayer']);
});

// Notifications Routes
Route::prefix('notifications')->middleware(['auth:api'])->group(function () {

    // Get all notifications for the logged-in user
    Route::get('/', [NotificationsController::class, 'index']);

    // Get only unread notifications
    Route::get('/unread', [NotificationsController::class, 'unread']);

    // Mark a specific notification as read
    Route::patch('/{id}/mark-as-read', [NotificationsController::class, 'markAsRead']);

    // Mark all notifications as read
    Route::patch('/mark-all-as-read', [NotificationsController::class, 'markAllAsRead']);

});
Route::get('/players/{playerId}/ban-status', [ReportsController::class, 'checkBanStatus'])
    ->middleware('auth:api');
