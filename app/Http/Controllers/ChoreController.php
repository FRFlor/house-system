<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Chore;
use App\Models\ChoreCompletion;
use App\Models\ChoreExpense;
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

    public function index()
    {
        $chores = Chore::with('category')
            ->orderBy('next_due_at', 'asc')
            ->get()
            ->map(function ($chore) {
                return [
                    'id' => $chore->id,
                    'name' => $chore->name,
                    'description' => $chore->description,
                    'category' => [
                        'id' => $chore->category->id,
                        'name' => $chore->category->name,
                        'color' => $chore->category->color,
                    ],
                    'frequency_type' => $chore->frequency_type->value,
                    'frequency_value' => $chore->frequency_value,
                    'next_due_at' => $chore->next_due_at?->format('Y-m-d'),
                    'last_completed_at' => $chore->last_completed_at?->format('Y-m-d'),
                    'instruction_file_path' => $chore->instruction_file_path,
                ];
            });

        return Inertia::render('Chores/Index', [
            'chores' => $chores,
        ]);
    }

    public function edit(Chore $chore)
    {
        $categories = Category::all()->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'color' => $category->color,
            ];
        });

        $choreData = [
            'id' => $chore->id,
            'name' => $chore->name,
            'description' => $chore->description,
            'category_id' => $chore->category_id,
            'category_name' => $chore->category->name,
            'frequency_type' => $chore->frequency_type->value,
            'frequency_value' => $chore->frequency_value,
            'next_due_at' => $chore->next_due_at?->format('Y-m-d'),
            'instruction_file_path' => $chore->instruction_file_path,
        ];

        return Inertia::render('Chores/Edit', [
            'chore' => $choreData,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|integer|exists:categories,id',
            'category_name' => 'nullable|string|max:255',
            'frequency_type' => 'required|in:weeks,months,years,one_off',
            'frequency_value' => 'required|integer|min:1',
            'next_due_at' => 'required|date',
            'instruction_file_path' => 'nullable|string|max:255',
        ]);

        // Validate that either category_id or category_name is provided
        if (empty($validated['category_id']) && empty($validated['category_name'])) {
            return back()->withErrors(['category_name' => 'Please select a category or create a new one.']);
        }

        // Handle category logic
        if (!empty($validated['category_name'])) {
            // Creating or finding a category by name
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
        }

        // Remove category_name from validated data as it's not a chore field
        unset($validated['category_name']);

        $validated['user_id'] = auth()->id();
        $validated['next_due_at'] = Carbon::parse($validated['next_due_at']);

        Chore::create($validated);

        return redirect('/dashboard')->with('success', 'Chore created successfully!');
    }

    public function update(Request $request, Chore $chore)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|required_without:category_name|integer|exists:categories,id',
            'category_name' => 'nullable|required_without:category_id|string|max:255',
            'frequency_type' => 'required|in:weeks,months,years,one_off',
            'frequency_value' => 'required|integer|min:1',
            'next_due_at' => 'required|date',
            'instruction_file_path' => 'nullable|string|max:255',
        ]);

        // Validate that either category_id or category_name is provided
        if (empty($validated['category_id']) && empty($validated['category_name'])) {
            return back()->withErrors(['category_name' => 'Please select a category or create a new one.']);
        }

        // Handle category logic
        if (!empty($validated['category_name'])) {
            // Creating or finding a category by name
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
        }

        // Remove category_name from validated data as it's not a chore field
        unset($validated['category_name']);

        $validated['next_due_at'] = Carbon::parse($validated['next_due_at']);

        $chore->update($validated);

        if (url()->previous() === route('chores.edit', $chore)) {
            return redirect('/chores')->with('success', 'Chore updated successfully!');
        }

        return redirect()->back()->with('success', 'Chore updated successfully!');
    }

    public function complete(Request $request, Chore $chore): RedirectResponse
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
            'completed_at' => 'nullable|date',
            'expenses' => 'nullable|array',
            'expenses.*.amount' => 'required_with:expenses|numeric|min:0',
            'expenses.*.description' => 'required_with:expenses|string|max:255',
        ]);

        $completedAt = $request->completed_at
            ? Carbon::parse($request->completed_at)
            : now();

        // Mark the chore as completed (this updates last_completed_at and next_due_at)
        $chore->markAsCompleted($completedAt);

        // Create a completion record
        $completion = ChoreCompletion::create([
            'chore_id' => $chore->id,
            'completed_at' => $completedAt,
            'notes' => $request->notes,
        ]);

        // Create expense records if provided
        if ($request->has('expenses') && is_array($request->expenses)) {
            foreach ($request->expenses as $expense) {
                ChoreExpense::create([
                    'chore_completion_id' => $completion->id,
                    'amount' => (int) round($expense['amount'] * 100), // Convert to cents
                    'description' => $expense['description'],
                ]);
            }
        }

        return redirect()->route('dashboard')
            ->with('success', "'{$chore->name}' has been marked as completed!");
    }
}
