<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Transaction;
use App\Models\WarehouseOrder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Known issue #4: Paystack calls POST /api/paystack/webhook when a payment succeeds,
 * so payments are confirmed even if the customer closes the browser.
 */
class PaystackWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.frontend_url' => 'https://frontend.test']);
    }

    /**
     * Send a webhook signed the way Paystack signs it (HMAC SHA512 of the raw body).
     */
    private function sendWebhook(array $payload, ?string $signature = null, array $headers = []): TestResponse
    {
        $body = json_encode($payload);
        $signature ??= hash_hmac('sha512', $body, config('services.paystack.secret'));

        return $this->call('POST', '/api/paystack/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_PAYSTACK_SIGNATURE' => $signature,
            ...$headers,
        ], $body);
    }

    private function chargeSuccess(string $reference, float $amountInNaira, string $currency = 'NGN'): array
    {
        return [
            'event' => 'charge.success',
            'data' => [
                'status' => 'success',
                'reference' => $reference,
                'amount' => (int) round($amountInNaira * 100),
                'currency' => $currency,
            ],
        ];
    }

    private function pendingOrder(string $reference, float $total = 8000): array
    {
        $order = Order::factory()->create(['total_price' => $total]);
        $transaction = Transaction::factory()->forOrder($order)->create(['reference' => $reference]);

        return [$order, $transaction];
    }

    public function test_a_validly_signed_charge_success_confirms_the_payment(): void
    {
        [$order, $transaction] = $this->pendingOrder('ref_hook_ok');

        $this->sendWebhook($this->chargeSuccess('ref_hook_ok', 8000))->assertOk();

        $order->refresh();
        $this->assertTrue((bool) $order->is_paid);
        $this->assertSame('confirmed', $order->status);
        $this->assertSame('successful', $transaction->refresh()->status);
    }

    public function test_a_charge_success_confirms_a_warehouse_booking(): void
    {
        $booking = WarehouseOrder::factory()->create(['total_price' => 150000]);
        $transaction = Transaction::factory()->forWarehouseOrder($booking)->create(['reference' => 'ref_hook_wh']);

        $this->sendWebhook($this->chargeSuccess('ref_hook_wh', 150000))->assertOk();

        $this->assertSame('confirmed', $booking->refresh()->status);
        $this->assertSame('successful', $transaction->refresh()->status);
    }

    public function test_the_webhook_is_excluded_from_csrf_protection(): void
    {
        // Laravel skips CSRF checks while testing, so check the exclusion list itself.
        $middleware = $this->app->make(ValidateCsrfToken::class);
        $inExceptArray = new \ReflectionMethod($middleware, 'inExceptArray');

        $this->assertTrue($inExceptArray->invoke($middleware, Request::create('/api/paystack/webhook', 'POST')));
        $this->assertFalse($inExceptArray->invoke($middleware, Request::create('/api/orders', 'POST')));
    }

    public function test_an_invalid_signature_is_rejected_and_changes_nothing(): void
    {
        [$order, $transaction] = $this->pendingOrder('ref_hook_forged');

        $this->sendWebhook($this->chargeSuccess('ref_hook_forged', 8000), 'forged-signature')
            ->assertUnauthorized();

        $this->assertFalse((bool) $order->refresh()->is_paid);
        $this->assertSame('pending', $transaction->refresh()->status);
    }

    public function test_a_missing_signature_is_rejected(): void
    {
        [$order] = $this->pendingOrder('ref_hook_unsigned');
        $body = json_encode($this->chargeSuccess('ref_hook_unsigned', 8000));

        $this->call('POST', '/api/paystack/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], $body)->assertUnauthorized();

        $this->assertFalse((bool) $order->refresh()->is_paid);
    }

    public function test_a_body_signed_with_another_key_is_rejected(): void
    {
        [$order] = $this->pendingOrder('ref_hook_wrong_key');
        $payload = $this->chargeSuccess('ref_hook_wrong_key', 8000);
        $signature = hash_hmac('sha512', json_encode($payload), 'sk_test_someone_elses_key');

        $this->sendWebhook($payload, $signature)->assertUnauthorized();

        $this->assertFalse((bool) $order->refresh()->is_paid);
    }

    public function test_an_underpaid_charge_is_not_confirmed(): void
    {
        [$order, $transaction] = $this->pendingOrder('ref_hook_underpaid');

        $this->sendWebhook($this->chargeSuccess('ref_hook_underpaid', 100))->assertOk();

        $this->assertFalse((bool) $order->refresh()->is_paid);
        $this->assertSame('failed', $transaction->refresh()->status);
    }

    public function test_other_events_are_acknowledged_and_ignored(): void
    {
        [$order, $transaction] = $this->pendingOrder('ref_hook_other');

        $this->sendWebhook([
            'event' => 'transfer.success',
            'data' => ['reference' => 'ref_hook_other', 'amount' => 800000, 'currency' => 'NGN', 'status' => 'success'],
        ])->assertOk();

        $this->assertFalse((bool) $order->refresh()->is_paid);
        $this->assertSame('pending', $transaction->refresh()->status);
    }

    public function test_an_unknown_reference_or_malformed_body_is_acknowledged_without_error(): void
    {
        $this->sendWebhook($this->chargeSuccess('ref_does_not_exist', 8000))->assertOk();
        $this->sendWebhook(['event' => 'charge.success'])->assertOk();
    }

    public function test_a_duplicate_webhook_does_not_confirm_twice(): void
    {
        [$order, $transaction] = $this->pendingOrder('ref_hook_dup');
        $payload = $this->chargeSuccess('ref_hook_dup', 8000);

        $this->sendWebhook($payload)->assertOk();
        $orderAfterFirst = $order->refresh()->getAttributes();
        $transactionAfterFirst = $transaction->refresh()->getAttributes();

        $this->travel(5)->minutes();
        $this->sendWebhook($payload)->assertOk();

        $this->assertSame($orderAfterFirst, $order->refresh()->getAttributes());
        $this->assertSame($transactionAfterFirst, $transaction->refresh()->getAttributes());
    }

    public function test_webhook_then_browser_redirect_confirms_only_once(): void
    {
        [$order, $transaction] = $this->pendingOrder('ref_hook_then_browser');

        $this->sendWebhook($this->chargeSuccess('ref_hook_then_browser', 8000))->assertOk();
        $orderAfterWebhook = $order->refresh()->getAttributes();
        $transactionAfterWebhook = $transaction->refresh()->getAttributes();

        $this->travel(1)->minutes();
        $this->fakePaystackVerify('ref_hook_then_browser', 8000);
        $this->get('/api/transactions/verify?trxref=ref_hook_then_browser&reference=ref_hook_then_browser')
            ->assertRedirect('https://frontend.test/payment/'.$order->id.'?success=true&trxref=ref_hook_then_browser');

        $this->assertSame($orderAfterWebhook, $order->refresh()->getAttributes());
        $this->assertSame($transactionAfterWebhook, $transaction->refresh()->getAttributes());
    }

    public function test_browser_redirect_then_webhook_confirms_only_once(): void
    {
        [$order, $transaction] = $this->pendingOrder('ref_browser_then_hook');

        $this->fakePaystackVerify('ref_browser_then_hook', 8000);
        $this->get('/api/transactions/verify?trxref=ref_browser_then_hook&reference=ref_browser_then_hook')
            ->assertRedirect();
        $orderAfterBrowser = $order->refresh()->getAttributes();
        $transactionAfterBrowser = $transaction->refresh()->getAttributes();

        $this->travel(1)->minutes();
        $this->sendWebhook($this->chargeSuccess('ref_browser_then_hook', 8000))->assertOk();

        $this->assertSame($orderAfterBrowser, $order->refresh()->getAttributes());
        $this->assertSame($transactionAfterBrowser, $transaction->refresh()->getAttributes());
    }
}
