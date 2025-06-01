<?php

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ChoreController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/calendar', [CalendarController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('calendar');

Route::post('/chores/{chore}/complete', [ChoreController::class, 'complete'])
    ->middleware(['auth', 'verified'])
    ->name('chores.complete');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
