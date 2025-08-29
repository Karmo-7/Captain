<?php

use Illuminate\Support\Facades\Route;
use Modules\Rating\Http\Controllers\StadiumRatingController;
use Modules\Rating\Http\Controllers\FacilityRatingController;

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

    // Stadium Ratings
    Route::post('/stadiums/rate', [StadiumRatingController::class, 'store']);
    Route::get('/stadiums/{stadium}/ratings', [StadiumRatingController::class, 'index']);

    // Facility Ratings
    Route::post('/facilities/rate', [FacilityRatingController::class, 'store']);
    Route::get('/facilities/{facility}/ratings', [FacilityRatingController::class, 'index']);
});
