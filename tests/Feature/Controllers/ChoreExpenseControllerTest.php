<?php

use App\Models\Category;
use App\Models\Chore;
use App\Models\ChoreCompletion;
use App\Models\ChoreExpense;
use App\Models\User;

describe('Chore Expense Controller Tests', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $category = Category::factory()->create();
        $this->chore = Chore::factory()->create(['category_id' => $category->id]);
    });

    it('can complete a chore with expenses', function () {
        $expenses = [
            ['amount' => 25.50, 'description' => 'Cleaning supplies'],
            ['amount' => 12.99, 'description' => 'Paper towels'],
        ];

        $response = $this->post("/chores/{$this->chore->id}/complete", [
            'notes' => 'Chore completed with expenses',
            'expenses' => $expenses,
        ]);

        $response->assertRedirect('/dashboard');
        
        $completion = ChoreCompletion::where('chore_id', $this->chore->id)->first();
        expect($completion)->not->toBeNull();
        expect($completion->notes)->toBe('Chore completed with expenses');

        $expenseRecords = ChoreExpense::where('chore_completion_id', $completion->id)->get();
        expect($expenseRecords)->toHaveCount(2);
        
        expect($expenseRecords[0]->amount)->toBe(2550); // $25.50 in cents
        expect($expenseRecords[0]->description)->toBe('Cleaning supplies');
        
        expect($expenseRecords[1]->amount)->toBe(1299); // $12.99 in cents
        expect($expenseRecords[1]->description)->toBe('Paper towels');
    });

    it('can complete a chore without expenses', function () {
        $response = $this->post("/chores/{$this->chore->id}/complete", [
            'notes' => 'Chore completed without expenses',
        ]);

        $response->assertRedirect('/dashboard');
        
        $completion = ChoreCompletion::where('chore_id', $this->chore->id)->first();
        expect($completion)->not->toBeNull();
        expect($completion->notes)->toBe('Chore completed without expenses');

        $expenseRecords = ChoreExpense::where('chore_completion_id', $completion->id)->get();
        expect($expenseRecords)->toHaveCount(0);
    });

    it('validates expense amount is required when expenses are provided', function () {
        $expenses = [
            ['description' => 'Cleaning supplies'], // Missing amount
        ];

        $response = $this->post("/chores/{$this->chore->id}/complete", [
            'notes' => 'Chore completed',
            'expenses' => $expenses,
        ]);

        $response->assertSessionHasErrors(['expenses.0.amount']);
    });

    it('validates expense description is required when expenses are provided', function () {
        $expenses = [
            ['amount' => 25.50], // Missing description
        ];

        $response = $this->post("/chores/{$this->chore->id}/complete", [
            'notes' => 'Chore completed',
            'expenses' => $expenses,
        ]);

        $response->assertSessionHasErrors(['expenses.0.description']);
    });

    it('validates expense amount must be positive', function () {
        $expenses = [
            ['amount' => -5.00, 'description' => 'Invalid amount'],
        ];

        $response = $this->post("/chores/{$this->chore->id}/complete", [
            'notes' => 'Chore completed',
            'expenses' => $expenses,
        ]);

        $response->assertSessionHasErrors(['expenses.0.amount']);
    });

    it('handles decimal amounts correctly', function () {
        $expenses = [
            ['amount' => 25.99, 'description' => 'Test expense'],
        ];

        $response = $this->post("/chores/{$this->chore->id}/complete", [
            'expenses' => $expenses,
        ]);

        $response->assertRedirect('/dashboard');
        
        $completion = ChoreCompletion::where('chore_id', $this->chore->id)->first();
        $expense = ChoreExpense::where('chore_completion_id', $completion->id)->first();
        
        expect($expense->amount)->toBe(2599); // $25.99 in cents
        expect($expense->amount_in_dollars)->toBe(25.99);
    });
}); 