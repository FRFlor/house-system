<?php

namespace App\Http\Controllers;

use App\Models\Chore;
use App\Models\ChoreCompletion;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ChoreController extends Controller
{
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