<?php

namespace App\Http\Controllers;

use App\Helpers\ActorHelper;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreProductReviewRequest;
use App\Http\Requests\UpdateProductReviewRequest;
use App\Http\Resources\ProductReviewResource;
use App\Models\ProductReview;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // get all product reviews
        $product_reviews = ProductReview::with('user', 'product')->latest()->paginate();

        // check if it's empty
        if ($product_reviews->isEmpty()) {
            return ApiResponse::error([], 'product reviews not found', 404);
        }
        // transform data
        $response = ProductReviewResource::collection($product_reviews);
        // return response
        return ApiResponse::success($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductReviewRequest $request)
    {
        // validated data
        $data = $request->validated();

        try {
            // begin transaction
            DB::beginTransaction();

            // set created_by
            $data['user_id'] = ActorHelper::getUserId();

            // create product review
            $product_review = ProductReview::create($data);

            // transform data
            $response = new ProductReviewResource($product_review);

            // commit transaction
            DB::commit();

            return ApiResponse::success($response, 201);
        } catch (\Exception $e) {
            // rollback transaction
            DB::rollBack();
            // log error
            Log::error('Failed to create product review', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error([], 'failed to create product review ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductReview $productReview)
    {
        // transform data
        $response = new ProductReviewResource($productReview);
        // return response
        return ApiResponse::success($response, 'successful', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductReviewRequest $request, ProductReview $productReview)
    {
        // validated data
        $data = $request->validated();

        try {
            // begin transaction
            DB::beginTransaction();

            // update product review
            $productReview->update($data);

            // commit transaction
            DB::commit();

            // transform data
            $response = new ProductReviewResource($productReview);

            return ApiResponse::success($response, 'Product review updated successfully', 200);
        } catch (\Exception $e) {
            // rollback transaction
            DB::rollBack();
            // log error
            Log::error('Failed to update product review', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error([], 'failed to update product review ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductReview $productReview)
    {

        // delete product review
        $productReview->delete();

        return ApiResponse::success([], 'Product review deleted successfully', 200);
    }

    /**
     * [public] Display a listing of the resource.
     */
    public function getReviews()
    {
        // get all product reviews
        $product_reviews = ProductReview::where('status', 'approved')->with('user', 'product')->latest()->paginate();
        
        // check if it's empty
        if ($product_reviews->isEmpty()) {
            return ApiResponse::error([], 'product reviews not found', 404);
        }
        // transform data
        $response = ProductReviewResource::collection($product_reviews);
        // return response
        return ApiResponse::success($response, 200);
    }
}
