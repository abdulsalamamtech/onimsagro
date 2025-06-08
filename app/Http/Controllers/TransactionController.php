<?php

namespace App\Http\Controllers;

use App\Helpers\Paystack;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\WarehouseOrder;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // get all transactions
        $transactions = Transaction::with(['order', 'user'])->latest()->paginate(10);

        // check if transactions are empty
        if ($transactions->isEmpty()) {
            return response()->json(['message' => 'No transactions found'], 404);
        }

        // return response
        return response()->json($transactions, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // validate request data
        $data = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|string|in:pending,completed,failed',
            'payment_method' => 'required|string|max:255',
        ]);

        try {
            // create transaction
            $transaction = Transaction::create($data);

            // return response
            return response()->json($transaction, 201);
        } catch (\Exception $e) {
            // log error and return error response
            return response()->json(['message' => 'Transaction creation failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        return response()->json($transaction, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        // validate request data
        $data = $request->validate([
            'status' => 'sometimes|required|string|in:pending,completed,failed',
            'amount' => 'sometimes|required|numeric|min:0',
            'payment_method' => 'sometimes|required|string|max:255',
        ]);

        try {
            // update transaction
            $transaction->update($data);

            // return response
            return response()->json($transaction, 200);
        } catch (\Exception $e) {
            // log error and return error response
            return response()->json(['message' => 'Transaction update failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        try {
            // delete transaction
            $transaction->delete();

            // return response
            return response()->json(['message' => 'Transaction deleted successfully'], 200);
        } catch (\Exception $e) {
            // log error and return error response
            return response()->json(['message' => 'Transaction deletion failed', 'error' => $e->getMessage()], 500);
        }
    }


    // verify transaction
    public function verifyTransaction(Request $request)
    {
        // http://localhost:3000/?trxref=oo5ihug1qm&reference=oo5ihug1qm
        // http://127.0.0.1:8000/events/8?trxref=soq9s7fxmf&reference=soq9s7fxmf
        
        
        try {
            // Verify payment transaction
            if ($request?->filled('trxref') || $request?->filled('reference')) {
                $reference = $request?->reference ?? $request?->trxref;
                $PSP = Paystack::verify($reference);
                $message = $PSP['message'];
                info('verify payment message: ', [$message]);
                if ($PSP['success']) {
                    $transaction = Transaction::where('reference', $reference)->first();
                    if ($transaction) {
                        $transaction->status = 'successful';
                        $transaction->save();
    
                        // update order
                        // $order = Order::where('id', $transaction->order_id)->first();
                        // $order->paid = 'yes';
                        // if ($order->status == 'pending') {
                        //     $order->status = 'confirmed';
                        //     // Decrement available stock from order items
                        //     // info('START: decrement available stock from order');
                        //     // defer(app('App\Http\Controllers\AccountController')->decrementAvailableStockFromOrder($order), 'decrement available stock');
                        //     // info('PROCESSING: decrement available stock processing');
                        // }
                        // $order->save();
    
                        // $redirectUrl = $transaction->data['redirect_url'] ?? url()->previous();
                        // redirect to success page
                        $redirectUrl = config('app.frontend_url') . '/payment/success?trxref=' . $transaction->reference;
                        // payment type
                        if($transaction->payment_type == 'product') {
                            // update warehouse order
                            $warehouseOrder = WarehouseOrder::where('id', $transaction->warehouse_order_id)->first();
                            $warehouseOrder->status = 'confirmed';
                            $warehouseOrder->save();
                        }
                        if($transaction->payment_type == 'order') {
                            // update service order
                            $order = Order::where('id', $transaction->order_id)->first();
                            $order->status = 'confirmed';
                            $order->save();
                        }
    
                        return redirect($redirectUrl);
    
                    }
                }else{
                    // log error and return error response
                    info('Transaction verification failed: ', [$message]);
                    // Redirect to error page
                    $redirectUrl = config('app.frontend_url') . '/payment/error?trxref=' . $reference;
                }

            }
            
            // return response
            return redirect($redirectUrl);
            
        } catch (\Exception $e) {
            // log error and return error response
            // return response()->json(['message' => 'Transaction verification failed', 'error' => $e->getMessage()], 500);
            info('Transaction verification failed: ', [$e->getMessage()]);
            return redirect($redirectUrl);
        }
    }
}
