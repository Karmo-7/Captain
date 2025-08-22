<?php

use Illuminate\Http\Request;
use Modules\Facilities\Http\Controllers\FacilityController;
use Illuminate\Support\Facades\Route;
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

Route::middleware('auth:api')->get('/facilities', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:api'])->prefix('/facilities')->group(function (){
    Route::post('/create',[FacilityController::class,'create'])->middleware('role:stadium_owner');
    Route::post('/update/{id}', [FacilityController::class, 'update'])->middleware('role:stadium_owner');
    Route::get('/view/{id}', [FacilityController::class, 'view']);
    Route::get('/viewall/{id}', [FacilityController::class, 'viewall']);
    Route::delete('/delete/{id}', [FacilityController::class, 'delete'])->middleware('role:stadium_owner');


});
