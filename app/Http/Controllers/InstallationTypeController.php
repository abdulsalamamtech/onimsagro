<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\StoreInstallationTypeRequest;
use App\Http\Requests\UpdateInstallationTypeRequest;
use App\Http\Resources\InstallationTypeResource;
use App\Models\InstallationType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InstallationTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // get all installation types
        $installationTypes = InstallationType::latest()->paginate(10);

        // if not exists
        if ($installationTypes->isEmpty()) {
            return ApiResponse::error([], 'No installation types found', 404);
        }
        // transform data
        $response = InstallationTypeResource::collection($installationTypes);

        // return response
        return ApiResponse::success($response, 'successful', 200, $installationTypes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInstallationTypeRequest $request)
    {
        $data = $request->validated();

        try {
            // start transaction
            DB::beginTransaction();

            // create data
            $data['created_by'] = auth()?->id();

            // create installation type
            $installationType = InstallationType::create($data);

            // transform data
            $response = new InstallationTypeResource($installationType);

            // commit transaction
            DB::commit();

            // log activity
            info('Installation type created', [$installationType]);

            return ApiResponse::success($response, 'Installation type created successfully', 201, $installationType);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating installation type: ' . $e->getMessage());
            return ApiResponse::error([], 'Failed to create installation type', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(InstallationType $installationType)
    {
        // load relationship
        $installationType->load('createdBy');

        // transform data
        $response = new InstallationTypeResource($installationType);

        // return response
        return ApiResponse::success($response, 'successful', 200, $installationType);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInstallationTypeRequest $request, InstallationType $installationType)
    {
        $data = $request->validated();

        try {
            // start transaction
            DB::beginTransaction();

            // update data
            $installationType->update($data);

            // transform data
            $response = new InstallationTypeResource($installationType);

            // commit transaction
            DB::commit();

            // log activity
            info('Installation type updated', [$installationType]);

            return ApiResponse::success($response, 'Installation type updated successfully', 200, $installationType);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating installation type: ' . $e->getMessage());
            return ApiResponse::error([], 'Failed to update installation type', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InstallationType $installationType)
    {
        try {
            // start transaction
            DB::beginTransaction();

            // delete installation type
            $installationType->delete();

            // commit transaction
            DB::commit();

            // log activity
            info('Installation type deleted', [$installationType]);

            return ApiResponse::success([], 'Installation type deleted successfully', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting installation type: ' . $e->getMessage());
            return ApiResponse::error([], 'Failed to delete installation type', 500);
        }
    }
}
