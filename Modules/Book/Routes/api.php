<?php

use Illuminate\Http\Request;
use Modules\Book\Http\Controllers\BookController;
use Modules\Book\Http\Controllers\PaymentController;
use Modules\Book\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\AuthController;


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

Route::middleware('auth:api')->get('/book', function (Request $request) {
    return $request->user();
});



Route::prefix('Booking')->middleware(['auth:api'])->group(function () {
    Route::post('/create', [BookController::class, 'book']);
    Route::get('/view/{id}', [BookController::class, 'view']);
    Route::get('/viewAll/{id}', [BookController::class, 'viewAll'])->middleware('role:stadium_owner');
    Route::delete('/delete/{id}', [BookController::class, 'cancel'])->middleware('role:player|stadium_owner');
    Route::post('/pay', [PaymentController::class, 'pay']);

});
Route::get('/stripe/onboarding/return', [StripeWebhookController::class, 'handleOnboardingReturn']);
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook']);
Route::get('/stripe/onboarding/refresh', [StripeWebhookController::class, 'refreshOnboarding']);

