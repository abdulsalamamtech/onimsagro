<?php

namespace Tests\Feature;

use App\Models\ProductReview;
use App\Models\WarehouseReview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Known issue #7: GET /api/product-reviews was registered twice, so it returned warehouse reviews.
 */
class ReviewRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_reviews_returns_approved_product_reviews(): void
    {
        $approved = ProductReview::factory()->create(['review' => 'Great fertiliser']);
        ProductReview::factory()->create(['status' => 'pending']);
        WarehouseReview::factory()->create(['review' => 'Clean warehouse']);

        $response = $this->getJson('/api/product-reviews');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $approved->id)
            ->assertJsonPath('data.0.product_id', $approved->product_id)
            ->assertJsonPath('data.0.review', 'Great fertiliser');
    }

    public function test_warehouse_reviews_returns_approved_warehouse_reviews(): void
    {
        $approved = WarehouseReview::factory()->create(['review' => 'Clean warehouse']);
        WarehouseReview::factory()->create(['status' => 'rejected']);
        ProductReview::factory()->create(['review' => 'Great fertiliser']);

        $response = $this->getJson('/api/warehouse-reviews');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $approved->id)
            ->assertJsonPath('data.0.warehouse_id', $approved->warehouse_id)
            ->assertJsonPath('data.0.review', 'Clean warehouse');
    }
}
