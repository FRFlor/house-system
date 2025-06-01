<?php


use App\Models\Category;
use App\Models\Chore;
use App\Models\User;

it('can view the chores index page', function () {
    $category = Category::factory()->create();
    Chore::factory()->create(['category_id' => $category->id, 'name' => 'First Chore']);
    Chore::factory()->create(['category_id' => $category->id, 'name' => 'Second Chore']);

    $response = $this->actingAs(User::factory()->create())->get('/chores');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) =>
    $page->component('Chores/Index')
        ->has('chores')
        ->where('chores', fn ($chores) =>
            collect($chores)->pluck('name')->contains('First Chore') &&
            collect($chores)->pluck('name')->contains('Second Chore')
        )
    );
});
