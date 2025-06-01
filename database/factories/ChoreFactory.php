<?php

namespace Database\Factories;

use App\Enums\FrequencyType;
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
        $frequencyType = fake()->randomElement(FrequencyType::cases());
        $frequencyValue = match ($frequencyType) {
            FrequencyType::WEEKS => fake()->numberBetween(1, 4),
            FrequencyType::MONTHS => fake()->numberBetween(1, 12),
            FrequencyType::YEARS => fake()->numberBetween(1, 5),
            FrequencyType::ONE_OFF => 1, // Always 1 for one-off tasks
        };

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
            'frequency_type' => $frequencyType,
            'frequency_value' => $frequencyValue,
            'instruction_file_path' => fake()->optional()->filePath(),
            'last_completed_at' => fake()->optional()->dateTimeBetween('-1 year'),
            'next_due_at' => fake()->dateTimeBetween('now', '+1 year'),
        ];
    }
} 