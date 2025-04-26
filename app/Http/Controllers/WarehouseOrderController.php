<?php

namespace App\Http\Controllers;

use App\Helpers\ActorHelper;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreWarehouseOrderRequest;
use App\Http\Requests\UpdateWarehouseOrderRequest;
use App\Http\Resources\WarehouseOrderResource;
use App\Models\Activity;
use App\Models\Warehouse;
use App\Models\WarehouseOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WarehouseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // get all data
        $warehouseOrders = WarehouseOrder::with(['warehouse'])->latest()->paginate(10);
        // check if data is empty
        if ($warehouseOrders->isEmpty()) {
            return ApiResponse::error([], "No warehouses found", 404);
        }
        // transform data
        $response = WarehouseOrderResource::collection($warehouseOrders);
        // return response
        return ApiResponse::success($response, 'successful', 200);          
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWarehouseOrderRequest $request)
    {
      // validated data
      $data = $request->validated();

      try {
          // begin transaction
          DB::beginTransaction();

          // Calculate the warehouse order 
          $warehouse = Warehouse::findOrFail($data['warehouse_id']);
          $data['total_price'] = $warehouse->price;
      
          // create order
          $warehouse_order = WarehouseOrder::create($data);

          // transform data
          $response = new WarehouseOrderResource($warehouse_order);
          
          // log activity
          info('warehouse order created', [$warehouse_order]);
          Activity::create([
              'user_id' => ActorHelper::getUserId(),
              'description' => 'created warehouse order',
              'logs' => $warehouse_order
          ]);
          
          // commit transaction
          DB::commit();
          // return response
          return ApiResponse::success($response, 'warehouse order created successfully', 201, $response);
      } catch (\Throwable $th) {
          DB::rollBack();
          // log error
          // log error
          Log::error('Failed to create warehouse order', [
              'error' => $th->getMessage(),
              'trace' => $th->getTraceAsString(),
          ]);
          // return error response
          return ApiResponse::error([], 'Warehouse order creation failed ' . $th->getMessage(), 500);
      }    
    }

    /**
     * Display the specified resource.
     */
    public function show(WarehouseOrder $warehouseOrder)
    {
        // load relationships
        $warehouseOrder->load(['warehouse']);
        // transform data
        $response = new WarehouseOrderResource($warehouseOrder);
        // return response
        return ApiResponse::success($response); 
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWarehouseOrderRequest $request, WarehouseOrder $warehouseOrder)
    {
        $data = $request->validated();
        // remove empty values and get unique values
        // $data = array_unique($data);
        $data = array_filter($data, function ($value) {
            return !is_null($value) && $value !== '';
        });        
        
        try {
            // begin transaction
            DB::beginTransaction();
            
            $data['updated_by'] = ActorHelper::getUserId();

            // update product
            $warehouseOrder->update($data);

            // transform data
            $response = new WarehouseOrderResource($warehouseOrder);

            // log activity
            info('warehouse order updated', [$warehouseOrder]);
            Activity::create([
                'user_id' => ActorHelper::getUserId()?? null,
                'description' => 'updated warehouse order',
                'logs' => $warehouseOrder
            ]);
            
            // commit transaction
            DB::commit();

            // return response
            return ApiResponse::success($response, 'warehouse order updated successfully', 200, $warehouseOrder);

        } catch (\Throwable $th) {
            //throw $th;
            // rollback transaction
            DB::rollBack();
            // log error
            Log::error('Failed to update warehouse order', $th->getMessage());
            // return error response
            return ApiResponse::error('Failed to update warehouse order', 500);
        }         
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WarehouseOrder $warehouseOrder)
    {
        // Delete data from storage
        $warehouseOrder->delete();

        // return response
        return ApiResponse::success([], 'Warehouse order deleted successfully', 200);        
    }

    /**
     * Confirm order.
     */
    public function confirmOrder(WarehouseOrder $warehouseOrder)
    {
        // load relationships
        $warehouseOrder->status = 'confirmed';
        $warehouseOrder->save();
        // transform data
        $response = new WarehouseOrderResource($warehouseOrder);
        // return response
        return ApiResponse::success($response, 'Warehouse order confirmed successfully', 200);
    }  

    /**
     * Cancel order.
     */
    public function cancelOrder(WarehouseOrder $warehouseOrder)
    {
        // load relationships
        $warehouseOrder->status = 'cancelled';
        $warehouseOrder->save();
        // transform data
        $response = new WarehouseOrderResource($warehouseOrder);
        // return response
        return ApiResponse::success($response, 'Warehouse order cancelled successfully', 200);
    } 

    /**
     * Get order status.
     */
    public function getOrderStatus()
    {
        // get order status
        $response = ['pending', 'confirmed', 'processing', 'cancelled'];
        // return response
        return ApiResponse::success($response, 'successful', 200);
    }    
}
