<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\VitaGlyphUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Determine which guard to use based on email domain or other logic
        $guard = $this->determineGuard($request->email);

        if ($guard === 'admin') {
            $user = AdminUser::where('email', $request->email)->first();
        } else {
            $user = VitaGlyphUser::where('email', $request->email)->first();
            
            // Check if email is verified for VitaGlyph users
            if ($user && !$user->hasVerifiedEmail()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please verify your email address before logging in.',
                ], 403);
            }
        }

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        // Create token based on guard
        $tokenName = $guard === 'admin' ? 'AdminAuthToken' : 'VitaGlyphAuthToken';
        $token = $user->createToken($tokenName)->plainTextToken;

        return response()->json([
            'success' => true,
            'user' => $user,
            'token' => $token,
            'role' => $guard,
            'message' => 'Login successfully.',
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    protected function determineGuard($email)
    {
        // Simple logic - you can customize this based on your needs
        // For example, check if email ends with @admin.com or exists in admin table
        return AdminUser::where('email', $email)->exists() ? 'admin' : 'student';
    }
}