<?php

namespace App\Http\Controllers;

use App\Helpers\ActorHelper;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Activity;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // get all products
        $products = Product::latest()->paginate(10);
        // transform data
        $response = ProductResource::collection($products);
        // return response
        return ApiResponse::success($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        try {

            // begin transaction
            DB::beginTransaction();

            // set created_by
            $data['created_by'] = ActorHelper::getUserId() ?? null;

            // create product category
            $product = ProductCategory::create($data);

            // transform data
            $response = new ProductResource($product);

            // commit transaction
            DB::commit();
            // log activity
            info($this, [$product]);
            Activity::create([
                'user_id' => ActorHelper::getUserId()?? null,
                'description' => 'created product',
                'logs' => $response
            ]);

            // return response
            return ApiResponse::success($response, 'Product created successfully', 201, $productCategory);
           
        } catch (\Throwable $th) {
            //throw $th;
            // rollback transaction
            DB::rollBack();
            // log error
            Log::error($this, $th);
            // return error response
            return ApiResponse::error('Failed to create product', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        // transform data
        $response = new ProductResource($product);
        // return response
        return ApiResponse::success($response, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();

        try {
            // begin transaction
            DB::beginTransaction();

            // update product
            $product->update($data);

            // transform data
            $response = new ProductResource($product);

            // commit transaction
            DB::commit();
            // log activity
            info($this, [$product]);
            Activity::create([
                'user_id' => ActorHelper::getUserId()?? null,
                'description' => 'updated product',
                'logs' => $response
            ]);

            // return response
            return ApiResponse::success($response, 'Product updated successfully', 200, $product);

        } catch (\Throwable $th) {
            //throw $th;
            // rollback transaction
            DB::rollBack();
            // log error
            Log::error($this, $th);
            // return error response
            return ApiResponse::error('Failed to update product', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // delete product
        $product->delete();

        // return response
        return ApiResponse::success([], 'Product deleted successfully', 204);
    }
}
