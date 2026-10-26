<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TechnicalSupport>
 */
class TechnicalSupportFactory extends Factory
{
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'phone_number' => '080'.fake()->numerify('########'),
            'email' => fake()->safeEmail(),
            'stage_of_plant' => 'flowering',
            'problem_with_crop' => 'Leaves turning yellow',
            'status' => 'pending',
        ];
    }
}
