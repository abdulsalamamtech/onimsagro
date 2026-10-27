<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\WarehouseOrder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Defaults to a pending Paystack transaction for a product order.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'amount' => fake()->randomFloat(2, 1000, 100000),
            'status' => 'pending',
            'reference' => Str::lower(Str::random(12)),
            'payment_method' => 'online',
            'payment_provider' => 'paystack',
        ];
    }

    /**
     * A pending transaction for the given product order, for the order's total.
     */
    public function forOrder(Order $order): static
    {
        return $this->state(fn (array $attributes) => [
            'order_id' => $order->id,
            'warehouse_order_id' => null,
            'payment_type' => 'order',
            'full_name' => $order->full_name,
            'email' => $order->email,
            'amount' => $order->total_price,
        ]);
    }

    /**
     * A pending transaction for the given warehouse booking, for the booking's total.
     */
    public function forWarehouseOrder(WarehouseOrder $warehouseOrder): static
    {
        return $this->state(fn (array $attributes) => [
            'order_id' => null,
            'warehouse_order_id' => $warehouseOrder->id,
            'payment_type' => 'warehouse_order',
            'full_name' => $warehouseOrder->full_name,
            'email' => $warehouseOrder->email,
            'amount' => $warehouseOrder->total_price,
        ]);
    }
}
