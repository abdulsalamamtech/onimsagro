<?php

namespace App\Http\Controllers;

use App\Helpers\ActorHelper;
use App\Helpers\ApiResponse;
use App\Helpers\CustomGenerator;
use App\Http\Requests\StoreWarehouseRequest;
use App\Http\Requests\UpdateWarehouseRequest;
use App\Http\Resources\WarehouseResource;
use App\Models\Activity;
use App\Models\Asset;
use App\Models\Warehouse;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // get all data
        $warehouses = Warehouse::with(['banner', 'images.asset'])->latest()->paginate(10);
        // check if data is empty
        if ($warehouses->isEmpty()) {
            return ApiResponse::error([], "No warehouses found", 404);
        }
        // transform data
        $response = WarehouseResource::collection($warehouses);
        // return response
        return ApiResponse::success($response, 'successful', 200);        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWarehouseRequest $request)
    {
        // validated data
        $data = $request->validated();
        
        try {
            
            // begin transaction
            DB::beginTransaction();

            // set created_by
            $data['created_by'] = ActorHelper::getUserId();

            // Adding sku if empty
            if (empty($data['sku'])) {
                $data['sku'] = CustomGenerator::generateUniqueSKU();
            }
            // If status is empty
            if (empty($data['status'])) {
                $data['status'] = 'active';
            }

            // upload banner
            if ($request->hasFile('banner')) {
                // upload file to cloudinary
                $cloudinaryImage = Cloudinary::uploadApi()->upload($request->file('banner')->getRealPath());
                // save product banner to assets
                $asset = Asset::create([
                    'name' =>  $request->file('banner'),
                    'description' => 'product upload',
                    'url' => $cloudinaryImage['url'],
                    'file_id' => $cloudinaryImage['public_id'],
                    'type' => $cloudinaryImage['resource_type'],
                    'size' => $cloudinaryImage['bytes'],
                ]);
                // save product banner
                $data['banner_id'] = $asset->id;
            }
            
            // create warehouse
            $warehouse = Warehouse::create($data);

            // Upload images
            if ($request->hasFile('images')) {
                foreach($request->file('images') as $file){
                    // upload file to cloudinary
                    $cloudinaryImage = Cloudinary::uploadApi()->upload($file->getRealPath());
                    // save image to assets
                    $asset = Asset::create([
                        'name' =>  $data['name'],
                        'description' => 'product upload',
                        'url' => $cloudinaryImage['url'],
                        'file_id' => $cloudinaryImage['public_id'],
                        'type' => $cloudinaryImage['resource_type'],
                        'size' => $cloudinaryImage['bytes'],
                    ]);
                    info('product image', [$asset]);
                    // save images
                    $warehouse->images()->create(['asset_id' => $asset->id]);
                }    
            }

            // Load relationships
            $warehouse->load(['banner', 'images.asset']);

            // transform data
            $response = new WarehouseResource($warehouse);


            // log activity
            info('warehouse created', [$warehouse]);
            Activity::create([
                'user_id' => ActorHelper::getUserId()?? null,
                'description' => 'created warehouse',
                'logs' => $warehouse
            ]);
            // commit transaction
            DB::commit();
            // return response
            return ApiResponse::success($response, 'warehouse created successfully', 201, $response);
           
        }catch (\Throwable $th) {
            //throw $th;
            // rollback transaction
            DB::rollBack();
            // log error
            Log::error('Failed to create warehouse', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);
            // return error response
            return ApiResponse::error([], 'Failed to create warehouse '. $th->getMessage(), 500);
        }        
    }

    /**
     * [public] Display the specified resource.
     */
    public function show(Warehouse $warehouse)
    {
        // load relationships
        $warehouse->load(['banner', 'images.asset']);
        // transform data
        $response = new WarehouseResource($warehouse);
        // return response
        return ApiResponse::success($response);    
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse)
    {
        $data = $request->validated();

        try {
            // begin transaction
            DB::beginTransaction();

            // update product
            $warehouse->update($data);

            // transform data
            $response = new WarehouseResource($warehouse);

            // log activity
            info('warehouse updated', [$warehouse]);
            Activity::create([
                'user_id' => ActorHelper::getUserId()?? null,
                'description' => 'updated warehouse',
                'logs' => $warehouse
            ]);
            
            // commit transaction
            DB::commit();

            // return response
            return ApiResponse::success($response, 'warehouse updated successfully', 200, $warehouse);

        } catch (\Throwable $th) {
            //throw $th;
            // rollback transaction
            DB::rollBack();
            // log error
            Log::error('Failed to update warehouse', $th->getMessage());
            // return error response
            return ApiResponse::error('Failed to update warehouse', 500);
        }        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Warehouse $warehouse)
    {
        try {
            // begin transaction
            DB::beginTransaction();

            // delete product
            $warehouse->delete();

            // log activity
            info('warehouse deleted', [$warehouse]);
            Activity::create([
                'user_id' => ActorHelper::getUserId()?? null,
                'description' => 'deleted warehouse',
                'logs' => $warehouse
            ]);

            // commit transaction
            DB::commit();

            // return response
            return ApiResponse::success([], 'warehouse deleted successfully', 200);

        } catch (\Throwable $th) {
            //throw $th;
            // rollback transaction
            DB::rollBack();
            // log error
            Log::error('Failed to delete warehouse', $th);
            // return error response
            return ApiResponse::error('Failed to delete warehouse', 500);
        }
    }


    /**
     * [public] Display all active warehouses.
     */
    public function getWarehouses()
    {
        // get all products
        $warehouses = Warehouse::where('status', 'active')
            ->with(['banner', 'images.asset'])
            ->latest()->paginate(10);
        // transform data
        $response = WarehouseResource::collection($warehouses);
        // return response
        return ApiResponse::success($response, 'successful', 200);
    }


     /**
      * Get trashed data
      */
      public function trashed(){
        // Get data
        $warehouses = Warehouse::onlyTrashed()->paginate();
        // check if data is empty
        if ($warehouses->count() < 1) {
            return ApiResponse::error([], "No trashed data found", 404);
        }
        // transform data [add pagination to data]
        $response = WarehouseResource::collection($warehouses);
        // return response
        return ApiResponse::success($response, "successfully load trashed data", 200);
      }


    /**
     * Restore deleted data
     */
    public function restore($id){
        // Get data
        $warehouse = Warehouse::onlyTrashed()->find($id);
        // check if data is empty
        if (!$warehouse) {
            return ApiResponse::error([], "No trashed data found", 404);
        }
        // transform data
        $warehouse->restore();
        // return response
        return ApiResponse::success($warehouse, "successfully restored trashed data", 200);
    }    

    /**
     * [public] Search for Warehouse
     */
    public function searchWarehouse(Request $request)
    {

        // return "searching...";
        if ($request->filled('query')) {
            $search = $request->input('query');

            $products = Warehouse::with(['banner', 'images.asset'])
                ->where('status', 'active')
                ->whereAny([
                    'name',
                    'description',
                    'capacity',
                    'sku',
                    'price',
                    'tag',
                    'location',
                ], 'LIKE', "%$search%")
                ->latest()->paginate(10);

            // check if data is empty
            if ($products->isEmpty()) {
                return ApiResponse::error([], "No warehouse found", 404);
            }
            // transform data
            $response = WarehouseResource::collection($products);
            // return response
            return ApiResponse::success($response, 'successful', 200);
        }

        return ApiResponse::error([], "No warehouse found", 404);
    }    
}
