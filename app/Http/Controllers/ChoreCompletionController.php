<?php

namespace App\Http\Controllers;

use App\Models\ChoreCompletion;
use App\Models\ChoreExpense;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ChoreCompletionController extends Controller
{
    public function update(Request $request, ChoreCompletion $completion): RedirectResponse
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
            'completed_at' => 'nullable|date',
            'expenses' => 'nullable|array',
            'expenses.*.amount' => 'required_with:expenses|numeric|min:0.01',
            'expenses.*.description' => 'required_with:expenses|string|max:255|min:1',
        ]);

        // Update the completion notes and timestamp
        $updateData = [
            'notes' => $request->notes,
        ];

        if ($request->has('completed_at') && $request->completed_at !== null) {
            $updateData['completed_at'] = Carbon::parse($request->completed_at);
        }

        $completion->update($updateData);

        // Delete existing expenses
        $completion->expenses()->delete();

        // Create new expense records if provided
        if ($request->has('expenses') && is_array($request->expenses)) {
            foreach ($request->expenses as $expense) {
                if (!empty($expense['description']) && $expense['amount'] > 0) {
                    ChoreExpense::create([
                        'chore_completion_id' => $completion->id,
                        'amount' => (int) round($expense['amount'] * 100), // Convert to cents
                        'description' => $expense['description'],
                    ]);
                }
            }
        }

        return redirect()->back()
            ->with('success', 'Completion details updated successfully!');
    }
} 