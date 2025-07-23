<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\VitaGlyphUser;
use App\Http\Controllers\Controller;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;

class VitaGlyphAuthController extends Controller
{
    /**
     * Handle user registration
     */
    public function register(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|string|email|max:255|unique:tbl_VitaGlyphUser',
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
            'age' => 'required|integer|min:13|max:100',
            'gender' => 'required|in:male,female,other,prefer_not_to_say',
            'location' => 'required|string|min:2|max:100',
            'language_preference' => 'required|in:en,tl,ceb',
            'enable_facial_analysis' => 'required|boolean',
            'enable_physiological_analysis' => 'required|boolean',
            'store_emotional_data' => 'required|boolean',
            'store_physiological_data' => 'required|boolean',
            'data_sharing_consent' => 'required|accepted',
        ]);

        // Return validation errors if any
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Create the user
        $user = VitaGlyphUser::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'age' => $request->age,
            'gender' => $request->gender,
            'location' => $request->location,
            'language_preference' => $request->language_preference,
            'enable_facial_analysis' => $request->enable_facial_analysis,
            'enable_physiological_analysis' => $request->enable_physiological_analysis,
            'store_emotional_data' => $request->store_emotional_data,
            'store_physiological_data' => $request->store_physiological_data,
            'data_sharing_consent' => $request->data_sharing_consent,
            'device_id' => $request->device_id ?? Str::slug($request->userAgent()),
            'camera_type' => $request->camera_type ?? 'none',
            'ppg_sensor_type' => $request->ppg_sensor_type ?? 'none',
            'personalization_score' => 0.5,
            'email_verification_token' => Str::random(60),
        ]);

        // Generate verification URL
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
                'token' => $user->email_verification_token,
            ]
        );

        // Send verification email
        $user->notify(new VerifyEmailNotification($verificationUrl));

        // Create API token
        $token = $user->createToken('VitaGlyphAuthToken')->plainTextToken;

        // return response()->json([
        //     'success' => true,
        //     'user' => $user,
        //     'token' => $token,
        //     'message' => 'Registration successful'
        // ], 201);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. Please check your email for verification link.',
            'user' => $user->makeHidden(['password', 'remember_token']),
        ], 201);
    }

    public function verify(Request $request)
    {
        $user = VitaGlyphUser::findOrFail($request->id);

        if (!hash_equals((string) $request->hash, sha1($user->getEmailForVerification()))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification link',
            ], 403);
        }

        if (!hash_equals((string) $request->token, $user->email_verification_token)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification token',
            ], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Email already verified',
            ], 400);
        }

        $user->markEmailAsVerified();
        $user->email_verification_token = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully',
        ]);
    }

    public function resendVerification(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = VitaGlyphUser::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Email already verified',
            ], 400);
        }

        // Generate new verification URL
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
                'token' => $user->email_verification_token,
            ]
        );

        $user->notify(new VerifyEmailNotification($verificationUrl));

        return response()->json([
            'success' => true,
            'message' => 'Verification email resent',
        ]);
    }
}
