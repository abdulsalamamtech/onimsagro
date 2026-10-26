<?php

namespace App\Http\Controllers;

use App\Helpers\ActorHelper;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreTechnicalSupportRequest;
use App\Http\Requests\UpdateTechnicalSupportRequest;
use App\Http\Resources\TechnicalSupportResource;
use App\Models\Activity;
use App\Models\TechnicalSupport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TechnicalSupportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $technicalSupports = TechnicalSupport::with('cropType')->latest()->paginate(10);
        $response = TechnicalSupportResource::collection($technicalSupports);

        return ApiResponse::success($response, 'successful', 200, $technicalSupports);
    }

    /**
     * [public] Store a newly created resource in storage.
     */
    public function store(StoreTechnicalSupportRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $technicalSupport = TechnicalSupport::create($data);
            $technicalSupport->refresh();
            $response = new TechnicalSupportResource($technicalSupport);

            info('technical support request created', [$technicalSupport]);
            Activity::create([
                'user_id' => ActorHelper::getUserId(),
                'description' => 'created technical support request',
                'logs' => $technicalSupport,
            ]);

            DB::commit();

            return ApiResponse::success($response, 'Technical support request submitted successfully', 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to create technical support request', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);

            return ApiResponse::error([], 'Technical support request failed', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(TechnicalSupport $technicalSupport)
    {
        $technicalSupport->load('cropType');
        $response = new TechnicalSupportResource($technicalSupport);

        return ApiResponse::success($response, 'Technical support request retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTechnicalSupportRequest $request, TechnicalSupport $technicalSupport)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $technicalSupport->update($data);
            $response = new TechnicalSupportResource($technicalSupport);

            info('technical support request updated', [$technicalSupport]);
            Activity::create([
                'user_id' => ActorHelper::getUserId(),
                'description' => 'updated technical support request',
                'logs' => $technicalSupport,
            ]);

            DB::commit();

            return ApiResponse::success($response, 'Technical support request updated successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to update technical support request', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);

            return ApiResponse::error([], 'Technical support request update failed', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TechnicalSupport $technicalSupport)
    {
        $technicalSupport->delete();

        return ApiResponse::success([], 'Technical support request deleted successfully');
    }
}
