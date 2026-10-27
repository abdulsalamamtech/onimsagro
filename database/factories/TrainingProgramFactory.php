<?php

namespace Database\Factories;

use App\Models\FarmingInterest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TrainingProgram>
 */
class TrainingProgramFactory extends Factory
{
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'phone_number' => '080'.fake()->numerify('########'),
            'email' => fake()->safeEmail(),
            'farming_interest_id' => FarmingInterest::factory(),
        ];
    }
}
