<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    public function index(){
        return response()->json(auth()->user()->userProfile);
    }

    public function update(Request $request){
        $userProfile = auth()->user()->userProfile;
        $userProfile->update($request->all());
        return response()->json($userProfile);
    }

    
}
