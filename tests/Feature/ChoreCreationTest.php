<?php

use App\Models\Category;
use App\Models\Chore;
use App\Models\User;
use Carbon\Carbon;

it('can view the create chore page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/chores/create');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Chores/Create'));
});

it('can create a new chore with required fields', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $choreData = [
        'name' => 'Test Chore',
        'description' => 'A test chore description',
        'category_name' => $category->name,
        'frequency_type' => 'weeks',
        'frequency_value' => 2,
        'next_due_at' => '2024-01-15',
    ];

    $response = $this->actingAs($user)->post('/chores', $choreData);

    $response->assertRedirect('/dashboard');
    $this->assertDatabaseHas('chores', [
        'name' => 'Test Chore',
        'description' => 'A test chore description',
        'category_id' => $category->id,
        'frequency_type' => 'weeks',
        'frequency_value' => 2,
        'next_due_at' => '2024-01-15 00:00:00',
    ]);
});

it('validates required fields when creating a chore', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/chores', []);

    $response->assertSessionHasErrors(['name', 'frequency_type', 'frequency_value', 'next_due_at']);
});

it('validates frequency_type is valid when creating a chore', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $choreData = [
        'name' => 'Test Chore',
        'category_name' => $category->name,
        'frequency_type' => 'invalid_frequency',
        'frequency_value' => 1,
        'next_due_at' => '2024-01-15',
    ];

    $response = $this->actingAs($user)->post('/chores', $choreData);

    $response->assertSessionHasErrors(['frequency_type']);
    $response->assertSessionHasErrorsIn('default', [
        'frequency_type' => 'The selected frequency type is invalid.'
    ]);
});

it('can create a ONE_OFF chore', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $choreData = [
        'name' => 'One Time Task',
        'category_name' => $category->name,
        'frequency_type' => 'one_off',
        'frequency_value' => 1,
        'next_due_at' => '2024-01-15',
    ];

    $response = $this->actingAs($user)->post('/chores', $choreData);

    $response->assertRedirect('/dashboard');
    $this->assertDatabaseHas('chores', [
        'name' => 'One Time Task',
        'frequency_type' => 'one_off',
        'frequency_value' => 1,
        'next_due_at' => '2024-01-15 00:00:00',
    ]);
});

it('provides categories for chore creation form', function () {
    $user = User::factory()->create();
    $category1 = Category::factory()->create(['name' => 'Kitchen']);
    $category2 = Category::factory()->create(['name' => 'Bathroom']);

    $response = $this->actingAs($user)->get('/chores/create');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) =>
        $page->has('categories')
             ->where('categories', fn ($categories) =>
                 collect($categories)->pluck('id')->contains($category1->id) &&
                 collect($categories)->pluck('id')->contains($category2->id)
             )
    );
});

it('can create a chore with instruction file path', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $choreData = [
        'name' => 'Complex Task',
        'description' => 'A complex task with instructions',
        'category_name' => $category->name,
        'frequency_type' => 'months',
        'frequency_value' => 3,
        'next_due_at' => '2024-02-01',
        'instruction_file_path' => '/instructions/complex-task.pdf',
    ];

    $response = $this->actingAs($user)->post('/chores', $choreData);

    $response->assertRedirect('/dashboard');
    $this->assertDatabaseHas('chores', [
        'name' => 'Complex Task',
        'instruction_file_path' => '/instructions/complex-task.pdf',
    ]);
});

it('can create a chore with a new category name', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/chores', [
        'name' => 'Test Chore',
        'description' => 'Test Description',
        'category_name' => 'New Category',
        'frequency_type' => 'weeks',
        'frequency_value' => 1,
        'next_due_at' => '2024-01-15',
    ]);

    $response->assertRedirect('/dashboard');

    // Check that the category was created
    $this->assertDatabaseHas('categories', [
        'name' => 'New Category',
    ]);

    // Check that the chore was created with the new category
    $category = Category::where('name', 'New Category')->first();
    $this->assertDatabaseHas('chores', [
        'name' => 'Test Chore',
        'category_id' => $category->id,
    ]);
});

it('can create a chore with an existing category name', function () {
    $user = User::factory()->create();
    $existingCategory = Category::factory()->create(['name' => 'Existing Category']);

    $response = $this->actingAs($user)->post('/chores', [
        'name' => 'Test Chore',
        'description' => 'Test Description',
        'category_name' => 'Existing Category',
        'frequency_type' => 'weeks',
        'frequency_value' => 1,
        'next_due_at' => '2024-01-15',
    ]);

    $response->assertRedirect('/dashboard');

    // Check that no new category was created
    $this->assertEquals(1, Category::where('name', 'Existing Category')->count());

    // Check that the chore was created with the existing category
    $this->assertDatabaseHas('chores', [
        'name' => 'Test Chore',
        'category_id' => $existingCategory->id,
    ]);
});
