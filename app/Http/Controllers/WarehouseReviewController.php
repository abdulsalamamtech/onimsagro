<?php

namespace App\Http\Controllers;

use App\Helpers\ActorHelper;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreWarehouseReviewRequest;
use App\Http\Requests\UpdateWarehouseReviewRequest;
use App\Http\Resources\WarehouseResource;
use App\Models\WarehouseReview;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WarehouseReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // get all data
        $warehousesReviews = WarehouseReview::latest()->paginate(10);
        // check if data is empty
        if ($warehousesReviews->isEmpty()) {
            return ApiResponse::error([], "No warehouse reviews found", 404);
        }
        // transform data
        $response = WarehouseResource::collection($warehousesReviews);
        // return response
        return ApiResponse::success($response, 'successful', 200);            
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWarehouseReviewRequest $request)
    {
        // validated data
        $data = $request->validated();

        try {
            // begin transaction
            DB::beginTransaction();

            // set created_by
            $data['user_id'] = ActorHelper::getUserId() ?? null;

            // create product review
            $warehouse_review = WarehouseReview::create($data);
            
            // transform data
            $response = new WarehouseResource($warehouse_review);

            // commit transaction
            DB::commit();

            return ApiResponse::success($response, 201);

        } catch (\Exception $e) {
            // rollback transaction
            DB::rollBack();
            // log error
            Log::error('Failed to create warehouse review', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error([], 'failed to create warehouse review ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(WarehouseReview $warehouseReview)
    {
        // transform data
        $response = new WarehouseResource($warehouseReview);
        // return response
        return ApiResponse::success($response, 'successful', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWarehouseReviewRequest $request, WarehouseReview $warehouseReview)
    {
        // validated data
        $data = $request->validated();

        try {
            // begin transaction
            DB::beginTransaction();

            // update product review
            $warehouseReview->update($data);
            
            // transform data
            $response = new WarehouseResource($warehouseReview);

            // commit transaction
            DB::commit();

            return ApiResponse::success($response, 200);

        } catch (\Exception $e) {
            // rollback transaction
            DB::rollBack();
            // log error
            Log::error('Failed to update warehouse review', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error([], 'failed to update warehouse review ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WarehouseReview $warehouseReview)
    {
        try {
            // begin transaction
            DB::beginTransaction();

            // delete warehouse review
            $warehouseReview->delete();

            // commit transaction
            DB::commit();

            return ApiResponse::success([], 'successful', 200);

        } catch (\Exception $e) {
            // rollback transaction
            DB::rollBack();
            // log error
            Log::error('Failed to delete warehouse review', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error([], 'failed to delete warehouse review ' . $e->getMessage(), 500);
        }
    }
}
