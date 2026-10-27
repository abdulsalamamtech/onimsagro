<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Known issue #11: after payment the API redirects to config('app.frontend_url'),
 * which must be set from FRONTEND_URL (phpunit.xml sets it to https://frontend.test).
 */
class FrontendUrlConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_payment_redirects_to_the_configured_frontend_url(): void
    {
        $order = Order::factory()->create(['total_price' => 8000]);
        Transaction::factory()->forOrder($order)->create(['reference' => 'ref_frontend']);
        $this->fakePaystackVerify('ref_frontend', 8000);

        $response = $this->get('/api/transactions/verify?trxref=ref_frontend&reference=ref_frontend');

        $response->assertRedirect('https://frontend.test/payment/'.$order->id.'?success=true&trxref=ref_frontend');
    }

    public function test_verify_without_a_reference_redirects_to_the_frontend_error_page(): void
    {
        $this->get('/api/transactions/verify')
            ->assertRedirect('https://frontend.test/payment/error');
    }

    public function test_frontend_url_defaults_to_the_live_website_when_not_set(): void
    {
        $original = getenv('FRONTEND_URL');
        putenv('FRONTEND_URL');
        unset($_ENV['FRONTEND_URL'], $_SERVER['FRONTEND_URL']);

        try {
            $appConfig = require config_path('app.php');
        } finally {
            putenv('FRONTEND_URL='.$original);
            $_ENV['FRONTEND_URL'] = $_SERVER['FRONTEND_URL'] = $original;
        }

        $this->assertSame('https://onimsagro.com', $appConfig['frontend_url'] ?? null);
    }
}
