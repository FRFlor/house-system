<?php

namespace Database\Seeders;

use App\Enums\FrequencyType;
use App\Models\Category;
use App\Models\Chore;
use App\Models\ChoreCompletion;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ChoreSeeder extends Seeder
{
    public function run(): void
    {
        // Create categories
        $categories = [
            ['name' => 'Kitchen', 'color' => '#3B82F6'],
            ['name' => 'Bathroom', 'color' => '#10B981'],
            ['name' => 'Living Room', 'color' => '#F59E0B'],
            ['name' => 'Bedroom', 'color' => '#8B5CF6'],
            ['name' => 'Outdoor', 'color' => '#EF4444'],
            ['name' => 'Maintenance', 'color' => '#6B7280'],
        ];

        foreach ($categories as $categoryData) {
            Category::firstOrCreate(['name' => $categoryData['name']], $categoryData);
        }

        $kitchenCategory = Category::where('name', 'Kitchen')->first();
        $bathroomCategory = Category::where('name', 'Bathroom')->first();
        $livingRoomCategory = Category::where('name', 'Living Room')->first();
        $bedroomCategory = Category::where('name', 'Bedroom')->first();
        $outdoorCategory = Category::where('name', 'Outdoor')->first();
        $maintenanceCategory = Category::where('name', 'Maintenance')->first();

        // Create sample chores with different due dates
        $chores = [
            // Overdue chores
            [
                'name' => 'Clean Refrigerator',
                'category_id' => $kitchenCategory->id,
                'frequency_type' => FrequencyType::MONTHS,
                'frequency_value' => 3,
                'next_due_at' => Carbon::yesterday()->subDays(2),
            ],
            [
                'name' => 'Deep Clean Shower',
                'category_id' => $bathroomCategory->id,
                'frequency_type' => FrequencyType::WEEKS,
                'frequency_value' => 2,
                'next_due_at' => Carbon::yesterday(),
            ],

            // Due today
            [
                'name' => 'Vacuum Living Room',
                'category_id' => $livingRoomCategory->id,
                'frequency_type' => FrequencyType::WEEKS,
                'frequency_value' => 1,
                'next_due_at' => Carbon::today(),
            ],
            [
                'name' => 'Change Bed Sheets',
                'category_id' => $bedroomCategory->id,
                'frequency_type' => FrequencyType::WEEKS,
                'frequency_value' => 2,
                'next_due_at' => Carbon::today(),
            ],

            // Upcoming chores
            [
                'name' => 'Mow Lawn',
                'category_id' => $outdoorCategory->id,
                'frequency_type' => FrequencyType::WEEKS,
                'frequency_value' => 1,
                'next_due_at' => Carbon::tomorrow(),
            ],
            [
                'name' => 'Clean Windows',
                'category_id' => $livingRoomCategory->id,
                'frequency_type' => FrequencyType::MONTHS,
                'frequency_value' => 2,
                'next_due_at' => Carbon::today()->addDays(3),
            ],
            [
                'name' => 'Replace Air Filter',
                'category_id' => $maintenanceCategory->id,
                'frequency_type' => FrequencyType::MONTHS,
                'frequency_value' => 3,
                'next_due_at' => Carbon::today()->addDays(5),
            ],

            // One-off chores
            [
                'name' => 'Install New Ceiling Fan',
                'category_id' => $bedroomCategory->id,
                'frequency_type' => FrequencyType::ONE_OFF,
                'frequency_value' => 1,
                'next_due_at' => Carbon::today()->addDays(2),
            ],
            [
                'name' => 'Fix Leaky Faucet',
                'category_id' => $bathroomCategory->id,
                'frequency_type' => FrequencyType::ONE_OFF,
                'frequency_value' => 1,
                'next_due_at' => Carbon::today(),
            ],

            // Completed one-off (should not appear in active lists)
            [
                'name' => 'Paint Guest Room',
                'category_id' => $bedroomCategory->id,
                'frequency_type' => FrequencyType::ONE_OFF,
                'frequency_value' => 1,
                'next_due_at' => null,
                'last_completed_at' => Carbon::yesterday(),
            ],
        ];

        foreach ($chores as $choreData) {
            $chore = Chore::firstOrCreate(
                ['name' => $choreData['name']],
                $choreData
            );

            // Create some recent completions for demonstration
            if (in_array($chore->name, ['Vacuum Living Room', 'Clean Refrigerator', 'Mow Lawn'])) {
                ChoreCompletion::firstOrCreate([
                    'chore_id' => $chore->id,
                    'completed_at' => Carbon::now()->subDays(rand(1, 6)),
                ], [
                    'notes' => 'Completed as scheduled',
                ]);
            }
        }

        // Create a few more recent completions
        $dishesChore = Chore::firstOrCreate([
            'name' => 'Wash Dishes',
            'category_id' => $kitchenCategory->id,
            'frequency_type' => FrequencyType::WEEKS,
            'frequency_value' => 1,
            'next_due_at' => Carbon::today()->addDays(7),
        ]);

        ChoreCompletion::firstOrCreate([
            'chore_id' => $dishesChore->id,
            'completed_at' => Carbon::now()->subHours(3),
        ], [
            'notes' => 'Quick wash after dinner',
        ]);

        ChoreCompletion::firstOrCreate([
            'chore_id' => $dishesChore->id,
            'completed_at' => Carbon::now()->subDays(2),
        ], [
            'notes' => 'Weekly deep clean',
        ]);
    }
} 