<?php

use App\Models\Category;

test('category can be created with name and color', function () {
    $category = Category::factory()->create([
        'name' => 'HVAC',
        'color' => '#FF5733'
    ]);

    expect($category->name)->toBe('HVAC');
    expect($category->color)->toBe('#FF5733');
    expect($category->exists)->toBeTrue();
});

test('category name is required', function () {
    expect(fn() => Category::factory()->create(['name' => null]))
        ->toThrow(\Illuminate\Database\QueryException::class);
});

test('category name must be unique', function () {
    Category::factory()->create(['name' => 'HVAC']);
    
    expect(fn() => Category::factory()->create(['name' => 'HVAC']))
        ->toThrow(\Illuminate\Database\QueryException::class);
});

test('category has default color if not provided', function () {
    $category = Category::factory()->create(['color' => null]);
    
    expect($category->color)->not->toBeNull();
});

test('category can have many chores', function () {
    $category = Category::factory()->create();
    
    expect($category->chores())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
}); 