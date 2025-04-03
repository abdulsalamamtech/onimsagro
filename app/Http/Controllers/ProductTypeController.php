<?php

namespace App\Http\Controllers;

use App\Helpers\ActorHelper;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductTypeRequest;
use App\Http\Requests\UpdateProductTypeRequest;
use App\Http\Resources\ProductTypeResource;
use App\Models\Activity;
use App\Models\ProductType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // get all product types
        $productTypes = ProductType::latest()->paginate(10);

        // transform data
        $response = ProductTypeResource::collection($productTypes);

        // return response
        return ApiResponse::success($response, 'successful', 200, $productTypes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductTypeRequest $request)
    {
        $data = $request->validated();

        try {
            // start transaction
            DB::beginTransaction();
            // create data
            $data['created_by'] = ActorHelper::getUserId() ?? null;
            $productType = ProductType::create($data);
            // transform data
            $response = new ProductTypeResource($productType);

            // commit transaction and log activity 
            DB::commit();
            info($this, [$productType]);
            Activity::create([
                'user_id' => ActorHelper::getUserId() ?? null,
                'description' => 'created product type',
                'logs' => $response
            ]);

            return ApiResponse::success($response, 'successful', 201, $productType);
        } catch (\Throwable $th) {
            //throw $th;
            // rollback transaction
            DB::rollBack();
            LOG::error($this, $th);
            return ApiResponse::error('Failed to create product type', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductType $productType)
    {
        // transform data
        $response = new ProductTypeResource($productType);

        // return response
        return ApiResponse::success($response, 'successful', 200, $productType);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductTypeRequest $request, ProductType $productType)
    {
        $data = $request->validated();

        try {
            // start transaction
            DB::beginTransaction();
            // update data
            $productType->update($data);
            // transform data
            $response = new ProductTypeResource($productType);

            // commit transaction and log activity 
            DB::commit();
            info($this, [$productType]);
            Activity::create([
                'user_id' => ActorHelper::getUserId() ?? null,
                'description' => 'updated product type',
                'logs' => $response
            ]);

            return ApiResponse::success($response, 'successful', 200, $productType);
        } catch (\Throwable $th) {
            //throw $th;
            // rollback transaction
            DB::rollBack();
            Log::error($this, $th);
            return ApiResponse::error('Failed to update product type', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductType $productType)
    {
        // Delete data from storage
        $productType->delete();

        // return response
        return ApiResponse::success([], 'Product type deleted successfully', 204);
    }
}
