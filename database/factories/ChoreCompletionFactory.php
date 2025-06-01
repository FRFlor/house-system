<?php

namespace Database\Factories;

use App\Models\Chore;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChoreCompletion>
 */
class ChoreCompletionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'chore_id' => Chore::factory(),
            'completed_at' => fake()->dateTimeBetween('-1 year'),
            'notes' => fake()->optional()->sentence(),
        ];
    }
} 