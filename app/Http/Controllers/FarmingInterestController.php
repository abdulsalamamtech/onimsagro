<?php

namespace App\Http\Controllers;

use App\Helpers\ActorHelper;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreFarmingInterestRequest;
use App\Http\Requests\UpdateFarmingInterestRequest;
use App\Http\Resources\FarmingInterestResource;
use App\Models\Activity;
use App\Models\FarmingInterest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FarmingInterestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // get all product reviews
        $farming_interests = FarmingInterest::with('createdBy')->latest()->paginate();
        // transform data
        $response = FarmingInterestResource::collection($farming_interests);
        // return response
        return ApiResponse::success($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFarmingInterestRequest $request)
    {
        $data = $request->validated();

        try {
            //code...
            // begin transaction
            DB::beginTransaction();

            // set created_by
            $data['created_by'] = ActorHelper::getUserId();

            // Save to database
            $farming_interest = FarmingInterest::create($data);

            // transform data
            $response = new FarmingInterestResource($farming_interest);

            // log activity
            info('farming interest created', [$farming_interest]);
            Activity::create([
                'user_id' => ActorHelper::getUserId(),
                'description' => 'created farming interest',
                'logs' => $farming_interest
            ]);

            // commit transaction
            DB::commit();

            // return response
            return ApiResponse::success($response, 'farming interest created successfully', 201, $response);            
        } catch (\Throwable $th) {
            //throw $th;
            // rollback transaction
            DB::rollBack();
            // log error
            Log::error('failed to create farming interest', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);
            // return error response
            return ApiResponse::error([], 'failed to create farming interest '. $th->getMessage(), 500);            
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(FarmingInterest $farmingInterest)
    {
        $response = new FarmingInterestResource($farmingInterest);

        return ApiResponse::success($response);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFarmingInterestRequest $request, FarmingInterest $farmingInterest)
    {
        $data = $request->validated();

        try {
            //code...
            // begin transaction
            DB::beginTransaction();

            // Update to database
            $farmingInterest->update($data);

            // transform data
            $response = new FarmingInterestResource($farmingInterest);

            // log activity
            info('farming interest updated', [$farmingInterest]);
            Activity::create([
                'user_id' => ActorHelper::getUserId(),
                'description' => 'updated farming interest',
                'logs' => $farmingInterest
            ]);

            // commit transaction
            DB::commit();

            // return response
            return ApiResponse::success($response, 'farming interest updated successfully', 201, $response);            
        } catch (\Throwable $th) {
            //throw $th;
            // rollback transaction
            DB::rollBack();
            // log error
            Log::error('failed to update farming interest', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);
            // return error response
            return ApiResponse::error([], 'failed to update farming interest '. $th->getMessage(), 500);            
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FarmingInterest $farmingInterest)
    {
        // delete data
        $farmingInterest->delete();

        // return response
        return ApiResponse::success([], 'farming interest deleted successfully', 204);
    }
}
