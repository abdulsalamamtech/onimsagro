<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\StoreInstallationServiceRequest;
use App\Http\Requests\UpdateInstallationServiceRequest;
use App\Http\Resources\InstallationServiceResource;
use App\Models\InstallationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InstallationServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // get all installation services
        $installationServices = InstallationService::with('installationType')->latest()->paginate();
        // check if exists
        if ($installationServices->isEmpty()) {
            // return empty response
            return ApiResponse::success([], 'no installation services found', 404);
        }
        // transform data
        $response = InstallationServiceResource::collection($installationServices);
        // return response
        return ApiResponse::success($response, 'successful', 200, $installationServices);
    }

    /**
     * [public] Store a newly created resource in storage.
     */
    public function store(StoreInstallationServiceRequest $request)
    {
        $data = $request->validated();

        try {
            // begin transaction
            DB::beginTransaction();

            // Save to database
            $installationService = InstallationService::create($data);

            // transform data
            $response = new InstallationServiceResource($installationService);

            // log activity
            info('installation service created', [$installationService]);

            // commit transaction
            DB::commit();

            // return response
            return ApiResponse::success($response, 'installation service created successfully', 201);
        } catch (\Exception $e) {
            // rollback transaction
            DB::rollBack();
            Log::error('Error creating installation service: ' . $e->getMessage());
            return ApiResponse::error($e->getMessage(), 'failed to create installation service', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(InstallationService $installationService)
    {
        $response = new InstallationServiceResource($installationService);
        return ApiResponse::success($response, 'successful', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInstallationServiceRequest $request, InstallationService $installationService)
    {
        $data = $request->validated();

        try {
            // begin transaction
            DB::beginTransaction();

            // Update installation service
            $installationService->update($data);

            // transform data
            $response = new InstallationServiceResource($installationService);

            // log activity
            info('installation service updated', [$installationService]);

            // commit transaction
            DB::commit();

            // return response
            return ApiResponse::success($response, 'installation service updated successfully', 200);
        } catch (\Exception $e) {
            // rollback transaction
            DB::rollBack();
            Log::error('Error updating installation service: ' . $e->getMessage());
            return ApiResponse::error($e->getMessage(), 'failed to update installation service', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InstallationService $installationService)
    {
        try {
            // begin transaction
            DB::beginTransaction();

            // Delete installation service
            $installationService->delete();

            // log activity
            info('installation service deleted', [$installationService]);

            // commit transaction
            DB::commit();

            // return response
            return ApiResponse::success([], 'installation service deleted successfully', 204);
        } catch (\Exception $e) {
            // rollback transaction
            DB::rollBack();
            Log::error('Error deleting installation service: ' . $e->getMessage());
            return ApiResponse::error($e->getMessage(), 'failed to delete installation service', 500);
        }
    }
}
