<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Google_Client;
use Illuminate\Support\Facades\Validator;
use App\Notifications\CustomResetPassword;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|unique:users',
                'password' => [
                    'required',
                    'min:8',
                    'confirmed',
                    'regex:/[A-Z]/'
                ],
                'role' => 'required|in:admin,player,stadium_owner'
            ]);

            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $user->assignRole($request->role);
            $user->sendEmailVerificationNotification();

            return response()->json([
                'status' => true,
                'status_code' => 201,
                'message' => 'User registered. Please verify your email.',
                'data' => [
                    'role' => $request->role
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'status_code' => 422,
                'message' => 'Validation error.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'status_code' => 500,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'status_code' => 401,
                'message' => 'Invalid credentials'
            ], 401);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'status_code' => 401,
                'message' => 'Incorrect password'
            ], 401);
        }

        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'status' => false,
                'status_code' => 403,
                'message' => 'Email not verified'
            ], 403);
        }

        $token = $user->createToken('access-token')->accessToken;
        $profile = $user->profile;
        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'user' => $user,
                'profile_id' => $profile ? $profile->id : 0

            ]
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Logged out'
        ], 200);
    }

    public function me(Request $request)
    {
        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'User info retrieved successfully',
            'data' => [
                'user' => $request->user()
            ]
        ], 200);
    }

    public function resendVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'status_code' => 422,
                'message' => 'Validation error',
                'data' => [
                    'errors' => $validator->errors()
                ]
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'status' => false,
                'status_code' => 400,
                'message' => 'Email is already verified'
            ], 400);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Verification link sent'
        ], 200);
    }

    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string)$hash, sha1($user->getEmailForVerification()))) {
            return response()->json([
                'status' => false,
                'status_code' => 403,
                'message' => 'Invalid verification link'
            ], 403);
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        $token = $user->createToken('access-token')->accessToken;
        $redirectUrl = "myapp://email-verified?token={$token}&email={$user->email}";

        return redirect()->away($redirectUrl);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => 'Email not found'
            ], 404);
        }

        $token = Password::createToken($user);

        $user->notify(new CustomResetPassword($token, $user->email));

        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Password reset link sent successfully'
        ], 200);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return response()->json([
            'status' => $status === Password::PASSWORD_RESET,
            'status_code' => 200,
            'message' => __($status)
        ], 200);
    }

    public function loginWithGoogleMobile(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string',
        ]);

        try {
            $client = new \Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
            $payload = $client->verifyIdToken($request->id_token);

            if (!$payload) {
                return response()->json([
                    'status' => false,
                    'status_code' => 401,
                    'message' => 'Invalid Google ID token'
                ], 401);
            }

            $email = $payload['email'];

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'email_verified_at' => now(),
                    'password' => Hash::make(Str::random(24)),
                ]
            );

            if (!$user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
                event(new Verified($user));
            }

            if (!$user->hasAnyRole(['admin', 'player', 'stadium_owner'])) {
                $user->assignRole('player');
            }

            $token = $user->createToken('google-mobile-token')->accessToken;

            $profile = $user->profile;
            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => 'Login successful',
                'data' => [
                    'token' => $token,
                    'user' => $user,
                    'profile_id' => $profile ? $profile->id : 0,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'status_code' => 500,
                'message' => 'Authentication failed',
                'data' => [
                    'error' => $e->getMessage()
                ]
            ], 500);
        }
    }
}
