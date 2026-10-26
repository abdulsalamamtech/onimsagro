<?php

namespace App\Http\Controllers;

use App\Helpers\ActorHelper;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreCropTypeRequest;
use App\Http\Requests\UpdateCropTypeRequest;
use App\Http\Resources\CropTypeResource;
use App\Models\Activity;
use App\Models\CropType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CropTypeController extends Controller
{
    /**
     * [public] List all crop types (used by the technical-support form).
     */
    public function index()
    {
        $cropTypes = CropType::orderBy('name')->get();
        $response = CropTypeResource::collection($cropTypes);

        return ApiResponse::success($response);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCropTypeRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $data['created_by'] = ActorHelper::getUserId();
            $cropType = CropType::create($data);
            $response = new CropTypeResource($cropType);

            info('crop type created', [$cropType]);
            Activity::create([
                'user_id' => ActorHelper::getUserId(),
                'description' => 'created crop type',
                'logs' => $cropType,
            ]);

            DB::commit();

            return ApiResponse::success($response, 'crop type created successfully', 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('failed to create crop type', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);

            return ApiResponse::error([], 'failed to create crop type', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CropType $cropType)
    {
        $response = new CropTypeResource($cropType);

        return ApiResponse::success($response);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCropTypeRequest $request, CropType $cropType)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $cropType->update($data);
            $response = new CropTypeResource($cropType);

            info('crop type updated', [$cropType]);
            Activity::create([
                'user_id' => ActorHelper::getUserId(),
                'description' => 'updated crop type',
                'logs' => $cropType,
            ]);

            DB::commit();

            return ApiResponse::success($response, 'crop type updated successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('failed to update crop type', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);

            return ApiResponse::error([], 'failed to update crop type', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CropType $cropType)
    {
        $cropType->delete();

        return ApiResponse::success([], 'crop type deleted successfully');
    }
}
