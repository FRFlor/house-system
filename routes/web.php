<?php

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ChoreController;
use App\Http\Controllers\ChoreCompletionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');
    Route::get('/chores/create', [ChoreController::class, 'create'])->name('chores.create');
    Route::post('/chores', [ChoreController::class, 'store'])->name('chores.store');
    Route::get('/chores', [ChoreController::class, 'index'])->name('chores.index');
    Route::get('/chores/{chore}/edit', [ChoreController::class, 'edit'])->name('chores.edit');
    Route::put('/chores/{chore}', [ChoreController::class, 'update'])->name('chores.update');
    Route::post('/chores/{chore}/complete', [ChoreController::class, 'complete'])->name('chores.complete');

    Route::put('/completions/{completion}', [ChoreCompletionController::class, 'update'])->name('completions.update');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
