<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\SendResetPasswordEmailRequest;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Create a new user
     *
     * @param  RegisterRequest  $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        event(new Registered($user));

        return response()->json(['message' => 'User registered'], 201);
    }

    /**
     * Authenticate the user
     *
     * @param  LoginRequest  $request
     * @return JsonResponse
     *
     * @throws ValidationException
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->is_suspended) {
            return response()->json(['message' => 'Your account is suspended, please contact Admin.'], 401);
        }

        return response()->json(['token' => $user->createToken('api')->plainTextToken]);
    }

    /**
     * Revoke current user access token
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'User access token revoked successfully.']);
    }

    /**
     * Send reset password email
     *
     * @param  SendResetPasswordEmailRequest  $request
     * @return JsonResponse
     */
    public function sendResetPasswordEmail(SendResetPasswordEmailRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $response = Password::sendResetLink($validated);

        if ($response == Password::RESET_THROTTLED) {
            return response()->json(['message' => trans($response)], 400);
        }

        return response()->json(['message' => 'If the email you specified exists in our system, we\'ve sent a password reset link to it.']);
    }

    /**
     * Check user email & token combination.
     *
     * @param  Request  $request
     * @param  string  $token
     * @return JsonResponse
     */
    public function checkResetToken(Request $request, string $token): JsonResponse
    {
        $user = User::where('email', $request->input('email'))->first();

        if (! $user) {
            return response()->json(['message' => 'Invalid token.'], 422);
        }

        if (! Password::tokenExists($user, $token)) {
            return response()->json(['message' => 'Invalid token'], 422);
        }

        return response()->json(['message' => 'ok']);
    }

    /**
     * Reset the user password
     *
     * @param  UpdatePasswordRequest  $request
     * @return JsonResponse
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $status = Password::reset(
            $validated,
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status == Password::PASSWORD_RESET
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 400);
    }
}
