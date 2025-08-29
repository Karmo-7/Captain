<?php
use Modules\Book\Http\Controllers\StripeWebhookController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('book')->group(function() {
    Route::get('/', 'BookController@index');
});
Route::get('/stripe/onboarding/refresh', [StripeWebhookController::class, 'refreshOnboarding']);
Route::get('/stripe/onboarding/return', [StripeWebhookController::class, 'handleOnboardingReturn']);
