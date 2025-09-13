<?php

namespace App\Http\Controllers;

use App\Helpers\ActorHelper;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductCategoryRequest;
use App\Http\Requests\UpdateProductCategoryRequest;
use App\Http\Resources\ProductCategoryResource;
use App\Models\Activity;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // get all product categories
        $productCategories = ProductCategory::with(['createdBy'])
        ->withCount('products')->latest()->paginate(10);

        // transform data
        $response = ProductCategoryResource::collection($productCategories);

        // return response
        return ApiResponse::success($response, 'successful', 200, $productCategories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductCategoryRequest $request)
    {
        $data = $request->validated();

        try {
            // begin transaction
            DB::beginTransaction();

            // set created_by
            $data['created_by'] = ActorHelper::getUserId();

            // create product category
            $productCategory = ProductCategory::create($data);

            // transform data
            $response = new ProductCategoryResource($productCategory);

            // commit transaction
            DB::commit();
            // log activity
            info('product category created', [$productCategory]);
            Activity::create([
                'user_id' => ActorHelper::getUserId(),
                'description' => 'created product category',
                'logs' => $productCategory
            ]);

            // return response
            return ApiResponse::success($response, 'Product category created successfully', 201, $productCategory);

        } catch (\Throwable $th) {
            //throw $th;
            // rollback transaction
            DB::rollBack();
            // log error
            Log::error('Failed to create product type', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);
            // return error response
            return ApiResponse::error([], 'Failed to create product type '. $th->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductCategory $productCategory)
    {
        // transform data
        $response = new ProductCategoryResource($productCategory);

        // return response
        return ApiResponse::success($response, 'successful', 200, $productCategory);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductCategoryRequest $request, ProductCategory $productCategory)
    {
        $data = $request->validated();

        try {
            // begin transaction
            DB::beginTransaction();

            // update product category
            $productCategory->update($data);

            // transform data
            $response = new ProductCategoryResource($productCategory);

            // commit transaction
            DB::commit();
            // log activity
            info('Product category updated', [$productCategory]);
            Activity::create([
                'user_id' => ActorHelper::getUserId()?? null,
                'description' => 'updated product category',
                'logs' => $response
            ]);

            // return response
            return ApiResponse::success($response, 'Product category updated successfully', 200, $productCategory);

        } catch (\Throwable $th) {
            //throw $th;
            // rollback transaction
            DB::rollBack();
            // log error
            Log::error($this, $th);
            // return error response
            return ApiResponse::error('Failed to update product category', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductCategory $productCategory)
    {
        // delete product category
        $productCategory->delete();

        // return response
        return ApiResponse::success([], 'Product category deleted successfully', 200);
    }
}
