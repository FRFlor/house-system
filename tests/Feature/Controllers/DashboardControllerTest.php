<?php

use App\Enums\FrequencyType;
use App\Http\Controllers\DashboardController;
use App\Models\Category;
use App\Models\Chore;
use App\Models\ChoreCompletion;
use App\Models\User;
use Carbon\Carbon;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('dashboard returns inertia response with correct component', function () {
    $response = $this->get(route('dashboard'));
    
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Dashboard'));
});

test('dashboard provides overdue chores', function () {
    $category = Category::factory()->create();
    
    // Create an overdue chore (due yesterday)
    $overdueChore = Chore::factory()->create([
        'name' => 'Overdue Task',
        'category_id' => $category->id,
        'next_due_at' => Carbon::yesterday(),
    ]);
    
    // Create a future chore (should not appear in overdue)
    Chore::factory()->create([
        'name' => 'Future Task',
        'category_id' => $category->id,
        'next_due_at' => Carbon::tomorrow(),
    ]);
    
    $response = $this->get(route('dashboard'));
    
    $response->assertInertia(fn ($page) => 
        $page->has('overdue_chores', 1)
             ->where('overdue_chores.0.name', 'Overdue Task')
    );
});

test('dashboard provides chores due today', function () {
    $category = Category::factory()->create();
    
    // Create a chore due today
    $todayChore = Chore::factory()->create([
        'name' => 'Today Task',
        'category_id' => $category->id,
        'next_due_at' => Carbon::today(),
    ]);
    
    // Create a chore due tomorrow (should not appear in today)
    Chore::factory()->create([
        'name' => 'Tomorrow Task',
        'category_id' => $category->id,
        'next_due_at' => Carbon::tomorrow(),
    ]);
    
    $response = $this->get(route('dashboard'));
    
    $response->assertInertia(fn ($page) => 
        $page->has('due_today', 1)
             ->where('due_today.0.name', 'Today Task')
    );
});

test('dashboard provides upcoming chores', function () {
    $category = Category::factory()->create();
    
    // Create upcoming chores (next 7 days, excluding today)
    $upcomingChore = Chore::factory()->create([
        'name' => 'Upcoming Task',
        'category_id' => $category->id,
        'next_due_at' => Carbon::today()->addDays(3),
    ]);
    
    // Create a chore too far in future (should not appear in upcoming)
    Chore::factory()->create([
        'name' => 'Far Future Task',
        'category_id' => $category->id,
        'next_due_at' => Carbon::today()->addDays(10),
    ]);
    
    $response = $this->get(route('dashboard'));
    
    $response->assertInertia(fn ($page) => 
        $page->has('upcoming_chores', 1)
             ->where('upcoming_chores.0.name', 'Upcoming Task')
    );
});

test('dashboard provides recent completions', function () {
    $category = Category::factory()->create();
    $chore = Chore::factory()->create(['category_id' => $category->id]);
    
    // Create a recent completion
    $recentCompletion = ChoreCompletion::factory()->create([
        'chore_id' => $chore->id,
        'completed_at' => Carbon::now()->subHours(2),
        'notes' => 'Completed successfully',
    ]);
    
    // Create an old completion (should not appear in recent)
    ChoreCompletion::factory()->create([
        'chore_id' => $chore->id,
        'completed_at' => Carbon::now()->subDays(10),
    ]);
    
    $response = $this->get(route('dashboard'));
    
    $response->assertInertia(fn ($page) => 
        $page->has('recent_completions', 1)
             ->where('recent_completions.0.notes', 'Completed successfully')
    );
});

test('dashboard excludes completed ONE_OFF chores from active lists', function () {
    $category = Category::factory()->create();
    
    // Create a completed ONE_OFF chore (should not appear anywhere)
    $completedOneOff = Chore::factory()->create([
        'name' => 'Completed One-Off',
        'category_id' => $category->id,
        'frequency_type' => FrequencyType::ONE_OFF,
        'next_due_at' => null, // Completed ONE_OFF chores have null next_due_at
        'last_completed_at' => Carbon::yesterday(),
    ]);
    
    // Create an active ONE_OFF chore (should appear in due today)
    $activeOneOff = Chore::factory()->create([
        'name' => 'Active One-Off',
        'category_id' => $category->id,
        'frequency_type' => FrequencyType::ONE_OFF,
        'next_due_at' => Carbon::today(),
        'last_completed_at' => null,
    ]);
    
    $response = $this->get(route('dashboard'));
    
    $response->assertInertia(fn ($page) => 
        $page->has('due_today', 1)
             ->where('due_today.0.name', 'Active One-Off')
             ->has('overdue_chores', 0)
             ->has('upcoming_chores', 0)
    );
});

test('dashboard provides summary statistics', function () {
    $category = Category::factory()->create();
    
    // Create various chores for statistics
    Chore::factory()->create(['category_id' => $category->id, 'next_due_at' => Carbon::yesterday()]); // Overdue
    Chore::factory()->create(['category_id' => $category->id, 'next_due_at' => Carbon::today()]); // Due today
    Chore::factory()->create(['category_id' => $category->id, 'next_due_at' => Carbon::tomorrow()]); // Upcoming
    Chore::factory()->create(['category_id' => $category->id, 'next_due_at' => null]); // Completed ONE_OFF
    
    $response = $this->get(route('dashboard'));
    
    $response->assertInertia(fn ($page) => 
        $page->where('stats.total_active_chores', 3)
             ->where('stats.overdue_count', 1)
             ->where('stats.due_today_count', 1)
             ->where('stats.upcoming_count', 1)
    );
}); 