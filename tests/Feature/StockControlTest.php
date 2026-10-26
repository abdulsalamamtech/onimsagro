<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\WarehouseOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Known issue #5: stock is checked when an order is placed and reduced once when it is paid.
 */
class StockControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.frontend_url' => 'https://frontend.test']);
    }

    private function placeOrder(array $items)
    {
        return $this->postJson('/api/orders', [
            'full_name' => 'Ada Farmer',
            'email' => 'ada@example.com',
            'phone_number' => '08012345678',
            'address' => '12 Farm Road, Abuja',
            'order_items' => $items,
        ]);
    }

    /**
     * A pending order (with its Paystack transaction) for the given [product, quantity] lines.
     */
    private function pendingOrder(string $reference, array $lines): Order
    {
        $total = 0;
        foreach ($lines as [$product, $quantity]) {
            $total += $product->price * $quantity;
        }
        $order = Order::factory()->create(['total_price' => $total]);
        foreach ($lines as [$product, $quantity]) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $product->price,
                'total_price' => $product->price * $quantity,
            ]);
        }
        Transaction::factory()->forOrder($order)->create(['reference' => $reference]);

        return $order;
    }

    private function pay(string $reference, float $amount)
    {
        $this->fakePaystackVerify($reference, $amount);

        return $this->get('/api/transactions/verify?trxref='.$reference.'&reference='.$reference);
    }

    // --- Placing an order ---------------------------------------------------

    public function test_an_order_for_more_than_the_available_stock_is_rejected(): void
    {
        $this->fakePaystackInitialize();
        $product = Product::factory()->create(['name' => 'NPK Fertiliser 50kg', 'price' => 2500, 'stock' => 4]);

        $response = $this->placeOrder([['product_id' => $product->id, 'quantity' => 5]]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['order_items.0.quantity' => 'Only 4 of NPK Fertiliser 50kg left in stock.']);
        $this->assertSame(0, Order::count());
        Http::assertNothingSent();
    }

    public function test_an_out_of_stock_product_cannot_be_ordered(): void
    {
        $this->fakePaystackInitialize();
        $product = Product::factory()->create(['name' => 'Hybrid Maize Seeds', 'stock' => 0]);

        $this->placeOrder([['product_id' => $product->id, 'quantity' => 1]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['order_items.0.quantity' => 'Hybrid Maize Seeds is out of stock.']);
        $this->assertSame(0, Order::count());
    }

    public function test_repeated_lines_for_the_same_product_are_added_up(): void
    {
        $this->fakePaystackInitialize();
        $product = Product::factory()->create(['stock' => 5]);

        $this->placeOrder([
            ['product_id' => $product->id, 'quantity' => 3],
            ['product_id' => $product->id, 'quantity' => 3],
        ])->assertUnprocessable();

        $this->assertSame(0, Order::count());
    }

    public function test_an_order_for_exactly_the_available_stock_is_accepted(): void
    {
        $this->fakePaystackInitialize();
        $product = Product::factory()->create(['price' => 2500, 'stock' => 5]);

        $this->placeOrder([['product_id' => $product->id, 'quantity' => 5]])->assertCreated();

        // Placing an order does not reduce stock; only payment does.
        $this->assertSame(5, (int) $product->refresh()->stock);
    }

    // --- Paying for an order ------------------------------------------------

    public function test_payment_reduces_stock_for_every_item_in_the_order(): void
    {
        $fertiliser = Product::factory()->create(['price' => 2500, 'stock' => 10]);
        $seeds = Product::factory()->create(['price' => 1000, 'stock' => 20]);
        $order = $this->pendingOrder('ref_stock_multi', [[$fertiliser, 2], [$seeds, 3]]);

        $this->pay('ref_stock_multi', 8000)->assertRedirect();

        $this->assertSame(8, (int) $fertiliser->refresh()->stock);
        $this->assertSame(17, (int) $seeds->refresh()->stock);
        $this->assertFalse((bool) $order->refresh()->needs_attention);
    }

    public function test_stock_never_goes_below_zero_and_the_order_is_flagged_for_admin(): void
    {
        Log::spy();
        $product = Product::factory()->create(['name' => 'Knapsack Sprayer', 'price' => 2000, 'stock' => 3]);
        $order = $this->pendingOrder('ref_oversold', [[$product, 3]]);

        // Someone else bought 2 between this order being placed and paid for.
        $product->update(['stock' => 1]);

        $this->pay('ref_oversold', 6000)->assertRedirect(
            'https://frontend.test/payment/'.$order->id.'?success=true&trxref=ref_oversold'
        );

        $this->assertSame(0, (int) $product->refresh()->stock);
        $order->refresh();
        $this->assertTrue((bool) $order->is_paid, 'The customer paid, so the order is still confirmed.');
        $this->assertSame('confirmed', $order->status);
        $this->assertTrue((bool) $order->needs_attention);
        $this->assertStringContainsString('Knapsack Sprayer', $order->attention_note);
        $this->assertStringContainsString('short by 2', $order->attention_note);
        Log::shouldHaveReceived('warning')->withArgs(fn ($message) => str_contains($message, 'stock'))->once();
    }

    public function test_stock_is_reduced_only_once_even_if_a_second_transaction_is_paid(): void
    {
        $product = Product::factory()->create(['price' => 2000, 'stock' => 10]);
        $order = $this->pendingOrder('ref_first_attempt', [[$product, 3]]);
        // A second payment attempt for the same order (e.g. the customer retried).
        Transaction::factory()->forOrder($order)->create(['reference' => 'ref_second_attempt']);

        $this->pay('ref_first_attempt', 6000)->assertRedirect();
        $this->pay('ref_second_attempt', 6000)->assertRedirect();

        $this->assertSame(7, (int) $product->refresh()->stock);
    }

    public function test_a_rejected_payment_does_not_reduce_stock(): void
    {
        $product = Product::factory()->create(['price' => 2000, 'stock' => 10]);
        $this->pendingOrder('ref_underpaid_stock', [[$product, 3]]);

        $this->pay('ref_underpaid_stock', 100)->assertRedirect();

        $this->assertSame(10, (int) $product->refresh()->stock);
    }

    public function test_paying_for_a_warehouse_booking_does_not_touch_product_stock(): void
    {
        $product = Product::factory()->create(['stock' => 10]);
        $booking = WarehouseOrder::factory()->create(['total_price' => 150000]);
        Transaction::factory()->forWarehouseOrder($booking)->create(['reference' => 'ref_wh_stock']);

        $this->pay('ref_wh_stock', 150000)->assertRedirect();

        $this->assertSame('confirmed', $booking->refresh()->status);
        $this->assertSame(10, (int) $product->refresh()->stock);
    }
}
