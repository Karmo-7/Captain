<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Team\Http\Controllers\TeamController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->group(function () {

    // للحصول على بيانات المستخدم
    Route::get('/team', function (Request $request) {
        return $request->user();
    });

    // جميع عمليات teams
    Route::apiResource('teams', TeamController::class);

    // إحصائيات الفريق
    Route::get('/teams/{teamId}/stats', [TeamController::class, 'teamStats']);

    // تحديث الفريق
    Route::post('teams/{team}/update', [TeamController::class, 'update']);
});
