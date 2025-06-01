<?php

use App\Enums\FrequencyType;
use App\Models\Category;
use App\Models\Chore;
use App\Models\ChoreCompletion;
use App\Models\User;
use Carbon\Carbon;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('chore can be marked as completed', function () {
    $category = Category::factory()->create();
    $chore = Chore::factory()->create([
        'category_id' => $category->id,
        'frequency_type' => FrequencyType::MONTHS,
        'frequency_value' => 3,
        'next_due_at' => Carbon::today(),
        'last_completed_at' => null,
    ]);

    $response = $this->post(route('chores.complete', $chore), [
        'notes' => 'Completed successfully',
    ]);

    $response->assertRedirect();
    
    $chore->refresh();
    expect($chore->last_completed_at)->not->toBeNull();
    expect($chore->next_due_at)->toBeGreaterThan(Carbon::today());
    
    // Check that a completion record was created
    expect($chore->completions()->count())->toBe(1);
    expect($chore->completions()->first()->notes)->toBe('Completed successfully');
});

test('chore completion creates completion record with timestamp', function () {
    $chore = Chore::factory()->create([
        'frequency_type' => FrequencyType::WEEKS,
        'frequency_value' => 2,
        'next_due_at' => Carbon::today(),
    ]);

    $completedAt = Carbon::now();
    
    $response = $this->post(route('chores.complete', $chore), [
        'notes' => 'Test completion',
        'completed_at' => $completedAt->toISOString(),
    ]);

    $response->assertRedirect();
    
    $completion = $chore->completions()->first();
    expect($completion)->not->toBeNull();
    expect($completion->notes)->toBe('Test completion');
    expect($completion->completed_at->format('Y-m-d H:i'))->toBe($completedAt->format('Y-m-d H:i'));
});

test('ONE_OFF chore becomes inactive when completed', function () {
    $chore = Chore::factory()->create([
        'frequency_type' => FrequencyType::ONE_OFF,
        'frequency_value' => 1,
        'next_due_at' => Carbon::today(),
        'last_completed_at' => null,
    ]);

    $response = $this->post(route('chores.complete', $chore), [
        'notes' => 'One-time task completed',
    ]);

    $response->assertRedirect();
    
    $chore->refresh();
    expect($chore->last_completed_at)->not->toBeNull();
    expect($chore->next_due_at)->toBeNull(); // ONE_OFF chores become null
    
    // Check completion record was still created
    expect($chore->completions()->count())->toBe(1);
});

test('chore completion validates required fields', function () {
    $chore = Chore::factory()->create();

    $response = $this->post(route('chores.complete', $chore), []);

    // Should succeed even without notes (notes are optional)
    $response->assertRedirect();
    
    $chore->refresh();
    expect($chore->last_completed_at)->not->toBeNull();
});

test('chore completion handles custom completion date', function () {
    $chore = Chore::factory()->create([
        'frequency_type' => FrequencyType::MONTHS,
        'frequency_value' => 1,
        'next_due_at' => Carbon::create(2025, 3, 15), // March 15th
    ]);

    $customDate = Carbon::create(2025, 3, 10); // Completed 5 days early
    
    $response = $this->post(route('chores.complete', $chore), [
        'completed_at' => $customDate->toISOString(),
        'notes' => 'Completed early',
    ]);

    $response->assertRedirect();
    
    $chore->refresh();
    
    // For calendar-based scheduling, next due should still be April 15th
    // (preserving the day of month)
    expect($chore->next_due_at->format('Y-m-d'))->toBe('2025-04-15');
    expect($chore->last_completed_at->format('Y-m-d'))->toBe('2025-03-10');
});

test('chore completion redirects back to dashboard', function () {
    $chore = Chore::factory()->create();

    $response = $this->post(route('chores.complete', $chore));

    $response->assertRedirect(route('dashboard'));
});

test('chore completion can include success message', function () {
    $chore = Chore::factory()->create(['name' => 'Test Chore']);

    $response = $this->post(route('chores.complete', $chore), [
        'notes' => 'All done!',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');
}); 