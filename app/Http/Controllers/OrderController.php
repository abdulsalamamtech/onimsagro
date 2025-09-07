<?php

namespace App\Http\Controllers;

use App\Helpers\ActorHelper;
use App\Helpers\ApiResponse;
use App\Helpers\Paystack;
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

            // Get total payment amount
            $totalPayAmount = $order?->total_price;
            if (!$totalPayAmount) {
                return ApiResponse::error([], 'Error: unable to retrieve order payment amount!', 500);
            }

            // Create the payment data
            $payment_data = [
                'name' => $order->full_name,
                'email' => $order->email,
                'amount' => round($totalPayAmount, 2),
                'payment_id' => $order->id,
                // 'redirect_url' => URL('account/orders'),
                // 'redirect_url' => config('app.frontend_url') . '/payment/success?order_id=' . $order->id,
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
                $order->transactions()->create([
                    'user_id' => $order->user_id,
                    'order_id' => $order?->id,
                    'full_name' => $order->full_name,
                    'email' => $order->email,
                    'payment_type' => 'order',
                    'amount' => $totalPayAmount,
                    'status' => 'pending',
                    'reference' => $PSP['reference'],
                    'payment_provider' => $PSP['gateway'],
                    'data' => json_encode($PSP)
                ]);

                // Payment link
                $response = [
                    'order_id' => $order->id,
                    'amount' => $totalPayAmount,
                    // transform data
                    'order' => new OrderResource($order),
                    'payment_link' => $PSP['authorization_url'],
                ];

                // log activity
                info('order created', [$order]);
                Activity::create([
                    'user_id' => ActorHelper::getUserId(),
                    'description' => 'created order and payment link',
                    'logs' => $order
                ]);

                // commit transaction
                DB::commit();

                // return response
                return ApiResponse::success($response, 'order created successfully, please make payment to validate your order!', 201, $response);
            } else {
                info('payment initialization error: ' . $PSP['message']);
                return ApiResponse::error([], 'Error: unable to initialize payment process!', 500);
            }
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
        // remove empty values and get unique values
        // $data = array_unique($data);
        $data = array_filter($data, function ($value) {
            return !is_null($value) && $value !== '';
        });
        // return $data;
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
                'user_id' => ActorHelper::getUserId() ?? null,
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

        return ApiResponse::error([], 'order can\'t be deleted');
        // delete order
        // $order->delete();

        // return response
        return ApiResponse::success([], 'Order deleted successfully', 200);
    }

    /**
     * Confirm order.
     */
    public function confirmOrder(Order $order)
    {

        // update order status to completed
        $order->status = ($order->status == 'pending') ? 'confirmed' : $order->status;
        $order->save();
        // load relationships
        $order->status = 'confirmed';
        $order->save();
        // transform data
        $response = new OrderResource($order);
        // return response
        return ApiResponse::success($response, 'Order confirmed successfully', 200);
    }

    /**
     * Cancel order.
     */
    public function cancelOrder(Order $order)
    {
        // if order is paid
        if($order?->is_paid){
            return ApiResponse::error([], "order has already been paid for!", 401);
        }
        // load relationships
        $order->status = 'cancelled';
        $order->save();
        // transform data
        $response = new OrderResource($order);
        // return response
        return ApiResponse::success($response, 'Order cancelled successfully', 200);
    }

    /**
     * Get order status.
     */
    public function getOrderStatus()
    {
        // get order status
        $response = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
        // return response
        return ApiResponse::success($response, 'successful', 200);
    }

    /**
     * Verify Order
     */
    public function verifyOrder(Order $order){
        // load relationships
        $order->load(['transactions']);
        // verify if order has a successful transaction
        if(!$order?->is_paid || $order?->transactions->isEmpty()){
            return ApiResponse::error([], 'Error: Order has no successful transaction!', 400);
        }
        // update order status to completed
        $order->status = ($order->status == 'pending') ? 'completed' : $order->status;
        $order->save();
        // transform data
        $response = new OrderResource($order);
        // return response
        return ApiResponse::success($response, 'Order retrieved successfully', 200);
    }
}
