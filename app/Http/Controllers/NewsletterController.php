<?php

namespace App\Http\Controllers;

use App\Helpers\ActorHelper;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreNewsletterRequest;
use App\Http\Requests\UpdateNewsletterRequest;
use App\Http\Resources\NewsletterResource;
use App\Models\Activity;
use App\Models\Newsletter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NewsletterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $newsletters = Newsletter::latest()->paginate();

        // check if there are no farmers
        if ($newsletters->isEmpty()) {
            return ApiResponse::error([], 'No newsletter found', 404);
        }

        // transform data
        $response = NewsletterResource::collection($newsletters);

        // return response
        return ApiResponse::success($response, 'successful', 200, $newsletters);
        
    }

    /**
     * [public] Store a newly created resource in storage.
     */
    public function store(StoreNewsletterRequest $request)
    {
        $data = $request->validated();

        try {
            //code...
            DB::beginTransaction();
            // create newsletter
            $newsletter = newsletter::create($data);
    
            // transform data
            $response = new NewsletterResource($newsletter);

            // log activity
            info('newsletter created', [$newsletter]);
            Activity::create([
                'user_id' => ActorHelper::getUserId(),
                'description' => 'created newsletter',
                'logs' => $newsletter
            ]);            
    
            // Commit 
            DB::commit();
            // return response
            return ApiResponse::success($response, 'newsletter created successfully', 201, $newsletter);

        } catch (\Throwable $th) {
            //throw $th;
            // rollback transaction
            DB::rollBack();
            // log error
            Log::error('failed to create newsletter', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);
            // return error response
            return ApiResponse::error([], 'failed to create newsletter '. $th->getMessage(), 500);             
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Newsletter $newsletter)
    {
        return ApiResponse::success($newsletter, 'newsletter retrieved successfully', 200);    
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNewsletterRequest $request, Newsletter $newsletter)
    {
        $data = $request->validated();

        // update data
        $newsletter->update($data);

        // transform data
        $response = new NewsletterResource($newsletter);

        // return response
        return ApiResponse::success($response, 'newsletter updated successfully', 200, $newsletter);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Newsletter $newsletter)
    {
        $newsletter->delete();

        return ApiResponse::success([], 'newsletter deleted successfully', 200);        
    }
}
