<?php

namespace App\Http\Controllers;

use App\Helpers\ActorHelper;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Activity;
use App\Models\Order;
use App\Models\Product;
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
        $orders = Order::with(['orderItems.product'])->latest()->paginate(10);
        // transform data
        $response = OrderResource::collection($orders);
        // return response
        return ApiResponse::success($response);
    }

    /**
     * [public] Store a newly created resource in storage.
     */
    public function store(StoreOrderRequest $request)
    {
        // validated data
        $data = $request->validated();

        try {
            // begin transaction
            DB::beginTransaction();
            // 'user_id' => 'required|exists:users,id',
            // 'updated_by' => 'required|exists:users,id',
            // 'total_price' => 'required|numeric|min:0',
            // 'status' => 'required|string|in:pending,completed,cancelled',
            // 'order_items.*.unit_price' => 'required|numeric|min:0',
            // 'order_items.*.total_price' => 'required|numeric|min:0',
            
            // set created_by
            // $data['created_by'] = ActorHelper::getUserId();
            // 'order_id',
            // 'product_id',
            // 'quantity',
            // 'unit_price',
            // 'total_price',


            // Calculate the entire order product
            $data['total_price'] = 0;
            $order_items = [];
            foreach ($data['order_items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $order_items[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'total_price' => $product->price * $item['quantity'],
                ];
                $data['total_price'] += $product->price * $item['quantity'];
            }
        
            // create order
            $order = Order::create($data);

            // Create many order items
            $order->orderItems()->createMany($order_items);
            // transform data
            $response = new OrderResource($order);
            
            // log activity
            info('order created', [$order]);
            Activity::create([
                'user_id' => ActorHelper::getUserId(),
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
            return ApiResponse::error([], 'Order creation failed ' . $th->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        // get data relationships
        $order->load(['orderItems.product']);
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

            // log activity
            info('order updated', [$order]);
            Activity::create([
                'user_id' => ActorHelper::getUserId()?? null,
                'description' => 'updated order',
                'logs' => $order
            ]);
            // commit transaction
            DB::commit();

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
