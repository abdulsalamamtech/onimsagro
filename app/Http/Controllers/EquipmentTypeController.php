<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\StoreEquipmentTypeRequest;
use App\Http\Requests\UpdateEquipmentTypeRequest;
use App\Http\Resources\EquipmentTypeResource;
use App\Models\EquipmentType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EquipmentTypeController extends Controller
{
    /**
     * [public] Display a listing of the resource.
     */
    public function index()
    {
        // get all installation types
        $equipmentTypes = EquipmentType::latest()->paginate(10);

        // if not exists
        if ($equipmentTypes->isEmpty()) {
            return ApiResponse::error([], 'No equipment types found', 404);
        }
        // transform data
        $response = EquipmentTypeResource::collection($equipmentTypes);

        // return response
        return ApiResponse::success($response, 'successful', 200, $equipmentTypes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEquipmentTypeRequest $request)
    {
        $data = $request->validated();
        try {
            // start transaction
            DB::beginTransaction();

            // create data
            $data['created_by'] = auth()?->id();

            // create equipment type
            $equipmentType = EquipmentType::create($data);

            // transform data
            $response = new EquipmentTypeResource($equipmentType);

            // commit transaction
            DB::commit();

            // log activity
            info('Equipment type created', [$equipmentType]);

            return ApiResponse::success($response, 'Equipment type created successfully', 201, $equipmentType);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating equipment type: ' . $e->getMessage());
            return ApiResponse::error([], 'Failed to create equipment type', 500);
        }        
    }

    /**
     * Display the specified resource.
     */
    public function show(EquipmentType $equipmentType)
    {
        // load relationship
        $equipmentType->load('createdBy');

        // transform data
        $response = new EquipmentTypeResource($equipmentType);

        // return response
        return ApiResponse::success($response, 'successful', 200, $equipmentType);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEquipmentTypeRequest $request, EquipmentType $equipmentType)
    {
        $data = $request->validated();

        try {
            // start transaction
            DB::beginTransaction();

            // update data
            $equipmentType->update($data);

            // transform data
            $response = new EquipmentTypeResource($equipmentType);

            // commit transaction
            DB::commit();

            // log activity
            info('Equipment type updated', [$equipmentType]);

            return ApiResponse::success($response, 'Equipment type updated successfully', 200, $equipmentType);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating equipment type: ' . $e->getMessage());
            return ApiResponse::error([], 'Failed to update equipment type', 500);
        }        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EquipmentType $equipmentType)
    {
        try {
            // start transaction
            DB::beginTransaction();

            // delete equipment type
            $equipmentType->delete();

            // commit transaction
            DB::commit();

            // log activity
            info('Equipment type deleted', [$equipmentType]);

            return ApiResponse::success([], 'Equipment type deleted successfully', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting equipment type: ' . $e->getMessage());
            return ApiResponse::error([], 'Failed to delete equipment type', 500);
        }        
    }
}
