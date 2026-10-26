<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\WarehouseOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Paystack sends the customer back to GET /api/transactions/verify?reference=...
 * after payment. These tests describe what that must do.
 */
class PaymentVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Set explicitly so these tests do not depend on known issue #11.
        config(['app.frontend_url' => 'https://frontend.test']);
    }

    private function verify(string $reference)
    {
        return $this->get('/api/transactions/verify?trxref='.$reference.'&reference='.$reference);
    }

    public function test_successful_payment_marks_a_product_order_paid_and_confirmed(): void
    {
        $order = Order::factory()->create(['total_price' => 8000]);
        $transaction = Transaction::factory()->forOrder($order)->create(['reference' => 'ref_order_ok']);
        $this->fakePaystackVerify('ref_order_ok', 8000);

        $response = $this->verify('ref_order_ok');

        $response->assertRedirect('https://frontend.test/payment/'.$order->id.'?success=true&trxref=ref_order_ok');

        $order->refresh();
        $this->assertTrue((bool) $order->is_paid);
        $this->assertSame('confirmed', $order->status);
        $this->assertSame('successful', $transaction->refresh()->status);
    }

    public function test_failed_payment_leaves_the_order_unpaid(): void
    {
        $order = Order::factory()->create(['total_price' => 8000]);
        $transaction = Transaction::factory()->forOrder($order)->create(['reference' => 'ref_order_failed']);
        $this->fakePaystackVerify('ref_order_failed', 8000, 'failed');

        $response = $this->verify('ref_order_failed');

        $response->assertRedirect('https://frontend.test/payment/ref_order_failed?success=false&trxref=ref_order_failed');

        $order->refresh();
        $this->assertFalse((bool) $order->is_paid);
        $this->assertSame('pending', $order->status);
        $this->assertSame('pending', $transaction->refresh()->status);
    }

    public function test_successful_payment_confirms_a_warehouse_booking(): void
    {
        $this->knownIssue('Known issue #2 in CLAUDE.md', function () {
            $booking = WarehouseOrder::factory()->create(['total_price' => 150000]);
            $transaction = Transaction::factory()->forWarehouseOrder($booking)->create(['reference' => 'ref_wh_ok']);
            $this->fakePaystackVerify('ref_wh_ok', 150000);

            $response = $this->verify('ref_wh_ok');

            $response->assertRedirect();
            $this->assertStringContainsString('success=true', $response->headers->get('Location'));
            $this->assertSame('confirmed', $booking->refresh()->status);
            $this->assertSame('successful', $transaction->refresh()->status);
        });
    }

    public function test_verification_rejects_a_payment_whose_amount_does_not_match_the_order(): void
    {
        $this->knownIssue('Known issue #3 in CLAUDE.md', function () {
            $order = Order::factory()->create(['total_price' => 8000]);
            $transaction = Transaction::factory()->forOrder($order)->create(['reference' => 'ref_underpaid']);
            // Paystack says "success", but the customer only paid ₦1.
            $this->fakePaystackVerify('ref_underpaid', 1);

            $response = $this->verify('ref_underpaid');

            $response->assertRedirect();
            $this->assertStringNotContainsString('success=true', $response->headers->get('Location'));
            $order->refresh();
            $this->assertFalse((bool) $order->is_paid, 'An underpaid order must not be marked paid.');
            $this->assertSame('pending', $order->status);
            $this->assertNotSame('successful', $transaction->refresh()->status);
        });
    }

    public function test_verifying_the_same_reference_twice_changes_nothing_the_second_time(): void
    {
        $this->knownIssue('New issue, not yet in the CLAUDE.md list (verification re-processes an already-successful payment)', function () {
            $order = Order::factory()->create(['total_price' => 8000]);
            $transaction = Transaction::factory()->forOrder($order)->create(['reference' => 'ref_twice']);
            $this->fakePaystackVerify('ref_twice', 8000);

            $this->verify('ref_twice')->assertRedirect();
            $orderAfterFirst = $order->refresh()->getAttributes();
            $transactionAfterFirst = $transaction->refresh()->getAttributes();

            $this->travel(5)->minutes();
            $this->verify('ref_twice')->assertRedirect();

            $this->assertSame($orderAfterFirst, $order->refresh()->getAttributes());
            $this->assertSame($transactionAfterFirst, $transaction->refresh()->getAttributes());
        });
    }

    public function test_verifying_again_does_not_undo_progress_made_by_an_admin(): void
    {
        $this->knownIssue('New issue, not yet in the CLAUDE.md list (verification re-processes an already-successful payment)', function () {
            $order = Order::factory()->create(['total_price' => 8000]);
            Transaction::factory()->forOrder($order)->create(['reference' => 'ref_shipped']);
            $this->fakePaystackVerify('ref_shipped', 8000);

            $this->verify('ref_shipped')->assertRedirect();

            // An admin ships the order, then the customer reloads the payment return page.
            $order->refresh()->update(['status' => 'shipped']);
            $this->verify('ref_shipped')->assertRedirect();

            $this->assertSame('shipped', $order->refresh()->status);
        });
    }

    public function test_a_paid_order_reduces_stock_exactly_once_even_if_verified_twice(): void
    {
        $this->knownIssue('Known issue #5 in CLAUDE.md', function () {
            $product = Product::factory()->create(['price' => 2000, 'stock' => 10]);
            $order = Order::factory()->create(['total_price' => 6000]);
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => 3,
                'unit_price' => 2000,
                'total_price' => 6000,
            ]);
            Transaction::factory()->forOrder($order)->create(['reference' => 'ref_stock']);
            $this->fakePaystackVerify('ref_stock', 6000);

            $this->verify('ref_stock')->assertRedirect();
            $this->verify('ref_stock')->assertRedirect();

            $this->assertSame(7, (int) $product->refresh()->stock);
        });
    }
}
