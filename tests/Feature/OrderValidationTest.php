<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OrderValidationTest extends TestCase
{
    use RefreshDatabase;

    private function validOrder(array $overrides = []): array
    {
        return array_merge([
            'full_name' => 'Ada Farmer',
            'email' => 'ada@example.com',
            'phone_number' => '08012345678',
            'address' => '12 Farm Road, Abuja',
            'order_items' => [['product_id' => Product::factory()->create()->id, 'quantity' => 1]],
        ], $overrides);
    }

    /**
     * @return array<string, array{0: array, 1: string}>
     */
    public static function invalidOrders(): array
    {
        return [
            'no items' => [['order_items' => []], 'order_items'],
            'unknown product' => [['order_items' => [['product_id' => 999999, 'quantity' => 1]]], 'order_items.0.product_id'],
            'zero quantity' => [['order_items' => null, '_quantity' => 0], 'order_items.0.quantity'],
            'negative quantity' => [['order_items' => null, '_quantity' => -2], 'order_items.0.quantity'],
            'bad email' => [['email' => 'not-an-email'], 'email'],
            'missing name' => [['full_name' => ''], 'full_name'],
            'missing phone' => [['phone_number' => ''], 'phone_number'],
        ];
    }

    /**
     * @dataProvider invalidOrders
     */
    public function test_invalid_orders_are_rejected_before_payment(array $overrides, string $field): void
    {
        $this->fakePaystackInitialize();
        $data = $this->validOrder();
        if (array_key_exists('_quantity', $overrides)) {
            $data['order_items'][0]['quantity'] = $overrides['_quantity'];
            unset($overrides['_quantity'], $overrides['order_items']);
        }

        $this->postJson('/api/orders', array_merge($data, $overrides))
            ->assertUnprocessable()
            ->assertJsonValidationErrors([$field]);

        $this->assertSame(0, Order::count());
        Http::assertNothingSent();
    }
}
