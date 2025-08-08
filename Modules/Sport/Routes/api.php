<?php

use Illuminate\Http\Request;
use Modules\Sport\Http\Controllers\SportController;
use Illuminate\Support\Facades\Route;
use Modules\Sport\Http\Controllers\MatchSlotController;


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

Route::middleware(['auth:api'])->prefix('sport')->group(function () {
    Route::post('/create', [SportController::class, 'create'])->middleware('role:admin');
    Route::post('/update/{id}',[SportController::class,'update'])->middleware('role:admin');;
    Route::delete('/delete/{id}',[SportController::class,'delete'])->middleware('role:admin');;
    Route::get('/view/{id}',[SportController::class,'view']);
    Route::get('/viewall',[SportController::class,'viewall']);


  
});
