<?php

namespace App\Http\Controllers;

use App\Models\Chore;
use App\Models\ChoreCompletion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $month = (int) $request->get('month', Carbon::now()->month);
        $year = (int) $request->get('year', Carbon::now()->year);

        $startDate = Carbon::create($year, $month, 1)->startOfDay();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();

        $chores = Chore::with('category')
            ->whereDate('next_due_at', '>=', $startDate->toDateString())
            ->whereDate('next_due_at', '<=', $endDate->toDateString())
            ->get()
            ->map(function ($chore) {
                return [
                    'id' => $chore->id,
                    'name' => $chore->name,
                    'description' => $chore->description,
                    'next_due_at' => $chore->next_due_at->toISOString(),
                    'frequency_type' => $chore->frequency_type,
                    'frequency_value' => $chore->frequency_value,
                    'category' => [
                        'id' => $chore->category->id,
                        'name' => $chore->category->name,
                        'color' => $chore->category->color,
                    ],
                ];
            });

        // Get all completions within the month range
        $completions = ChoreCompletion::with(['chore.category'])
            ->whereDate('completed_at', '>=', $startDate->toDateString())
            ->whereDate('completed_at', '<=', $endDate->toDateString())
            ->get()
            ->map(function ($completion) {
                return [
                    'id' => $completion->id,
                    'completed_at' => $completion->completed_at->toISOString(),
                    'notes' => $completion->notes,
                    'chore' => [
                        'id' => $completion->chore->id,
                        'name' => $completion->chore->name,
                        'description' => $completion->chore->description,
                        'category' => [
                            'id' => $completion->chore->category->id,
                            'name' => $completion->chore->category->name,
                            'color' => $completion->chore->category->color,
                        ],
                    ],
                ];
            });

        return Inertia::render('Calendar', [
            'chores' => $chores,
            'completions' => $completions,
            'currentMonth' => $month,
            'currentYear' => $year,
        ]);
    }
}
