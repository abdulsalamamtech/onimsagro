<?php

namespace Database\Factories;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WarehouseOrder>
 */
class WarehouseOrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'warehouse_id' => Warehouse::factory(),
            'full_name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone_number' => '080'.fake()->numerify('########'),
            'address' => fake()->address(),
            'total_price' => fake()->randomFloat(2, 10000, 500000),
            'status' => 'pending',
        ];
    }
}
