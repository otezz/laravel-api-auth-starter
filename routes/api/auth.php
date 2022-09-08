<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Auth\VerificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware(['auth:sanctum'])->name('logout');

    Route::get('/verify/{id}/{hash}',
        [VerificationController::class, 'verify'])->middleware(['signed'])->name('verification.verify');
    Route::post('/verify/resend', [VerificationController::class, 'resend'])->middleware([
        'auth:sanctum',
        'throttle:6,1',
    ])->name('verification.send');

    Route::post('/password/email',
        [AuthController::class, 'sendResetPasswordEmail'])->middleware('guest')->name('password.email');
    Route::get('/password/reset/{token}',
        [AuthController::class, 'checkResetToken'])->middleware('guest')->name('password.reset');
    Route::post('/password/reset',
        [AuthController::class, 'updatePassword'])->middleware('guest')->name('password.update');

    // Social auth routes
    Route::post('/social', [SocialAuthController::class, 'handleToken']);
    Route::get('/social/redirect/{provider}', [SocialAuthController::class, 'handleRedirect']);
    Route::get('/social/callback/{provider}', [SocialAuthController::class, 'handleCallback']);
});
