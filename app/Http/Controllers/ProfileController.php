<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdateProfilePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\ProfileResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Show user profile detail.
     *
     * @return ProfileResource
     */
    public function index(): ProfileResource
    {
        return new ProfileResource(Auth::user());
    }

    /**
     * Update current user detail.
     *
     * @param  UpdateProfileRequest  $request
     * @return UserResource
     */
    public function update(UpdateProfileRequest $request): UserResource
    {
        $validated = $request->validated();

        Auth::user()->update($validated);

        return new UserResource(Auth::user());
    }

    /**
     * Update password for current user.
     *
     * @param  UpdateProfilePasswordRequest  $request
     * @return JsonResponse
     */
    public function password(UpdateProfilePasswordRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json(['message' => 'Password updated']);
    }
}
