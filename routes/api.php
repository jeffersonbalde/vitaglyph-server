<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\VitaGlyphAuthController;
use App\Http\Controllers\Api\FlaskEmotion\EmotionDetectionController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Debug route
Route::get('/users', function () {
    return User::all();
});

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [LoginController::class, 'login']);

    Route::post('/register', [VitaGlyphAuthController::class, 'register']);

    Route::get('/email/verify/{id}/{hash}/{token}', [VitaGlyphAuthController::class, 'verify'])
    ->name('verification.verify');
    Route::post('/email/resend', [VitaGlyphAuthController::class, 'resendVerification'])
        ->name('verification.resend');
});

// Authenticated routes (sanctum protected)
Route::middleware(['auth:sanctum'])->group(function () {
    // User routes
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        $role = match(get_class($user)) {
            \App\Models\AdminUser::class => 'admin',
            \App\Models\VitaGlyphUser::class => 'vitaglyph_user',
            default => 'unknown'
        };

        return response()->json([
            'user' => $user,
            'role' => $role
        ]);
    });

    Route::prefix('auth')->group(function () {
        Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth:sanctum');
    });
});


Route::post('/detect-emotion', [EmotionDetectionController::class, 'predictEmotion']);