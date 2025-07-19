<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request; 

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/send-test-mail', function () {
    Mail::raw('This is a test from Gmail SMTP', function ($message) {
        $message->to('youremail@gmail.com')->subject('Test Email from Laravel');
    });

    return 'Sent';
});

Route::get('/reset-password-redirect', function (Request $request) {
    $token = $request->query('token');
    $email = $request->query('email');
    $url = "myapp://reset-password?token={$token}&email={$email}";

    return redirect()->away($url);
});


Route::get('/password/reset/{token}', function ($token) {
    return response()->json([
        'message' => 'This is just a placeholder route for password reset.',
        'token' => $token,
    ]);
})->name('password.reset');

