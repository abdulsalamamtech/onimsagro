<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\StoreFarmAssistanceRequest;
use App\Http\Requests\UpdateFarmAssistanceRequest;
use App\Http\Resources\FarmAssistanceResource;
use App\Models\FarmAssistance;
use Illuminate\Support\Facades\Log;

class FarmAssistanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $farmAssistance = FarmAssistance::paginate();

        // check if there are no farm assistance requests
        if ($farmAssistance->isEmpty()) {
            return ApiResponse::error([], 'No farm assistance requests found', 404);
        }

        // transform data
        $response = FarmAssistanceResource::collection($farmAssistance);

        // return response
        return ApiResponse::success($response, 'successful', 200, $farmAssistance);
    }

    /**
     * [public] Store a newly created resource in storage.
     */
    public function store(StoreFarmAssistanceRequest $request)
    {
        $data = $request->validated();

        // create data
        // $data['created_by'] = auth()->id();
        $data['created_by'] = $request?->user()?->id;

        try {
            //code...
            $farmAssistance = FarmAssistance::create($data);

            // transform data
            $response = new FarmAssistanceResource($farmAssistance);

            // log activity
            info('Farm assistance request created', [$farmAssistance]);

            return ApiResponse::success($response, 'Farm assistance request created successfully', 201, $farmAssistance);
        } catch (\Exception $e) {
            // log error
            Log::error('Error creating farm assistance request', ['error' => $e->getMessage()]);
            return ApiResponse::error($e->getMessage(), 'Error creating farm assistance request', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(FarmAssistance $farmAssistance)
    {
        // check if farm assistance request exists
        if (!$farmAssistance) {
            return ApiResponse::error([], 'Farm assistance request not found', 404);
        }

        // transform data
        $response = new FarmAssistanceResource($farmAssistance);

        // return response
        return ApiResponse::success($response, 'successful', 200, $farmAssistance);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFarmAssistanceRequest $request, FarmAssistance $farmAssistance)
    {
        // check if farm assistance request exists
        if (!$farmAssistance) {
            return ApiResponse::error([], 'Farm assistance request not found', 404);
        }

        $data = $request->validated();

        // update data
        $farmAssistance->update($data);

        // transform data
        $response = new FarmAssistanceResource($farmAssistance);

        // log activity
        info('Farm assistance request updated', [$farmAssistance]);

        return ApiResponse::success($response, 'Farm assistance request updated successfully', 200, $farmAssistance);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FarmAssistance $farmAssistance)
    {
        // check if farm assistance request exists
        if (!$farmAssistance) {
            return ApiResponse::error([], 'Farm assistance request not found', 404);
        }

        try {
            $farmAssistance->delete();

            // log activity
            info('Farm assistance request deleted', [$farmAssistance]);

            return ApiResponse::success([], 'Farm assistance request deleted successfully', 200);
        } catch (\Exception $e) {
            // log error
            Log::error('Error deleting farm assistance request', ['error' => $e->getMessage()]);
            return ApiResponse::error($e->getMessage(), 'Error deleting farm assistance request', 500);
        }
    }
}
