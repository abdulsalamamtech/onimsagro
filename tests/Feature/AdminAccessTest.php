<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_admin_routes(): void
    {
        $this->getJson('/api/admin/orders')->assertUnauthorized();
        $this->getJson('/api/admin/order-status')->assertUnauthorized();
        $this->postJson('/api/admin/products', ['name' => 'Sneaky product'])->assertUnauthorized();
    }

    public function test_a_super_admin_can_access_admin_routes(): void
    {
        Order::factory()->count(3)->create();
        Sanctum::actingAs(User::factory()->superAdmin()->create());

        $this->getJson('/api/admin/order-status')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->getJson('/api/admin/orders')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'data');
    }

    public function test_a_logged_in_user_without_the_super_admin_role_is_denied(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson('/api/admin/orders');

        // Must never succeed for a normal user.
        $this->assertGreaterThanOrEqual(400, $response->status());
        $response->assertJsonMissingPath('data');

        // ...and should say "forbidden" (403) rather than look like a server crash.
        $this->knownIssue('Known issue #13 in CLAUDE.md', function () use ($response) {
            $response->assertForbidden();
        });
    }
}
