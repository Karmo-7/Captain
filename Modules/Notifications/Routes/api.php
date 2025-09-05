<?php

use Illuminate\Support\Facades\Route;
use Modules\Notifications\Http\Controllers\NotificationsController;




Route::middleware('auth:api')->prefix('notifications')->group(function () {
    Route::get('/', [NotificationsController::class, 'index']);
    Route::get('/unread', [NotificationsController::class, 'unread']);
    Route::post('/{id}/read', [NotificationsController::class, 'markAsRead']);
    Route::post('/mark-all-read', [NotificationsController::class, 'markAllAsRead']);
});

