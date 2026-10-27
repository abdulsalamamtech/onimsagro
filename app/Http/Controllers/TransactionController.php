<?php

namespace App\Http\Controllers;

use App\Helpers\Paystack;
use App\Models\Transaction;
use App\Services\PaymentConfirmationResult;
use App\Services\PaymentConfirmationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

    /**
     * [public] Paystack redirects the customer here after payment.
     * Verifies the payment with Paystack, confirms the order or warehouse booking,
     * then sends the customer back to the website.
     */
    public function verifyTransaction(Request $request, PaymentConfirmationService $payments)
    {
        // e.g. /api/transactions/verify?trxref=oo5ihug1qm&reference=oo5ihug1qm
        $frontendUrl = config('app.frontend_url');
        $errorUrl = $frontendUrl.'/payment/error';

        try {
            $reference = $request->input('reference') ?? $request->input('trxref');
            if (! $reference) {
                return redirect($errorUrl);
            }

            $failedUrl = $frontendUrl.'/payment/'.$reference.'?success=false&trxref='.$reference;

            $PSP = Paystack::verify($reference);
            info('verify payment message: ', [$PSP['message']]);
            if (! $PSP['success']) {
                return redirect($failedUrl);
            }

            $result = $payments->confirm($reference, $PSP['data']);
            if ($result->status === PaymentConfirmationResult::NOT_FOUND) {
                return redirect($errorUrl);
            }
            if (! $result->isPaid()) {
                return redirect($failedUrl);
            }

            $transaction = $result->transaction;
            if ($transaction->payment_type === 'warehouse_order') {
                return redirect($frontendUrl.'/payment/'.$transaction->warehouse_order_id
                    .'?success=true&trxref='.$reference.'&type=warehouse_order');
            }

            return redirect($frontendUrl.'/payment/'.$transaction->order_id.'?success=true&trxref='.$reference);
        } catch (\Throwable $e) {
            Log::error('Transaction verification failed', ['error' => $e->getMessage()]);

            return redirect($errorUrl);
        }
    }
}
