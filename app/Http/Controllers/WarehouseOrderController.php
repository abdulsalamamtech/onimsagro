<?php

namespace App\Http\Controllers;

use App\Helpers\ActorHelper;
use App\Helpers\ApiResponse;
use App\Helpers\Paystack;
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

            // Get total payment amount
            $totalPayAmount = $warehouse_order?->total_price;
            if (!$totalPayAmount) {
                return ApiResponse::error([], 'Error: unable to retrieve order payment amount!', 500);
            }

            // Create the payment data
            $payment_data = [
                'name' => $warehouse_order->full_name,
                'email' => $warehouse_order->email,
                'amount' => round($totalPayAmount, 2),
                'payment_id' => $warehouse_order->id,
                // 'redirect_url' => URL('account/orders'),
                // 'redirect_url' => config('app.frontend_url'),
                'redirect_url' => route('transactions.verify'),
            ];

            $PSP = Paystack::make($payment_data);
            if ($PSP['success']) {
                // Record The transaction
                // 'user_id',
                // 'order_id',
                // 'amount',
                // 'status',
                // 'reference',
                // 'payment_method',
                // 'data'

                // Create the transaction for the order
                $warehouse_order->transactions()->create([
                    'user_id' => $warehouse_order->user_id,
                    'warehouse_order_id' => $warehouse_order?->id,
                    'full_name' => $warehouse_order->full_name,
                    'email' => $warehouse_order->email,
                    'payment_type' => 'warehouse_order',
                    'amount' => $totalPayAmount,
                    'status' => 'pending',
                    'reference' => $PSP['reference'],
                    'payment_provider' => $PSP['gateway'],
                    'data' => json_encode($PSP)
                ]);

                // Payment link
                $response = [
                    'warehouse_order_id' => $warehouse_order->id,
                    'amount' => $totalPayAmount,
                    // transform data
                    'warehouse_order' => new WarehouseOrderResource($warehouse_order),
                    'payment_link' => $PSP['authorization_url'],
                ];

                // log activity
                info('order created', [$warehouse_order]);
                Activity::create([
                    'user_id' => ActorHelper::getUserId(),
                    'description' => 'created warehouse order and payment link',
                    'logs' => $warehouse_order
                ]);

                // commit transaction
                DB::commit();

                // return response
                return ApiResponse::success($response, 'warehouse order created successfully, please make payment to validate your warehouse order!', 201, $response);
            } else {
                info('payment initialization error: ' . $PSP['message']);
                return ApiResponse::error([], 'Error: unable to initialize payment process!', 500);
            }

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
