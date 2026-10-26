<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_cannot_read_a_customers_details_by_order_number(): void
    {
        $this->knownIssue('Known issue #14 in CLAUDE.md', function () {
            $order = Order::factory()->create([
                'is_paid' => true,
                'status' => 'confirmed',
                'email' => 'customer@example.com',
                'phone_number' => '08031112222',
                'address' => '7 Private Close, Kaduna',
            ]);
            Transaction::factory()->forOrder($order)->create(['status' => 'successful']);

            $response = $this->getJson('/api/orders/'.$order->id.'/verify');

            $response->assertDontSee('customer@example.com');
            $response->assertDontSee('08031112222');
            $response->assertDontSee('7 Private Close');
        });
    }
}
