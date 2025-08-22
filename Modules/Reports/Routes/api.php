<?php

use Illuminate\Support\Facades\Route;
use Modules\Reports\Http\Controllers\ReportsController;

// Route::middleware('auth:api')->group(function () {
//     Route::post('/reports', [ReportsController::class,'store']);
//     Route::get('/reports', [ReportsController::class,'index']);
//     Route::patch('/reports/{report}', [ReportsController::class,'updateStatus']);
// });
Route::prefix('reports')->middleware(['auth:api'])->group(function () {

    // Stadium owner creates a report
    Route::post('/', [ReportsController::class, 'store']);

    // Admin fetches all reports
    Route::get('/', [ReportsController::class, 'index']);

    // Admin updates report status (notified / banned)
    Route::patch('/{report}', [ReportsController::class, 'updateStatus']);
});
