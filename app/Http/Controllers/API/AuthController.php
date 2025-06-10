<?php

namespace App\Http\Controllers\API;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // User Registration API
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            Log::error("Register validation failed");
            return response()->json(['error' => $validator->errors()], 401);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        $token = $user->createToken('MyAppToken')->plainTextToken;
        Activity::create([
            'user_id' => $user->id,
            'description' => 'created user account',
            'logs' => $user
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully.',
            'token' => $token,
            'user' => $user,
        ]);
    }

    // User Login API
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string']
        ]);

        // incorrect email or password
        if (!Auth::attempt($request->only('email', 'password'))) {
            Log::error("Login validation failed");
            return response()->json([
                'success' => true,
                'message' => 'Unauthorized, incorrect email or password',
            ], 401);
        }

        $user = Auth::user();
        // load user with profile
        $user->load(['userProfile.profileImage']);
        $token = $user->createToken('auth_token')->plainTextToken;
        Activity::create([
            'user_id' => $user->id,
            'description' => 'login user account',
            'logs' => $user
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'token' => $token,
            'user' => $user,
        ]);
    }

    // User Profile API (Protected)
    public function profile(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            Log::error("User not found");
            return response()->json(['error' => 'User not found'], 404);
        }
        $user->load(['userProfile.profileImage']);
        Activity::create([
            'user_id' => $user->id,
            'description' => 'view user profile',
            'logs' => $user
        ]);
        Log::info("User profile retrieved successfully", ['user_id' => $user->id]);
        return response()->json([
            'success' => true,
            'message' => 'successful',
            'data' => $user,
        ]);
    }

    // User Logout API
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        Activity::create([
            'user_id' => $request->user()->id,
            'description' => 'logout user account',
            'logs' => $request->user()
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Logout successful.',
        ]);
    }

    /**
     * Destroy the user's token.
     */
    public function logoutDevices(Request $request)
    {
        // $user->tokens()->delete();
        $request->user()->tokens()->delete();
        $message = 'You have successfully logged out from all devices.';
        Activity::create([
            'user_id' => $request->user()->id,
            'description' => 'logout user from all devices',
            'logs' => $request->user()
        ]);
        Log::info("User logged out from all devices", ['user_id' => $request->user()->id]);
        // Return success response
        return ApiResponse::success([], $message);

    }
}
