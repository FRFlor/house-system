<?php

use App\Models\Category;
use App\Models\Chore;
use App\Models\User;

it('can view the edit chore page', function () {
    $category = Category::factory()->create();
    $chore = Chore::factory()->create(['category_id' => $category->id]);

    $response = $this->actingAs(User::factory()->create())->get("/chores/{$chore->id}/edit");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) =>
    $page->component('Chores/Edit')
        ->has('chore')
        ->has('categories')
        ->where('chore.id', $chore->id)
    );
});

it('can update a chore', function () {
    $category = Category::factory()->create();
    $newCategory = Category::factory()->create(['name' => 'Updated Category']);
    $chore = Chore::factory()->create(['category_id' => $category->id]);

    $updateData = [
        'name' => 'Updated Chore Name',
        'description' => 'Updated description',
        'category_name' => $newCategory->name,
        'frequency_type' => 'months',
        'frequency_value' => 3,
        'next_due_at' => '2024-03-15',
        'instruction_file_path' => '/updated/path.pdf',
    ];

    $response = $this->actingAs(User::factory()->create())->put("/chores/{$chore->id}", $updateData);

    $response->assertRedirect('/chores');
    $this->assertDatabaseHas('chores', [
        'id' => $chore->id,
        'name' => 'Updated Chore Name',
        'description' => 'Updated description',
        'category_id' => $newCategory->id,
        'frequency_type' => 'months',
        'frequency_value' => 3,
        'instruction_file_path' => '/updated/path.pdf',
    ]);
});

it('can update a chore with a new category name', function () {
    $category = Category::factory()->create();
    $chore = Chore::factory()->create(['category_id' => $category->id]);

    $updateData = [
        'name' => 'Updated Chore',
        'category_name' => 'Brand New Category',
        'frequency_type' => 'weeks',
        'frequency_value' => 2,
        'next_due_at' => '2024-02-15',
    ];

    $response = $this->actingAs(User::factory()->create())->put("/chores/{$chore->id}", $updateData);

    $response->assertRedirect('/chores');

    // Check that the new category was created
    $this->assertDatabaseHas('categories', [
        'name' => 'Brand New Category',
    ]);

    // Check that the chore was updated with the new category
    $newCategory = Category::where('name', 'Brand New Category')->first();
    $this->assertDatabaseHas('chores', [
        'id' => $chore->id,
        'name' => 'Updated Chore',
        'category_id' => $newCategory->id,
    ]);
});

it('validates required fields when updating a chore', function () {
    $category = Category::factory()->create();
    $chore = Chore::factory()->create(['category_id' => $category->id]);

    $response = $this->actingAs(User::factory()->create())->put("/chores/{$chore->id}", []);

    $response->assertSessionHasErrors(['name', 'category_name', 'frequency_type', 'frequency_value', 'next_due_at']);
});
