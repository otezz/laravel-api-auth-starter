<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    /**
     * Verify user's email
     *
     * @param  EmailVerificationRequest  $request
     * @return JsonResponse
     */
    public function verify(EmailVerificationRequest $request): JsonResponse
    {
        $user = User::findOrFail($request->route('id'));

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();

            event(new Verified($user));
        }

        return response()->json(['message' => 'Email verified']);
    }

    /**
     * Resend email verification
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function resend(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Your email has already verified'], 400);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification link sent!']);
    }
}
