<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Admin login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
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

        // Check if user is admin
        if ($user->role !== 'admin') {
            return response()->json([
                'api_version' => 'v1.0.0',
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED_ROLE',
                    'message' => 'Admin access required'
                ]
            ], 403);
        }

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

        $token = $user->createToken('admin-token')->plainTextToken;

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
     * Get authenticated admin user
     */
    public function me(Request $request)
    {
        $user = $request->user();

        // Ensure user is admin
        if ($user->role !== 'admin') {
            return response()->json([
                'api_version' => 'v1.0.0',
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED_ROLE',
                    'message' => 'Admin access required'
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
     * Admin logout
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        // Ensure user is admin
        if ($user->role !== 'admin') {
            return response()->json([
                'api_version' => 'v1.0.0',
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED_ROLE',
                    'message' => 'Admin access required'
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