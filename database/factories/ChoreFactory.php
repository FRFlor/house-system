<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Chore>
 */
class ChoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'name' => fake()->randomElement([
                'Replace Furnace Filter',
                'Clean Gutters',
                'Check Smoke Detectors',
                'Service HVAC System',
                'Clean Dryer Vent',
                'Test Water Heater',
                'Inspect Roof',
                'Clean Windows',
            ]),
            'description' => fake()->sentence(),
            'frequency_months' => fake()->randomElement([1, 3, 6, 12]),
            'instruction_file_path' => fake()->optional()->filePath(),
            'last_completed_at' => fake()->optional()->dateTimeBetween('-1 year'),
            'next_due_at' => fake()->optional()->dateTimeBetween('now', '+1 year'),
        ];
    }
} 