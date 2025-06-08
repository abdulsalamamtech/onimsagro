<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Asset;
use App\Models\User;
use App\Models\UserProfile;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    /**
     * Display the authenticated user's profile.
     */
    public function index()
    {
        // return response()->json(auth()->user()->userProfile);
        $user = auth()->user();
        if (!$user->userProfile) {
            return ApiResponse::error([], 'User profile not found', 404);
        }
        $response = $user->load(['userProfile.profileImage']);
        return ApiResponse::success($response, 'User profile retrieved successfully');
    }

    /**
     * Update a specified user profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function updateUserProfile(Request $request, User $user)
    {
        // validate data
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            // 'phone' => 'nullable|string|max:15',
        ]);
        $data['user_id'] = $user->id;
        $userProfile = UserProfile::updateOrCreate([
            'user_id' => $user->id
        ], $data);

        // check if profile image is provided
        // upload banner
        if ($request->hasFile('image')) {
            // upload file to cloudinary
            $cloudinaryImage = Cloudinary::uploadApi()->upload($request->file('image')->getRealPath());
            // save product banner to assets
            $asset = Asset::create([
                'name' =>  $request->file('image'),
                'description' => 'profile image upload',
                'url' => $cloudinaryImage['url'],
                'file_id' => $cloudinaryImage['public_id'],
                'type' => $cloudinaryImage['resource_type'],
                'size' => $cloudinaryImage['bytes'],
            ]);

            // update the assets
            $userProfile->update([
                'asset_id' => $asset->id
            ]);
        }

        $response = $user->load(['userProfile.profileImage']);

        return ApiResponse::success($response, 'User profile updated successfully');
    }


    /**
     * Update the authenticated user's profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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
        // check if profile image is provided
        // upload banner
        if ($request->hasFile('image')) {
            // upload file to cloudinary
            $cloudinaryImage = Cloudinary::uploadApi()->upload($request->file('image')->getRealPath());
            // save product banner to assets
            $asset = Asset::create([
                'name' =>  $request->file('image'),
                'description' => 'profile image upload',
                'url' => $cloudinaryImage['url'],
                'file_id' => $cloudinaryImage['public_id'],
                'type' => $cloudinaryImage['resource_type'],
                'size' => $cloudinaryImage['bytes'],
            ]);

            // update the assets
            $user->userProfile()->update([
                'asset_id' => $asset->id
            ]);
        }

        $response = $user->load(['userProfile.profileImage']);

        return ApiResponse::success($response, 'User profile updated successfully');
    }
}
