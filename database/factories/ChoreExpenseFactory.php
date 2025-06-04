<?php

namespace Database\Factories;

use App\Models\ChoreCompletion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChoreExpense>
 */
class ChoreExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'chore_completion_id' => ChoreCompletion::factory(),
            'amount' => fake()->numberBetween(100, 10000), // $1.00 to $100.00 in cents
            'description' => fake()->sentence(3),
        ];
    }
}
