<?php

namespace App\Http\Controllers;

use App\Helpers\ActorHelper;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreFarmerRequest;
use App\Http\Requests\UpdateFarmerRequest;
use App\Http\Resources\FarmerResource;
use App\Models\Activity;
use App\Models\Farmer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FarmerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $farmers = Farmer::paginate();

        // check if there are no farmers
        if ($farmers->isEmpty()) {
            return ApiResponse::error([], 'No farmers found', 404);
        }

        // transform data
        $response = FarmerResource::collection($farmers);

        // return response
        return ApiResponse::success($response, 'successful', 200, $farmers);

    }

    /**
     * [public] Store a newly created resource in storage.
     */
    public function store(StoreFarmerRequest $request)
    {
        $data = $request->validated();

        // create data
        // $data['created_by'] = auth()->id();
        $data['created_by'] = ActorHelper::getUserId();

        try {
            //code...
            DB::beginTransaction();
            // create farmer
            $farmer = Farmer::create($data);
    
            // transform data
            $response = new FarmerResource($farmer);

            // log activity
            info('farmer created', [$farmer]);
            Activity::create([
                'user_id' => ActorHelper::getUserId(),
                'description' => 'created farmer',
                'logs' => $farmer
            ]);            
    
            // Commit 
            DB::commit();
            // return response
            return ApiResponse::success($response, 'Farmer created successfully', 201, $farmer);

        } catch (\Throwable $th) {
            //throw $th;
            // rollback transaction
            DB::rollBack();
            // log error
            Log::error('failed to create farmer', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);
            // return error response
            return ApiResponse::error([], 'failed to create farmer '. $th->getMessage(), 500);             
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Farmer $farmer)
    {
        // load the createdBy relationship
        $farmer->load('createdBy');
        $response = new FarmerResource($farmer);

        return ApiResponse::success($response, 'Farmer retrieved successfully', 200, $farmer);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFarmerRequest $request, Farmer $farmer)
    {
        $data = $request->validated();

        try {
            //code...
            DB::beginTransaction();
            // update farmer
            $farmer->update($data);
    
            // transform data
            $response = new FarmerResource($farmer);
            
            // log activity
            info('farmer updated', [$farmer]);
            Activity::create([
                'user_id' => ActorHelper::getUserId(),
                'description' => 'updated farmer',
                'logs' => $farmer
            ]);

            // commit transaction
            DB::commit();
    
            // return response
            return ApiResponse::success($response, 'Farmer updated successfully', 200, $farmer);
        } catch (\Throwable $th) {
            //throw $th;
            // rollback transaction
            DB::rollBack();
            // log error
            Log::error('failed to update farmer', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);
            // return error response
            return ApiResponse::error([], 'failed to update farmer '. $th->getMessage(), 500); 
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Farmer $farmer)
    {
        // delete farmer
        $farmer->delete();

        // return response
        return ApiResponse::success([], 'Farmer deleted successfully', 200);
    }
}
