<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Warehouse>
 */
class WarehouseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company().' Warehouse',
            'description' => fake()->sentence(),
            'capacity' => fake()->numberBetween(10, 500).' tonnes',
            'sku' => 'WH-'.Str::upper(Str::random(10)),
            'price' => fake()->randomFloat(2, 10000, 500000),
            'location' => fake()->city(),
            'status' => 'active',
        ];
    }
}
