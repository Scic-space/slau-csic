<?php

// ============================================
// API Auth Controller
// app/Http/Controllers/Api/AuthController.php
// ============================================

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $accountKey = $this->loginAccountRateLimitKey($request);

        if (RateLimiter::tooManyAttempts($accountKey, 5)) {
            return response()->json([
                'message' => 'Too many login attempts. Please try again later.',
            ], 429);
        }

        if (! Auth::attempt($credentials)) {
            RateLimiter::hit($accountKey, 60);

            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        RateLimiter::clear($accountKey);

        $user = Auth::user();

        // Check if user is an active member
        if ($user->membership_status !== 'active') {
            Auth::logout();

            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'program' => $user->program,
                'year_of_study' => $user->year_of_study,
                'membership_type' => $user->membership_type,
                'membership_status' => $user->membership_status,
                'bio' => $user->bio,
                'joined_at' => $user->joined_at,
            ],
        ]);
    }

    public function refreshToken(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user || ! $user->currentAccessToken()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $user->currentAccessToken()->delete();

        $newToken = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'token' => $newToken,
            'expires_in' => config('sanctum.expiration') * 60,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        if (! $request->user() || ! $request->user()->currentAccessToken()) {
            return response()->json([
                'message' => 'Already logged out',
            ]);
        }

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'program' => $user->program,
            'year_of_study' => $user->year_of_study,
            'membership_type' => $user->membership_type,
            'membership_status' => $user->membership_status,
            'bio' => $user->bio,
            'joined_at' => $user->joined_at,
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'bio' => 'sometimes|string|max:500',
            'github_username' => 'sometimes|string|max:255',
            'linkedin_url' => 'sometimes|url|max:255',
            'discord_username' => 'sometimes|string|max:255',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user,
        ]);
    }

    protected function loginAccountRateLimitKey(Request $request): string
    {
        $email = Str::lower(Str::transliterate((string) $request->input('email')));

        return 'api-login:account:'.hash('sha256', $email);
    }
}
