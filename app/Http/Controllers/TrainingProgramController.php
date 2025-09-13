<?php

namespace App\Http\Controllers;

use App\Helpers\ActorHelper;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreTrainingProgramRequest;
use App\Http\Requests\UpdateTrainingProgramRequest;
use App\Http\Resources\TrainingProgramResource;
use App\Models\Activity;
use App\Models\TrainingProgram;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TrainingProgramController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // get all product reviews
        $trainingPrograms = TrainingProgram::with('createdBy', 'farmingInterest')->latest()->paginate();
        // check if there are no farmers
        if ($trainingPrograms->isEmpty()) {
            return ApiResponse::error([], 'No training program found', 404);
        }
        // transform data
        $response = TrainingProgramResource::collection($trainingPrograms);
        // return response
        return ApiResponse::success($response, 200);
    }

    /**
     * [public] Store a newly created resource in storage.
     */
    public function store(StoreTrainingProgramRequest $request)
    {
        $data = $request->validated();

        try {
            //code...
            // begin transaction
            DB::beginTransaction();

            // set created_by
            $data['created_by'] = ActorHelper::getUserId();

            // Save to database
            $trainingProgram = TrainingProgram::create($data);

            // transform data
            $response = new TrainingProgramResource($trainingProgram);

            // log activity
            info('training program created', [$trainingProgram]);
            Activity::create([
                'user_id' => ActorHelper::getUserId(),
                'description' => 'created training program',
                'logs' => $trainingProgram
            ]);

            // commit transaction
            DB::commit();

            // return response
            return ApiResponse::success($response, 'training program created successfully', 201, $response);            
        } catch (\Throwable $th) {
            //throw $th;
            // rollback transaction
            DB::rollBack();
            // log error
            Log::error('failed to create training program', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);
            // return error response
            return ApiResponse::error([], 'failed to create training program '. $th->getMessage(), 500);            
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(TrainingProgram $trainingProgram)
    {
        $response = new TrainingProgramResource($trainingProgram);

        return ApiResponse::success($response);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTrainingProgramRequest $request, TrainingProgram $trainingProgram)
    {
        $data = $request->validated();

        try {
            //code...
            // begin transaction
            DB::beginTransaction();

            // Update to database
            $trainingProgram->update($data);

            // transform data
            $response = new TrainingProgramResource($trainingProgram);

            // log activity
            info('training program updated', [$trainingProgram]);
            Activity::create([
                'user_id' => ActorHelper::getUserId(),
                'description' => 'updated training program',
                'logs' => $trainingProgram
            ]);

            // commit transaction
            DB::commit();

            // return response
            return ApiResponse::success($response, 'training program updated successfully', 201, $response);            
        } catch (\Throwable $th) {
            //throw $th;
            // rollback transaction
            DB::rollBack();
            // log error
            Log::error('failed to update training program', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);
            // return error response
            return ApiResponse::error([], 'failed to update training program '. $th->getMessage(), 500);            
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TrainingProgram $trainingProgram)
    {
        // delete data
        $trainingProgram->delete();

        // return response
        return ApiResponse::success([], 'training program deleted successfully', 204);
    }
}
