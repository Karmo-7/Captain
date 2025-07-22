<?php

use Illuminate\Http\Request;
use Modules\Stadium\Http\Controllers\StadiumController;
use Modules\Stadium\Http\Controllers\StadiumRequestController;

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

Route::middleware('auth:api')->get('/stadium', function (Request $request) {
    return $request->user();
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

});
