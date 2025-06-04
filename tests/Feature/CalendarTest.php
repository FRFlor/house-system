<?php

use App\Models\Category;
use App\Models\Chore;
use App\Models\ChoreCompletion;
use App\Models\ChoreExpense;
use App\Models\User;
use Carbon\Carbon;
use Inertia\Testing\AssertableInertia;

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('May 15th 2021 at 3PM'));
});

it('can view the calendar page', function () {
    $response = $this->actingAs(User::factory()->create())->get('/calendar');

    $response->assertStatus(200);
    $response->assertInertia(fn($page) => $page->component('Calendar'));
});

it('displays chores for the current month', function () {
    $category = Category::factory()->create();

    // Create chores for different dates in current month
    $choreToday = Chore::factory()->create([
        'category_id' => $category->id,
        'next_due_at' => Carbon::today(),
    ]);

    $choreTomorrow = Chore::factory()->create([
        'category_id' => $category->id,
        'next_due_at' => Carbon::tomorrow(),
    ]);

    $choreLastWeek = Chore::factory()->create([
        'category_id' => $category->id,
        'next_due_at' => Carbon::today()->subWeek(),
    ]);

    $this
        ->actingAs(User::factory()->create())
        ->get('/calendar')
        ->assertSuccessful()
        ->assertInertia(function (AssertableInertia $page) use ($choreToday, $choreTomorrow, $choreLastWeek) {
            return $page->has('chores')
                ->where('chores', fn($chores) => $chores->pluck('id')->contains($choreToday->id))
                ->where('chores', fn($chores) => $chores->pluck('id')->contains($choreTomorrow->id))
                ->where('chores', fn($chores) => $chores->pluck('id')->contains($choreLastWeek->id));
        }
        );
});

it('displays completed chores with their completion data', function () {
    $category = Category::factory()->create();
    $chore = Chore::factory()->create([
        'category_id' => $category->id,
        'next_due_at' => Carbon::yesterday(),
    ]);

    // Complete the chore
    $completion = ChoreCompletion::factory()->create([
        'chore_id' => $chore->id,
        'completed_at' => Carbon::yesterday(),
        'notes' => 'Test completion notes',
    ]);

    $this
        ->actingAs(User::factory()->create())
        ->get('/calendar')
        ->assertSuccessful()
        ->assertInertia(fn(AssertableInertia $page) => $page
            ->has('completions')
            ->where('completions', fn($completions) => $completions
                ->pluck('id')->contains($completion->id)
            )
        );
});

it('displays completed chores with their expense data for editing', function () {
    $category = Category::factory()->create();
    $chore = Chore::factory()->create([
        'category_id' => $category->id,
        'next_due_at' => Carbon::yesterday(),
    ]);

    // Complete the chore
    $completion = ChoreCompletion::factory()->create([
        'chore_id' => $chore->id,
        'completed_at' => Carbon::yesterday(),
        'notes' => 'Test completion notes',
    ]);

    // Add expense to the completion
    $expense = ChoreExpense::factory()->create([
        'chore_completion_id' => $completion->id,
        'amount' => 2550, // $25.50 in cents
        'description' => 'Cleaning supplies',
    ]);

    $this
        ->actingAs(User::factory()->create())
        ->get('/calendar')
        ->assertSuccessful()
        ->assertInertia(function (AssertableInertia $page) use ($completion) {
            return $page
                ->has('completions')
                ->where('completions', function ($completions) use ($completion) {
                    $targetCompletion = collect($completions)->first(fn($c) => $c['id'] === $completion->id);
                    return $targetCompletion && 
                           isset($targetCompletion['expenses']) && 
                           count($targetCompletion['expenses']) === 1 &&
                           $targetCompletion['expenses'][0]['amount'] === 25.50 &&
                           $targetCompletion['expenses'][0]['description'] === 'Cleaning supplies';
                });
        });
});

it('can filter calendar by specific month and year', function () {
    $category = Category::factory()->create();

    // Create chore for next month
    $nextMonthChore = Chore::factory()->create([
        'category_id' => $category->id,
        'next_due_at' => Carbon::now()->addMonth(),
    ]);

    // Create chore for current month
    $currentMonthChore = Chore::factory()->create([
        'category_id' => $category->id,
        'next_due_at' => Carbon::now(),
    ]);

    $nextMonth = Carbon::now()->addMonth();
    $response = $this->actingAs(User::factory()->create())->get("/calendar?month={$nextMonth->month}&year={$nextMonth->year}");

    $response->assertStatus(200);
    $response->assertInertia(fn($page) => $page->where('chores', fn($chores) => collect($chores)->contains('id', $nextMonthChore->id) &&
        !collect($chores)->contains('id', $currentMonthChore->id)
    )
    );
});

it('calendar navigation is accessible from authenticated routes', function () {
    $user = User::factory()->create();
    
    // Test that calendar link is accessible for authenticated users
    $response = $this->actingAs($user)->get('/calendar');
    $response->assertStatus(200);
    $response->assertInertia(fn($page) => $page->component('Calendar'));
});
