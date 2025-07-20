<?php

use Illuminate\Http\Request;
use Modules\Sport\Http\Controllers\SportController;

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

Route::middleware(['auth:api', 'role:admin'])->prefix('sport')->group(function () {
    Route::post('/create', [SportController::class, 'create']);
    Route::post('/update/{id}',[SportController::class,'update']);



});
