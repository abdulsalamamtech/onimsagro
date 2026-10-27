<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\WarehouseOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Confirms a Paystack payment and the product order or warehouse booking it pays for.
 *
 * Shared by every place that learns about a payment (the customer's redirect back
 * from Paystack today, a Paystack webhook later), so it is safe to call more than
 * once for the same reference: only the first successful call changes anything.
 */
class PaymentConfirmationService
{
    private const CURRENCY = 'NGN';

    /**
     * @param  string  $reference  the Paystack transaction reference
     * @param  array  $paystackData  the verified `data` object from Paystack (amount is in kobo)
     */
    public function confirm(string $reference, array $paystackData): PaymentConfirmationResult
    {
        try {
            DB::beginTransaction();

            // Lock the row so two simultaneous confirmations cannot both process it.
            $transaction = Transaction::where('reference', $reference)->lockForUpdate()->first();

            if (! $transaction) {
                DB::rollBack();

                return new PaymentConfirmationResult(PaymentConfirmationResult::NOT_FOUND);
            }

            if ($transaction->status === 'successful') {
                DB::rollBack();

                return new PaymentConfirmationResult(PaymentConfirmationResult::ALREADY_CONFIRMED, $transaction);
            }

            if (($paystackData['status'] ?? null) !== 'success') {
                DB::rollBack();

                return new PaymentConfirmationResult(PaymentConfirmationResult::NOT_SUCCESSFUL, $transaction);
            }

            if (! $this->amountAndCurrencyMatch($transaction, $paystackData)) {
                $transaction->status = 'failed';
                $transaction->save();
                DB::commit();

                Log::warning('Paystack payment does not match the expected amount or currency', [
                    'reference' => $reference,
                    'transaction_id' => $transaction->id,
                    'expected_kobo' => $this->expectedKobo($transaction),
                    'paid_kobo' => $paystackData['amount'] ?? null,
                    'paid_currency' => $paystackData['currency'] ?? null,
                ]);

                return new PaymentConfirmationResult(PaymentConfirmationResult::MISMATCH, $transaction);
            }

            $transaction->status = 'successful';
            $transaction->save();

            if ($transaction->payment_type === 'warehouse_order' && $transaction->warehouse_order_id) {
                $this->confirmWarehouseOrder($transaction->warehouse_order_id);
            } elseif ($transaction->order_id) {
                $this->confirmOrder($transaction->order_id);
            }

            DB::commit();

            return new PaymentConfirmationResult(PaymentConfirmationResult::CONFIRMED, $transaction);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    private function amountAndCurrencyMatch(Transaction $transaction, array $paystackData): bool
    {
        $paidKobo = $paystackData['amount'] ?? null;
        $currency = strtoupper((string) ($paystackData['currency'] ?? ''));

        return is_numeric($paidKobo)
            && (int) $paidKobo === $this->expectedKobo($transaction)
            && $currency === self::CURRENCY;
    }

    private function expectedKobo(Transaction $transaction): int
    {
        return (int) round((float) $transaction->amount * 100);
    }

    private function confirmOrder(int $orderId): void
    {
        $order = Order::lockForUpdate()->findOrFail($orderId);

        // Reduce stock only the first time this order becomes paid, even if it
        // has more than one successful transaction.
        if (! $order->is_paid) {
            $this->takeStockFor($order);
        }

        $order->is_paid = true;
        // Only move a fresh order forward; never undo progress made by an admin.
        if ($order->status === 'pending') {
            $order->status = 'confirmed';
        }
        $order->save();
    }

    /**
     * Reduce each product's stock by the quantity ordered, never below zero.
     * If there is not enough stock the payment still stands, but the order is
     * flagged for an admin to resolve (refund, restock or partial delivery).
     */
    private function takeStockFor(Order $order): void
    {
        $quantities = $order->orderItems()
            ->selectRaw('product_id, SUM(quantity) as quantity')
            ->groupBy('product_id')
            ->pluck('quantity', 'product_id');

        // Lock products in id order so simultaneous payments cannot deadlock.
        $products = Product::withTrashed()
            ->whereIn('id', $quantities->keys())
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $shortages = [];
        foreach ($products as $product) {
            $wanted = (int) $quantities[$product->id];
            $available = max(0, (int) $product->stock);
            $taken = min($wanted, $available);

            if ($taken < $wanted) {
                $shortages[] = "{$product->name} (product #{$product->id}): ordered {$wanted}, "
                    ."only {$available} in stock, short by ".($wanted - $taken);
            }

            $product->stock = $available - $taken;
            $product->save();
        }

        if ($shortages) {
            $order->needs_attention = true;
            $order->attention_note = 'Paid but not enough stock: '.implode('; ', $shortages).'.';

            Log::warning('Paid order exceeds available stock; flagged for admin', [
                'order_id' => $order->id,
                'shortages' => $shortages,
            ]);
        }
    }

    private function confirmWarehouseOrder(int $warehouseOrderId): void
    {
        $warehouseOrder = WarehouseOrder::lockForUpdate()->findOrFail($warehouseOrderId);
        if ($warehouseOrder->status === 'pending') {
            $warehouseOrder->status = 'confirmed';
        }
        $warehouseOrder->save();
    }
}
