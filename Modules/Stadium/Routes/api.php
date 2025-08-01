<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Stadium\Http\Controllers\StadiumController;
use Modules\Stadium\Http\Controllers\StadiumRequestController;
use Modules\Stadium\Http\Controllers\StadiumSlotController;

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

    Route::get('/stadium', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('stadium-slots', StadiumSlotController::class);
    Route::get('stadium-slots/stadium/{stadium_id}', [StadiumSlotController::class, 'getSlotsByStadium']);


});


Route::middleware(['auth:api','role:admin'])->prefix('stadium')->group(function(){
    Route::post('/replyask/{id}',[StadiumRequestController::class,'ReplyAsk']);
    Route::get('/viewAllRequest',[StadiumRequestController::class,'viewall']);

});

Route::delete('/deleteRequest/{id}', [StadiumRequestController::class, 'deleteRequest'])
    ->middleware(['auth:api', 'role:admin|stadium_owner']);

Route::middleware(['auth:api', 'role:stadium_owner'])->prefix('stadium')->group(function () {
    Route::post('/addrequest', [StadiumRequestController::class,'AddRequest']);
    Route::post('/update/{id}', [StadiumController::class, 'update']);



});

Route::prefix('stadium')->middleware('auth:api')->group(function () {
    Route::get('/viewRequest/{id}', [StadiumRequestController::class, 'view']);
    Route::get('/viewall', [StadiumController::class, 'viewall']);
    Route::get('/view/{id}', [StadiumController::class, 'view']);
    Route::delete('/delete/{id}', [StadiumController::class, 'delete'])
        ->middleware('role:stadium_owner');

Route::get('/stadium/viewRequest/{id}', [StadiumRequestController::class, 'view'])->middleware('auth:api');



});
