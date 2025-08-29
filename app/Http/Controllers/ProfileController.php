<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ProfileRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\Profile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function create(ProfileRequest $request)
    {
        $userId = auth()->id();
        $existingProfile = Profile::where('user_id', $userId)->first();

        if ($existingProfile) {
            return response()->json([
                'status' => false,
                'status_code' => 409,
                'message' => 'Profile already exists',
                'data' => [
                    'profile' => $existingProfile
                ]
            ], 409);
        }

        $validated = $request->validated();
        $validated['user_id'] = $userId;

        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $avatarPath;
        }

        $profile = Profile::create($validated);

        return response()->json([
            'status' => true,
            'status_code' => 201,
            'message' => 'Profile created successfully',
            'data' => [
                'profile' => $profile
            ]
        ], 201);
    }

    public function update(UpdateProfileRequest $request, $id)
    {
        $profile = Profile::find($id);

        if (!$profile) {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => 'Profile not found',
            ], 404);
        }

        if (auth()->id() !== $profile->user_id) {
            return response()->json([
                'status' => false,
                'status_code' => 401,
                'message' => 'Unauthorized to update this profile',
            ], 401);
        }


        $validated=$request->validated();

        if ($request->hasFile('avatar')) {
            if ($profile->avatar && Storage::disk('public')->exists($profile->avatar)) {
                Storage::disk('public')->delete($profile->avatar);
            }

            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $avatarPath;
        }

        $profile->update($validated);

        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Profile updated successfully',
            'data' => [
                'profile' => $profile
            ]
        ], 200);
    }

    public function delete($id)
    {
        $profile = Profile::find($id);

        if (!$profile) {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => 'Profile not found',
            ], 404);
        }

        if (auth()->id() !== $profile->user_id && !auth()->user()->hasRole('admin')) {
            return response()->json([
                'status' => false,
                'status_code' => 401,
                'message' => 'Unauthorized to delete this profile',
            ], 401);
        }

        if ($profile->avatar && Storage::disk('public')->exists($profile->avatar)) {
            Storage::disk('public')->delete($profile->avatar);
        }

        $profile->delete();

        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Profile deleted successfully',
        ], 200);
    }

    public function view($id)
    {
        $profile = Profile::find($id);

        if (!$profile) {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => 'Profile not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Profile retrieved successfully',
            'data' => [
                'profile' => $profile
            ]
        ], 200);
    }

    public function viewall()
    {
        $profiles = Profile::all();

        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Profiles retrieved successfully',
            'data' => [
                'profiles' => $profiles
            ]
        ], 200);
    }
}
