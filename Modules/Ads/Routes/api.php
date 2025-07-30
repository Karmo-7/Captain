<?php

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;
use Modules\Ads\Http\Controllers\AdController;

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

// Route::middleware('auth:api')->get('/ads', function (Request $request) {
//     return $request->user();
// });
// Route::apiResource('ads', AdController::class);




Route::middleware('auth:api')->group(function () {

    // إذا حابب ترجع بيانات المستخدم الحالي
    Route::get('/user', function (\Illuminate\Http\Request $request) {
        return $request->user();
    });

    // كل CRUD للإعلانات تحت auth
    Route::apiResource('ads', AdController::class);
});
