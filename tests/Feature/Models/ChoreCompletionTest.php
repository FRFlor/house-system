<?php

use App\Models\Chore;
use App\Models\ChoreCompletion;

test('chore completion can be created with required fields', function () {
    $chore = Chore::factory()->create();
    
    $completion = ChoreCompletion::factory()->create([
        'chore_id' => $chore->id,
        'completed_at' => now(),
        'notes' => 'Replaced with HEPA filter',
    ]);

    expect($completion->chore_id)->toBe($chore->id);
    expect($completion->completed_at)->toBeInstanceOf(\Carbon\Carbon::class);
    expect($completion->notes)->toBe('Replaced with HEPA filter');
    expect($completion->exists)->toBeTrue();
});

test('chore completion belongs to a chore', function () {
    $completion = ChoreCompletion::factory()->create();
    
    expect($completion->chore())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class);
    expect($completion->chore)->toBeInstanceOf(Chore::class);
});

test('chore completion completed_at is required', function () {
    expect(fn() => ChoreCompletion::factory()->create(['completed_at' => null]))
        ->toThrow(\Illuminate\Database\QueryException::class);
});

test('chore completion can have notes', function () {
    $completion = ChoreCompletion::factory()->create([
        'notes' => 'Used premium filter this time'
    ]);
    
    expect($completion->notes)->toBe('Used premium filter this time');
});

test('chore completion notes can be null', function () {
    $completion = ChoreCompletion::factory()->create([
        'notes' => null
    ]);
    
    expect($completion->notes)->toBeNull();
}); 