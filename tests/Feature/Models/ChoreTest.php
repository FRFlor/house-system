<?php

use App\Enums\FrequencyType;
use App\Models\Category;
use App\Models\Chore;

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

test('chore calculates next due date when completed with months', function () {
    $chore = Chore::factory()->create([
        'frequency_type' => FrequencyType::MONTHS,
        'frequency_value' => 3,
        'last_completed_at' => null,
        'next_due_at' => null,
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
        'next_due_at' => null,
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
        'next_due_at' => null,
    ]);
    
    $completedAt = now();
    $expectedNextDue = $completedAt->copy()->addYears(1);
    $chore->markAsCompleted($completedAt);
    
    // Refresh the model to get the updated values from database
    $chore->refresh();
    
    expect($chore->last_completed_at->format('Y-m-d H:i:s'))->toBe($completedAt->format('Y-m-d H:i:s'));
    expect($chore->next_due_at->format('Y-m-d H:i:s'))->toBe($expectedNextDue->format('Y-m-d H:i:s'));
});

test('chore can have many completions', function () {
    $chore = Chore::factory()->create();
    
    expect($chore->completions())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
}); 