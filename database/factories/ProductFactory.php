<?php

namespace Database\Factories;

use App\Models\ProductCategory;
use App\Models\ProductType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_type_id' => ProductType::factory(),
            'product_category_id' => ProductCategory::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'sku' => 'SKU-'.Str::upper(Str::random(10)),
            'price' => fake()->randomFloat(2, 500, 50000),
            'stock' => 100,
            'location' => fake()->city(),
            'estimated_delivery' => 2,
            'status' => 'active',
        ];
    }

    /**
     * Indicate that the product is hidden from the public catalogue.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
