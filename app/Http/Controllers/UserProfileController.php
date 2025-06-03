<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    public function index()
    {
        return response()->json(auth()->user()->userProfile);
    }

    public function update(Request $request, User $user)
    {
        // validate data
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            // 'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
        ]);
        $data['user_id'] = $user->id;
        $userProfile = UserProfile::updateOrCreate([
            'user_id' => $user->id
        ], $data);

        $response = $user->load(['userProfile']);

        return ApiResponse::success($response, 'User profile updated successfully');
    }


    public function updateProfile(Request $request)
    {
        // validate data
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            // 'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
        ]);
        $user = auth()->user();
        // find or create
        if ($user->userProfile) {
            // update user profile
            $user->userProfile()->update($data);
        } else {
            $data['user_id'] = $user->id;
            $user->userProfile()->create($data);
        }

        $response = $user->load(['userProfile']);

        return ApiResponse::success($response, 'User profile updated successfully');
    }
}
