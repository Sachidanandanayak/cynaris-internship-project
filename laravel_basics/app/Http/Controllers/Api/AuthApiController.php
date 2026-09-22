<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthApiController extends Controller
{
    /**
     * Authenticate a user and issue a Laravel Sanctum personal access token.
     *
     * POST /api/login
     * Status: 200 OK (or 401 Unauthorized / 422 Unprocessable Entity)
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email'       => ['required', 'string', 'email'],
            'password'    => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'The provided credentials do not match our records.',
                'errors'  => [
                    'email' => ['The provided credentials do not match our records.'],
                ],
            ], 401);
        }

        $deviceName = $validated['device_name'] ?? 'api-client';
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'message'    => 'Authentication successful.',
            'token'      => $token,
            'token_type' => 'Bearer',
            'user'       => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
        ], 200);
    }

    /**
     * Revoke the current access token for the authenticated user.
     *
     * POST /api/logout
     * Status: 200 OK (or 401 Unauthorized)
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Token revoked successfully.',
        ], 200);
    }

    /**
     * Get the authenticated user's profile.
     *
     * GET /api/user
     * Status: 200 OK (or 401 Unauthorized)
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? null,
            ],
        ], 200);
    }
}
