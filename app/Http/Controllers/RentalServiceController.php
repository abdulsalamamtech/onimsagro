<?php

namespace App\Http\Controllers;

use App\Helpers\ActorHelper;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreRentalServiceRequest;
use App\Http\Requests\UpdateRentalServiceRequest;
use App\Http\Resources\RentalServiceResource;
use App\Models\Activity;
use App\Models\RentalService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RentalServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // get all rental services
        $rentalServices = RentalService::latest()->paginate(10);

        // if not exists
        if ($rentalServices->isEmpty()) {
            return ApiResponse::error([], 'No rental services found', 404);
        }
        // transform data
        $response = RentalServiceResource::collection($rentalServices);

        // return response
        return ApiResponse::success($response, 'successful', 200, $rentalServices);        
    }

    /**
     * [public] Store a newly created resource in storage.
     */
    public function store(StoreRentalServiceRequest $request)
    {
        $data = $request->validated();

        // create data
        // $data['created_by'] = auth()->id();
        $data['created_by'] = ActorHelper::getUserId();

        try {
            //code...
            DB::beginTransaction();
            // create farmer
            $rentalService = RentalService::create($data);
    
            // transform data
            $response = new RentalServiceResource($rentalService);

            // log activity
            info('rental service created', [$rentalService]);
            Activity::create([
                'user_id' => ActorHelper::getUserId(),
                'description' => 'created rental service',
                'logs' => $rentalService
            ]);            
    
            // Commit 
            DB::commit();
            // return response
            return ApiResponse::success($response, 'Rental service created successfully', 201, $rentalService);

        } catch (\Throwable $th) {
            //throw $th;
            // rollback transaction
            DB::rollBack();
            // log error
            Log::error('failed to create rental service', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);
            // return error response
            return ApiResponse::error([], 'failed to create rental service '. $th->getMessage(), 500);             
        }        
    }

    /**
     * Display the specified resource.
     */
    public function show(RentalService $rentalService)
    {
        // load the createdBy relationship
        $rentalService->load('createdBy');
        $response = new RentalServiceResource($rentalService);

        return ApiResponse::success($response, 'Rental service retrieved successfully', 200, $rentalService);        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRentalServiceRequest $request, RentalService $rentalService)
    {
        $data = $request->validated();

        try {
            //code...
            DB::beginTransaction();
            // update farmer
            $rentalService->update($data);
    
            // transform data
            $response = new RentalServiceResource($rentalService);
            
            // log activity
            info('Rental service updated', [$rentalService]);
            Activity::create([
                'user_id' => ActorHelper::getUserId(),
                'description' => 'updated farmer',
                'logs' => $rentalService
            ]);

            // commit transaction
            DB::commit();
    
            // return response
            return ApiResponse::success($response, 'Rental service updated successfully', 200, $rentalService);
        } catch (\Throwable $th) {
            //throw $th;
            // rollback transaction
            DB::rollBack();
            // log error
            Log::error('failed to update rental service', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);
            // return error response
            return ApiResponse::error([], 'failed to update rental service '. $th->getMessage(), 500); 
        }    
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RentalService $rentalService)
    {
        // delete farmer
        $rentalService->delete();

        // return response
        return ApiResponse::success([], 'Rental service deleted successfully', 200);        
    }
}
