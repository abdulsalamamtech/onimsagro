<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCatalogueTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_list_active_products(): void
    {
        Product::factory()->count(2)->create();
        Product::factory()->inactive()->create();

        $response = $this->getJson('/api/products');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data');
    }

    public function test_guest_can_view_one_product(): void
    {
        $product = Product::factory()->create(['name' => 'Hybrid Maize Seeds 10kg']);

        $response = $this->getJson('/api/products/'.$product->id);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonPath('data.name', 'Hybrid Maize Seeds 10kg');
    }

    public function test_viewing_a_missing_product_returns_404(): void
    {
        $this->getJson('/api/products/999999')->assertNotFound();
    }
}
