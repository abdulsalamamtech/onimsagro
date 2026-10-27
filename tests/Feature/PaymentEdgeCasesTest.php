<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Payment situations that must never mark an order as paid.
 */
class PaymentEdgeCasesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.frontend_url' => 'https://frontend.test']);
    }

    private function pendingOrder(string $reference): array
    {
        $order = Order::factory()->create(['total_price' => 8000]);
        $transaction = Transaction::factory()->forOrder($order)->create(['reference' => $reference]);

        return [$order, $transaction];
    }

    public function test_verify_without_a_reference_goes_to_the_error_page(): void
    {
        $this->get('/api/transactions/verify')->assertRedirect('https://frontend.test/payment/error');
    }

    public function test_an_unknown_reference_changes_nothing(): void
    {
        [$order] = $this->pendingOrder('ref_known');
        $this->fakePaystackVerify('ref_unknown', 8000);

        $this->get('/api/transactions/verify?reference=ref_unknown')->assertRedirect();

        $this->assertFalse((bool) $order->refresh()->is_paid);
        $this->assertSame(0, Transaction::where('status', 'successful')->count());
    }

    public function test_paystack_returning_an_error_leaves_the_order_unpaid(): void
    {
        [$order, $transaction] = $this->pendingOrder('ref_paystack_500');
        Http::fake(['api.paystack.co/*' => Http::response(['status' => false, 'message' => 'Server error'], 500)]);

        $this->get('/api/transactions/verify?reference=ref_paystack_500')
            ->assertRedirect('https://frontend.test/payment/ref_paystack_500?success=false&trxref=ref_paystack_500');

        $this->assertFalse((bool) $order->refresh()->is_paid);
        $this->assertSame('pending', $transaction->refresh()->status);
    }

    public function test_paystack_being_unreachable_leaves_the_order_unpaid(): void
    {
        [$order, $transaction] = $this->pendingOrder('ref_paystack_down');
        Http::fake(fn () => throw new ConnectionException('Could not resolve host'));

        $this->get('/api/transactions/verify?reference=ref_paystack_down')->assertRedirect();

        $this->assertFalse((bool) $order->refresh()->is_paid);
        $this->assertSame('pending', $transaction->refresh()->status);
    }

    public function test_a_failed_payment_start_leaves_no_open_database_transaction(): void
    {
        $this->knownIssue('Known issue #16 in CLAUDE.md', function () {
            Http::fake(['api.paystack.co/*' => Http::response(['status' => false, 'message' => 'Down'], 500)]);
            $product = Product::factory()->create(['price' => 1000]);
            $levelBefore = DB::transactionLevel();

            try {
                $this->postJson('/api/orders', [
                    'full_name' => 'Ada Farmer',
                    'email' => 'ada@example.com',
                    'phone_number' => '08012345678',
                    'address' => '12 Farm Road, Abuja',
                    'order_items' => [['product_id' => $product->id, 'quantity' => 1]],
                ])->assertServerError();

                $this->assertSame($levelBefore, DB::transactionLevel(), 'The order code left a database transaction open.');
                $this->assertSame(0, Order::count(), 'No order should be left behind when payment cannot start.');
            } finally {
                // Close anything the bug left open so later tests start clean.
                while (DB::transactionLevel() > $levelBefore) {
                    DB::rollBack();
                }
            }
        });
    }
}
