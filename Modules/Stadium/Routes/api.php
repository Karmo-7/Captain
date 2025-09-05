<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Stadium\Http\Controllers\StadiumController;
use Modules\Stadium\Http\Controllers\StadiumRequestController;
use Modules\Stadium\Http\Controllers\StadiumSearchController;
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
Route::get('/stadiums-leagues/search', [StadiumSearchController::class, 'search']);

});


Route::middleware(['auth:api','role:admin'])->prefix('stadium')->group(function(){
    Route::post('/replyask/{id}',[StadiumRequestController::class,'ReplyAsk']);
    Route::get('/viewAllRequest',[StadiumRequestController::class,'viewall']);
    Route::get('/get_all_owners', [StadiumController::class, 'get_all_owmer']);



});

Route::prefix('stadium')->middleware(['auth:api', 'role:stadium_owner|admin'])->group(function () {
    Route::delete('/delete/{id}', [StadiumController::class, 'delete']);
    Route::delete('/deleteRequest/{id}', [StadiumRequestController::class, 'deleteRequest']);
});


Route::middleware(['auth:api', 'role:stadium_owner'])->prefix('stadium')->group(function () {
    Route::post('/addrequest', [StadiumRequestController::class,'AddRequest']);
    Route::post('/update/{id}', [StadiumController::class, 'update']);
    Route::get('/view_my_asks', [StadiumRequestController::class, 'view_my_asks']);
    Route::get('/view_my_stadium', [StadiumRequestController::class, 'view_my_stadium']);


});

Route::prefix('stadium')->middleware('auth:api')->group(function () {
    Route::get('/viewRequest/{id}', [StadiumRequestController::class, 'view']);
    Route::get('/viewall', [StadiumController::class, 'viewall']);
    Route::get('/view/{id}', [StadiumController::class, 'view']);


// Route::get('/stadium/viewRequest/{id}', [StadiumRequestController::class, 'view'])->middleware('auth:api');


    Route::get('/nearby', [StadiumController::class,'nearstadium']);
    Route::get('/filter', [StadiumController::class, 'filter']);

    //this is for testing

});
Route::post('stadium-slots/generate/{stadium_id}', [StadiumSlotController::class, 'generateSlots']);
