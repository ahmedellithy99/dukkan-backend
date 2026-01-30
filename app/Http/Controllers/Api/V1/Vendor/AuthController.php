<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Register a new vendor user
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'api_version' => 'v1.0.0',
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Validation failed',
                    'fields' => $validator->errors()
                ]
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'vendor',
            'status' => 'active',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'api_version' => 'v1.0.0',
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'status' => $user->status,
                ],
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ], 201);
    }

    /**
     * Login user and create token
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'api_version' => 'v1.0.0',
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Validation failed',
                    'fields' => $validator->errors()
                ]
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'api_version' => 'v1.0.0',
                'success' => false,
                'error' => [
                    'code' => 'AUTHENTICATION_ERROR',
                    'message' => 'Invalid credentials'
                ]
            ], 401);
        }

        // Check if user is active first (before role check)
        if ($user->status !== 'active') {
            return response()->json([
                'api_version' => 'v1.0.0',
                'success' => false,
                'error' => [
                    'code' => 'ACCOUNT_SUSPENDED',
                    'message' => 'Account is suspended'
                ]
            ], 403);
        }

        // Check if user is vendor
        if ($user->role !== 'vendor') {
            return response()->json([
                'api_version' => 'v1.0.0',
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED_ROLE',
                    'message' => 'Vendor access required'
                ]
            ], 403);
        }

        $token = $user->createToken('vendor-token')->plainTextToken;

        return response()->json([
            'api_version' => 'v1.0.0',
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'status' => $user->status,
                ],
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ]);
    }

    /**
     * Get current authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        // Ensure user is vendor
        if ($user->role !== 'vendor') {
            return response()->json([
                'api_version' => 'v1.0.0',
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED_ROLE',
                    'message' => 'Vendor access required'
                ]
            ], 403);
        }

        return response()->json([
            'api_version' => 'v1.0.0',
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'status' => $user->status,
                ]
            ]
        ]);
    }

    /**
     * Logout user (revoke token)
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        // Ensure user is vendor
        if ($user->role !== 'vendor') {
            return response()->json([
                'api_version' => 'v1.0.0',
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED_ROLE',
                    'message' => 'Vendor access required'
                ]
            ], 403);
        }

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'api_version' => 'v1.0.0',
            'success' => true,
            'data' => [
                'message' => 'Successfully logged out'
            ]
        ]);
    }
}