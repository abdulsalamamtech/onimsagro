<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
                return response()->json(['error' => $validator->errors()], 401);
            }   
    
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
    
            $token = $user->createToken('MyAppToken')->plainTextToken;
    
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
            if (!Auth::attempt($request->only('email', 'password'))) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
    
            $user = Auth::user();
            $token = $user->createToken('MyAppToken')->plainTextToken;
    
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
            return response()->json([
                'success' => true,
                'user' => $request->user(),
            ]);
        }
    
        // User Logout API
        public function logout(Request $request)
        {
            $request->user()->tokens()->delete();
    
            return response()->json([
                'success' => true,
                'message' => 'Logout successful.',
            ]);
        }
}
