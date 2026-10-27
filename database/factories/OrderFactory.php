<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone_number' => '080'.fake()->numerify('########'),
            'address' => fake()->address(),
            'total_price' => fake()->randomFloat(2, 1000, 100000),
            'status' => 'pending',
            'is_paid' => false,
        ];
    }
}
