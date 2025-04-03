<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
        /**
     * Admin: Display a listing of the resource.
     */
    public function index(){

        // Fetch all activities from the database
        $activities = Activity::latest()->paginate();

        // Add metadata to the response
        $metadata = $activities;

        // Check if there are any activities
        if($activities->isEmpty()){
            return ApiResponse::error([], 'no activities found', 404);
        }

        // Transform the items
        $data = ActivityResource::collection($activities);

        // Return response
        return ApiResponse::success($data, 'successful', 200, $metadata);
    }



    /**
     * Admin: Display the specified resource.
     */
    public function show(Activity $activity)
    {
        // Fetch the activity and load related user details
        $activity = $activity->load(['user']);

        // Transform the data
        $data = new ActivityResource($activity);

        // Return response
        return ApiResponse::success($data, 'successful');
    }


    /**
     * Admin: Remove the specified resource from storage.
     */
    public function destroy(Activity $activity)
    {
        // Delete the activity
        $activity->delete();

        // Return response
        return ApiResponse::success([], 'activity deleted successfully', 200);
    }
}
