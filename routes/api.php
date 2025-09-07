<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\ProfileController;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;
use App\Models\User;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/users/{id}', function ($id) {
    return User::with('ads')->findOrFail($id);
});
Route::middleware('throttle:auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/password/forgot', [AuthController::class, 'forgotPassword']);
    Route::post('/password/reset', [AuthController::class, 'resetPassword']);
    Route::post('/auth/google-mobile', [AuthController::class, 'loginWithGoogleMobile']);
    Route::post('/email/resend', [AuthController::class, 'resendVerification']);
});

Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['signed']) // ضروري للتحقق من التوقيع
    ->name('verification.verify');
Route::middleware(['auth:api', 'throttle:api'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

});
    Route::middleware(['auth:api', 'role:admin'])->get('/admin', function () {
        return 'مرحبا أيها المدير';
    });

Route::middleware(['auth:api'])->group(function () {


    Route::prefix('profile')->middleware(['role:player'])->group(function () {
        Route::post('/create', [ProfileController::class, 'create']);
        Route::put('/update/{id}', [ProfileController::class, 'update']);
    });


    Route::prefix('profile')->middleware(['role:admin'])->group(function () {
        Route::get('/viewall', [ProfileController::class, 'viewall']);
    });


    Route::prefix('profile')->middleware(['role:player|admin'])->group(function () {
        Route::delete('/delete/{id}', [ProfileController::class, 'delete']);
        Route::get('/view/{id}', [ProfileController::class, 'view']);
    });

});





Route::get('/test/verify-link/{userId}', function ($userId) {
    $user = User::findOrFail($userId);

    $signedUrl = URL::temporarySignedRoute(
        'verification.verify',
        Carbon::now()->addMinutes(30),
        ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
    );

    return response()->json([
        'signed_url' => $signedUrl,
    ]);
});

Route::get('/players', [AuthController::class, 'getAllPlayers'])->middleware('auth:api');
