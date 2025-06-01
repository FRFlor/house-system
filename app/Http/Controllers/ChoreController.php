<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Chore;
use App\Models\ChoreCompletion;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChoreController extends Controller
{
    public function create()
    {
        $categories = Category::all()->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'color' => $category->color,
            ];
        });

        return Inertia::render('Chores/Create', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_name' => 'required|string|max:255',
            'frequency_type' => 'required|in:weeks,months,years,one_off',
            'frequency_value' => 'required|integer|min:1',
            'next_due_at' => 'required|date',
            'instruction_file_path' => 'nullable|string|max:255',
        ]);

        // Find or create the category
        $category = Category::where('name', $validated['category_name'])->first();
        
        if (!$category) {
            // Create new category with a random color
            $colors = ['#ef4444', '#f97316', '#eab308', '#22c55e', '#06b6d4', '#3b82f6', '#8b5cf6', '#ec4899'];
            $category = Category::create([
                'name' => $validated['category_name'],
                'color' => $colors[array_rand($colors)],
            ]);
        }
        
        $validated['category_id'] = $category->id;

        // Remove category_name from validated data as it's not a chore field
        unset($validated['category_name']);

        $validated['user_id'] = auth()->id();
        $validated['next_due_at'] = Carbon::parse($validated['next_due_at']);

        Chore::create($validated);

        return redirect('/dashboard')->with('success', 'Chore created successfully!');
    }

    public function complete(Request $request, Chore $chore): RedirectResponse
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
            'completed_at' => 'nullable|date',
        ]);

        $completedAt = $request->completed_at 
            ? Carbon::parse($request->completed_at)
            : now();

        // Mark the chore as completed (this updates last_completed_at and next_due_at)
        $chore->markAsCompleted($completedAt);

        // Create a completion record
        ChoreCompletion::create([
            'chore_id' => $chore->id,
            'completed_at' => $completedAt,
            'notes' => $request->notes,
        ]);

        return redirect()->route('dashboard')
            ->with('success', "'{$chore->name}' has been marked as completed!");
    }
} 