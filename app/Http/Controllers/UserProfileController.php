<?php

namespace App\Http\Controllers;

use App\Models\User;
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
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
        ]);
        // $userProfile = auth()->user()->userProfile;
        $userProfile = $user->userProfile;
        $userProfile->update($data);
        return response()->json($userProfile);
    }


    public function updateProfile(Request $request)
    {
        // validate data
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
        ]);
        $userProfile = auth()->user()->userProfile;
        $userProfile->update($data);
        return response()->json($userProfile);
    }
}
