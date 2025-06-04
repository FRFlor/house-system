<?php

use App\Models\Category;
use App\Models\Chore;
use App\Models\ChoreCompletion;
use App\Models\ChoreExpense;
use App\Models\User;
use Carbon\Carbon;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
    
    $this->category = Category::factory()->create();
    $this->chore = Chore::factory()->create(['category_id' => $this->category->id]);
    $this->completion = ChoreCompletion::factory()->create([
        'chore_id' => $this->chore->id,
        'notes' => 'Original notes',
        'completed_at' => Carbon::parse('2023-12-15 14:30:00'),
    ]);
});

it('can update a chore completion notes', function () {
    $response = $this->put("/completions/{$this->completion->id}", [
        'notes' => 'Updated notes',
    ]);

    $response->assertRedirect();
    
    $this->completion->refresh();
    expect($this->completion->notes)->toBe('Updated notes');
});

it('can update a chore completion completed_at timestamp', function () {
    $newCompletedAt = '2023-12-16 16:45:00';
    
    $response = $this->put("/completions/{$this->completion->id}", [
        'notes' => 'Updated notes',
        'completed_at' => $newCompletedAt,
    ]);

    $response->assertRedirect();
    
    $this->completion->refresh();
    expect($this->completion->completed_at->format('Y-m-d H:i:s'))->toBe($newCompletedAt);
});

it('can update a chore completion with all fields including completed_at', function () {
    $newCompletedAt = '2023-12-17 10:15:00';
    $expenses = [
        ['description' => 'Cleaning supplies', 'amount' => 25.50],
    ];

    $response = $this->put("/completions/{$this->completion->id}", [
        'notes' => 'Updated with new timestamp',
        'completed_at' => $newCompletedAt,
        'expenses' => $expenses,
    ]);

    $response->assertRedirect();
    
    $this->completion->refresh();
    expect($this->completion->notes)->toBe('Updated with new timestamp');
    expect($this->completion->completed_at->format('Y-m-d H:i:s'))->toBe($newCompletedAt);
    
    $expenseRecords = ChoreExpense::where('chore_completion_id', $this->completion->id)->get();
    expect($expenseRecords)->toHaveCount(1);
    expect($expenseRecords->first()->description)->toBe('Cleaning supplies');
});

it('validates completed_at format when updating completion', function () {
    $response = $this->put("/completions/{$this->completion->id}", [
        'notes' => 'Updated notes',
        'completed_at' => 'invalid-date-format',
    ]);

    $response->assertSessionHasErrors(['completed_at']);
});

it('can update a chore completion with expenses', function () {
    $expenses = [
        ['description' => 'Cleaning supplies', 'amount' => 25.50],
        ['description' => 'Tools', 'amount' => 15.00],
    ];

    $response = $this->put("/completions/{$this->completion->id}", [
        'notes' => 'Updated with expenses',
        'expenses' => $expenses,
    ]);

    $response->assertRedirect();
    
    $this->completion->refresh();
    expect($this->completion->notes)->toBe('Updated with expenses');
    
    $expenseRecords = ChoreExpense::where('chore_completion_id', $this->completion->id)->get();
    expect($expenseRecords)->toHaveCount(2);
    expect($expenseRecords->first()->amount)->toBe(2550); // $25.50 in cents
    expect($expenseRecords->first()->description)->toBe('Cleaning supplies');
    expect($expenseRecords->last()->amount)->toBe(1500); // $15.00 in cents
    expect($expenseRecords->last()->description)->toBe('Tools');
});

it('can update a chore completion and replace existing expenses', function () {
    // Create existing expense
    ChoreExpense::factory()->create([
        'chore_completion_id' => $this->completion->id,
        'amount' => 1000,
        'description' => 'Old expense',
    ]);

    $newExpenses = [
        ['description' => 'New expense', 'amount' => 30.00],
    ];

    $response = $this->put("/completions/{$this->completion->id}", [
        'notes' => 'Updated',
        'expenses' => $newExpenses,
    ]);

    $response->assertRedirect();
    
    $expenseRecords = ChoreExpense::where('chore_completion_id', $this->completion->id)->get();
    expect($expenseRecords)->toHaveCount(1);
    expect($expenseRecords->first()->description)->toBe('New expense');
    expect($expenseRecords->first()->amount)->toBe(3000); // $30.00 in cents
});

it('can clear all expenses when updating completion', function () {
    // Create existing expense
    ChoreExpense::factory()->create([
        'chore_completion_id' => $this->completion->id,
        'amount' => 1000,
        'description' => 'Old expense',
    ]);

    $response = $this->put("/completions/{$this->completion->id}", [
        'notes' => 'Updated without expenses',
        'expenses' => [],
    ]);

    $response->assertRedirect();
    
    $expenseRecords = ChoreExpense::where('chore_completion_id', $this->completion->id)->get();
    expect($expenseRecords)->toHaveCount(0);
});

it('validates required fields when updating completion', function () {
    $response = $this->put("/completions/{$this->completion->id}", [
        // No data provided
    ]);

    $response->assertSessionHasNoErrors(); // Notes are optional
});

it('validates expense fields when updating completion', function () {
    $expenses = [
        ['description' => ''], // Empty description
        ['amount' => -5.00, 'description' => 'Invalid amount'], // Negative amount
    ];

    $response = $this->put("/completions/{$this->completion->id}", [
        'notes' => 'Updated',
        'expenses' => $expenses,
    ]);

    $response->assertSessionHasErrors(['expenses.0.description', 'expenses.1.amount']);
});

it('returns 404 for non-existent completion', function () {
    $response = $this->put('/completions/999', [
        'notes' => 'Updated',
    ]);

    $response->assertNotFound();
}); 