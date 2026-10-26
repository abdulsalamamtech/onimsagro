<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OrderCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_an_order_uses_database_prices_and_ignores_client_prices(): void
    {
        $this->fakePaystackInitialize('ref_checkout_1');

        $fertiliser = Product::factory()->create(['price' => 2500]);
        $seeds = Product::factory()->create(['price' => 1000]);

        // A tampered request: the client claims everything costs ₦1.
        $response = $this->postJson('/api/orders', [
            'full_name' => 'Ada Farmer',
            'email' => 'ada@example.com',
            'phone_number' => '08012345678',
            'address' => '12 Farm Road, Abuja',
            'total_price' => 1,
            'order_items' => [
                ['product_id' => $fertiliser->id, 'quantity' => 2, 'unit_price' => 1, 'total_price' => 1],
                ['product_id' => $seeds->id, 'quantity' => 3, 'unit_price' => 1, 'total_price' => 1],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.payment_link', 'https://checkout.paystack.com/ref_checkout_1');

        // 2 × ₦2,500 + 3 × ₦1,000 = ₦8,000
        $order = Order::sole();
        $this->assertEquals(8000, (float) $order->total_price);
        $this->assertSame('pending', $order->status);
        $this->assertFalse((bool) $order->is_paid);

        $items = $order->orderItems()->orderBy('id')->get();
        $this->assertCount(2, $items);
        $this->assertEquals(2500, (float) $items[0]->unit_price);
        $this->assertEquals(5000, (float) $items[0]->total_price);
        $this->assertEquals(1000, (float) $items[1]->unit_price);
        $this->assertEquals(3000, (float) $items[1]->total_price);

        // Paystack is asked to charge ₦8,000, which is 800,000 kobo.
        Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/transaction/initialize')
            && (int) $request['amount'] === 800000
            && $request['email'] === 'ada@example.com');

        $transaction = Transaction::sole();
        $this->assertSame('ref_checkout_1', $transaction->reference);
        $this->assertSame('pending', $transaction->status);
        $this->assertSame('order', $transaction->payment_type);
        $this->assertSame($order->id, $transaction->order_id);
        $this->assertEquals(8000, (float) $transaction->amount);
    }
}
