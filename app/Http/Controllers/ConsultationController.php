<?php

namespace App\Http\Controllers;

use App\Helpers\ActorHelper;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreConsultationRequest;
use App\Http\Requests\UpdateConsultationRequest;
use App\Http\Resources\ConsultationResource;
use App\Models\Activity;
use App\Models\Consultation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConsultationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $consultations = Consultation::latest()->paginate(10);
        // transform data
        $response = ConsultationResource::collection($consultations);

        return ApiResponse::success($response, 'successful', 200 , $consultations);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreConsultationRequest $request)
    {
        // validate data
        $data = $request->validated();


        try {
            // start transaction
            DB::beginTransaction();
            // create consultation
            $consultation = Consultation::create($data);
            // transform data
            $response = new ConsultationResource($consultation);
            
            // commit transaction and log activity 
            DB::commit();
            info($this, [$consultation]);
            Activity::create([
                'user_id' => ActorHelper::getUserId() ?? null,
                'description' => 'created consultation',
                'logs' => $response
            ]);

            // return response
            return ApiResponse::success($response, 'Consultation created successfully', 201, $consultation);

        } catch (\Throwable $th) {
            //throw $th;
            $message = $th->getMessage();

            DB::rollBack();
            Log::error('Error rolling back transaction: ', [$message]);
            return ApiResponse::error($th, $message);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(Consultation $consultation)
    {
        // transform data
        $response = new ConsultationResource($consultation);
        // return response
        return ApiResponse::success($response, 'Consultation retrieved successfully', 200, $consultation);  
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateConsultationRequest $request, Consultation $consultation)
    {
        // validate data
        $data = $request->validated();
        
        try {
            // start transaction
            DB::beginTransaction();
            // update consultation
            $consultation->update($data);
            // transform data
            $response = new ConsultationResource($consultation);
            
            // commit transaction and log activity 
            DB::commit();
            info($this, [$consultation]);
            Activity::create([
                'user_id' => ActorHelper::getUserId() ?? null,
                'description' => 'updated consultation',
                'logs' => $response
            ]);

            // return response
            return ApiResponse::success($response, 'Consultation updated successfully', 200, $consultation);

        } catch (\Throwable $th) {
            //throw $th;
            $message = $th->getMessage();
            DB::rollBack();
            Log::error('Error rolling back transaction: ', [$message]);
            return ApiResponse::error($th, $message);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Consultation $consultation)
    {
        // delete consultation
        $consultation->delete();
        // return response
        return ApiResponse::success([], 'Consultation deleted successfully', 200);
    }
}
