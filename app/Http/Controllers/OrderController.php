<?php

namespace App\Http\Controllers;

use App\Helpers\ActorHelper;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Activity;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // get all products
        $orders = Order::latest()->paginate(10);
        // transform data
        $response = OrderResource::collection($orders);
        // return response
        return ApiResponse::success($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrderRequest $request)
    {
        // validated data
        $data = $request->validated();

        try {
            // begin transaction
            DB::beginTransaction();

            // set created_by
            // $data['created_by'] = ActorHelper::getUserId();

            // create order
            $order = Order::create($data);
            // transform data
            $response = new OrderResource($order);
            
            // commit transaction
            // DB::commit();
            // log activity
            info('order created', [$order]);
            Activity::create([
                'user_id' => ActorHelper::getUserId()?? null,
                'description' => 'created order',
                'logs' => $order
            ]);

            // commit transaction
            DB::commit();
            // return response
            return ApiResponse::success($response, 'order created successfully', 201, $response);
        } catch (\Throwable $th) {
            DB::rollBack();
            // log error
            // log error
            Log::error('Failed to create order', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);
            // return error response
            return ApiResponse::error([], 'Order creation failed ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        // transform data
        $response = new OrderResource($order);
        // return response
        return ApiResponse::success($response, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrderRequest $request, Order $order)
    {
        // validated data
        $data = $request->validated();

        try {
            // begin transaction
            DB::beginTransaction();

            // set updated_by
            $data['updated_by'] = ActorHelper::getUserId();

            // update order
            $order->update($data);
            // transform data
            $response = new OrderResource($order);

            // commit transaction
            DB::commit();
            // log activity
            info('order updated', [$order]);
            Activity::create([
                'user_id' => ActorHelper::getUserId()?? null,
                'description' => 'updated order',
                'logs' => $order
            ]);

            // return response
            return ApiResponse::success($response, 'Order updated successfully', 200, $response);
        } catch (\Throwable $th) {
            DB::rollBack();
            // log error
            Log::error('Failed to update order', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);
            // return error response
            return ApiResponse::error([], 'Order update failed ' . $th->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {

        // delete order
        $order->delete();

        // return response
        return ApiResponse::success([], 'Order deleted successfully', 200);

    }
}
