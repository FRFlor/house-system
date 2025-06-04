<?php

use App\Models\Chore;
use App\Models\ChoreCompletion;
use App\Models\ChoreExpense;
use App\Models\Category;

describe('ChoreExpense Model', function () {
    it('can be created with required fields', function () {
        $category = Category::factory()->create();
        $chore = Chore::factory()->create(['category_id' => $category->id]);
        $completion = ChoreCompletion::factory()->create(['chore_id' => $chore->id]);
        
        $expense = ChoreExpense::create([
            'chore_completion_id' => $completion->id,
            'amount' => 2550, // $25.50 in cents
            'description' => 'Cleaning supplies',
        ]);

        expect($expense)->toBeInstanceOf(ChoreExpense::class);
        expect($expense->amount)->toBe(2550);
        expect($expense->description)->toBe('Cleaning supplies');
        expect($expense->chore_completion_id)->toBe($completion->id);
    });

    it('belongs to a chore completion', function () {
        $category = Category::factory()->create();
        $chore = Chore::factory()->create(['category_id' => $category->id]);
        $completion = ChoreCompletion::factory()->create(['chore_id' => $chore->id]);
        
        $expense = ChoreExpense::factory()->create([
            'chore_completion_id' => $completion->id,
        ]);

        expect($expense->choreCompletion)->toBeInstanceOf(ChoreCompletion::class);
        expect($expense->choreCompletion->id)->toBe($completion->id);
    });

    it('can convert amount to dollars', function () {
        $category = Category::factory()->create();
        $chore = Chore::factory()->create(['category_id' => $category->id]);
        $completion = ChoreCompletion::factory()->create(['chore_id' => $chore->id]);
        
        $expense = ChoreExpense::create([
            'chore_completion_id' => $completion->id,
            'amount' => 2550, // $25.50 in cents
            'description' => 'Cleaning supplies',
        ]);

        expect($expense->amount_in_dollars)->toBe(25.50);
    });

    it('can set amount from dollars', function () {
        $category = Category::factory()->create();
        $chore = Chore::factory()->create(['category_id' => $category->id]);
        $completion = ChoreCompletion::factory()->create(['chore_id' => $chore->id]);
        
        $expense = new ChoreExpense([
            'chore_completion_id' => $completion->id,
            'description' => 'Cleaning supplies',
        ]);

        $expense->amount_in_dollars = 25.50;
        expect($expense->amount)->toBe(2550);
    });

    it('has a chore completion that belongs to a chore', function () {
        $category = Category::factory()->create();
        $chore = Chore::factory()->create(['category_id' => $category->id]);
        $completion = ChoreCompletion::factory()->create(['chore_id' => $chore->id]);
        
        $expense = ChoreExpense::factory()->create([
            'chore_completion_id' => $completion->id,
        ]);

        expect($expense->choreCompletion->chore)->toBeInstanceOf(Chore::class);
        expect($expense->choreCompletion->chore->id)->toBe($chore->id);
    });

    it('can have multiple expenses for the same chore completion', function () {
        $category = Category::factory()->create();
        $chore = Chore::factory()->create(['category_id' => $category->id]);
        $completion = ChoreCompletion::factory()->create(['chore_id' => $chore->id]);
        
        $expense1 = ChoreExpense::factory()->create([
            'chore_completion_id' => $completion->id,
            'amount' => 1000, // $10.00 in cents
            'description' => 'First expense',
        ]);

        $expense2 = ChoreExpense::factory()->create([
            'chore_completion_id' => $completion->id,
            'amount' => 1500, // $15.00 in cents
            'description' => 'Second expense',
        ]);

        expect($completion->expenses)->toHaveCount(2);
        expect($completion->expenses->pluck('amount')->toArray())->toBe([1000, 1500]);
    });
}); 