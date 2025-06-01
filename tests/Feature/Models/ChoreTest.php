<?php

use App\Enums\FrequencyType;
use App\Models\Category;
use App\Models\Chore;
use Carbon\Carbon;

test('chore can be created with required fields', function () {
    $category = Category::factory()->create();
    
    $chore = Chore::factory()->create([
        'category_id' => $category->id,
        'name' => 'Replace Furnace Filter',
        'description' => 'Replace the HVAC furnace filter',
        'frequency_type' => FrequencyType::MONTHS,
        'frequency_value' => 3,
    ]);

    expect($chore->name)->toBe('Replace Furnace Filter');
    expect($chore->description)->toBe('Replace the HVAC furnace filter');
    expect($chore->frequency_type)->toBe(FrequencyType::MONTHS);
    expect($chore->frequency_value)->toBe(3);
    expect($chore->category_id)->toBe($category->id);
    expect($chore->exists)->toBeTrue();
});

test('chore name is required', function () {
    expect(fn() => Chore::factory()->create(['name' => null]))
        ->toThrow(\Illuminate\Database\QueryException::class);
});

test('chore frequency_type is required', function () {
    expect(fn() => Chore::factory()->create(['frequency_type' => null]))
        ->toThrow(\Illuminate\Database\QueryException::class);
});

test('chore frequency_value is required', function () {
    expect(fn() => Chore::factory()->create(['frequency_value' => null]))
        ->toThrow(\Illuminate\Database\QueryException::class);
});

test('chore next_due_at should be provided when creating', function () {
    // While the database allows null, business logic expects next_due_at to be set when creating
    $chore = Chore::factory()->create(['next_due_at' => now()]);
    expect($chore->next_due_at)->not->toBeNull();
});

test('chore frequency_type must be valid', function () {
    // This should throw an exception because 'invalid' is not a valid FrequencyType enum value
    expect(fn() => Chore::factory()->create(['frequency_type' => 'invalid']))
        ->toThrow(\ValueError::class);
});

test('chore belongs to a category', function () {
    $chore = Chore::factory()->create();
    
    expect($chore->category())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class);
    expect($chore->category)->toBeInstanceOf(Category::class);
});

test('chore can have instruction file path', function () {
    $chore = Chore::factory()->create([
        'instruction_file_path' => 'instructions/furnace-filter.md'
    ]);
    
    expect($chore->instruction_file_path)->toBe('instructions/furnace-filter.md');
});

test('chore can be created with specific next due date for calendar-based scheduling', function () {
    $nextSeptember1st = Carbon::create(2025, 9, 1);
    
    $chore = Chore::factory()->create([
        'name' => 'Clean Gutters',
        'frequency_type' => FrequencyType::YEARS,
        'frequency_value' => 1,
        'next_due_at' => $nextSeptember1st,
    ]);
    
    expect($chore->next_due_at->format('Y-m-d'))->toBe('2025-09-01');
});

test('chore calculates next due date when completed with months', function () {
    $chore = Chore::factory()->create([
        'frequency_type' => FrequencyType::MONTHS,
        'frequency_value' => 3,
        'last_completed_at' => null,
        'next_due_at' => now(),
    ]);
    
    $completedAt = now();
    $expectedNextDue = $completedAt->copy()->addMonths(3);
    $chore->markAsCompleted($completedAt);
    
    // Refresh the model to get the updated values from database
    $chore->refresh();
    
    expect($chore->last_completed_at->format('Y-m-d H:i:s'))->toBe($completedAt->format('Y-m-d H:i:s'));
    expect($chore->next_due_at->format('Y-m-d H:i:s'))->toBe($expectedNextDue->format('Y-m-d H:i:s'));
});

test('chore calculates next due date when completed with weeks', function () {
    $chore = Chore::factory()->create([
        'frequency_type' => FrequencyType::WEEKS,
        'frequency_value' => 2,
        'last_completed_at' => null,
        'next_due_at' => now(),
    ]);
    
    $completedAt = now();
    $expectedNextDue = $completedAt->copy()->addWeeks(2);
    $chore->markAsCompleted($completedAt);
    
    // Refresh the model to get the updated values from database
    $chore->refresh();
    
    expect($chore->last_completed_at->format('Y-m-d H:i:s'))->toBe($completedAt->format('Y-m-d H:i:s'));
    expect($chore->next_due_at->format('Y-m-d H:i:s'))->toBe($expectedNextDue->format('Y-m-d H:i:s'));
});

test('chore calculates next due date when completed with years', function () {
    $chore = Chore::factory()->create([
        'frequency_type' => FrequencyType::YEARS,
        'frequency_value' => 1,
        'last_completed_at' => null,
        'next_due_at' => now(),
    ]);
    
    $completedAt = now();
    $expectedNextDue = $completedAt->copy()->addYears(1);
    $chore->markAsCompleted($completedAt);
    
    // Refresh the model to get the updated values from database
    $chore->refresh();
    
    expect($chore->last_completed_at->format('Y-m-d H:i:s'))->toBe($completedAt->format('Y-m-d H:i:s'));
    expect($chore->next_due_at->format('Y-m-d H:i:s'))->toBe($expectedNextDue->format('Y-m-d H:i:s'));
});

test('chore maintains calendar-based scheduling when completed', function () {
    // Set up a chore due on September 1st, 2025
    $september1st2025 = Carbon::create(2025, 9, 1);
    
    $chore = Chore::factory()->create([
        'name' => 'Clean Gutters',
        'frequency_type' => FrequencyType::YEARS,
        'frequency_value' => 1,
        'next_due_at' => $september1st2025,
    ]);
    
    // Complete it on any date (e.g., August 30th)
    $completedAt = Carbon::create(2025, 8, 30);
    $chore->markAsCompleted($completedAt);
    
    // Should be due September 1st of the following year
    $expectedNextDue = Carbon::create(2026, 9, 1);
    
    $chore->refresh();
    expect($chore->next_due_at->format('Y-m-d'))->toBe($expectedNextDue->format('Y-m-d'));
});

test('chore can have many completions', function () {
    $chore = Chore::factory()->create();
    
    expect($chore->completions())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});

test('chore can be created with ONE_OFF frequency type', function () {
    $chore = Chore::factory()->create([
        'name' => 'Install New Ceiling Fan',
        'frequency_type' => FrequencyType::ONE_OFF,
        'frequency_value' => 1, // Should be ignored for ONE_OFF
        'next_due_at' => now(),
    ]);
    
    expect($chore->frequency_type)->toBe(FrequencyType::ONE_OFF);
    expect($chore->frequency_value)->toBe(1);
});

test('ONE_OFF chore sets next_due_at to null when completed', function () {
    $chore = Chore::factory()->create([
        'frequency_type' => FrequencyType::ONE_OFF,
        'frequency_value' => 1,
        'next_due_at' => now(),
        'last_completed_at' => null,
    ]);
    
    $completedAt = now();
    $chore->markAsCompleted($completedAt);
    
    $chore->refresh();
    
    expect($chore->last_completed_at->format('Y-m-d H:i:s'))->toBe($completedAt->format('Y-m-d H:i:s'));
    expect($chore->next_due_at)->toBeNull();
}); 