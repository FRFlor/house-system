<?php

namespace App\Http\Controllers;

use App\Models\Chore;
use App\Models\ChoreCompletion;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $now = Carbon::now();
        $today = $now->startOfDay();
        $endOfToday = $now->copy()->endOfDay();
        $weekFromNow = $today->copy()->addDays(7);

        // Get active chores (exclude completed ONE_OFF chores)
        $activeChoresQuery = Chore::with('category')
            ->whereNotNull('next_due_at');

        // Overdue chores (due before today)
        $overdueChores = $activeChoresQuery->clone()
            ->where('next_due_at', '<', $today)
            ->orderBy('next_due_at', 'asc')
            ->get();

        // Due today
        $dueToday = $activeChoresQuery->clone()
            ->whereBetween('next_due_at', [$today, $endOfToday])
            ->orderBy('next_due_at', 'asc')
            ->get();

        // Upcoming (tomorrow through next 7 days)
        $upcomingChores = $activeChoresQuery->clone()
            ->where('next_due_at', '>', $endOfToday)
            ->where('next_due_at', '<=', $weekFromNow)
            ->orderBy('next_due_at', 'asc')
            ->get();

        // Recent completions (last 7 days)
        $recentCompletions = ChoreCompletion::with('chore.category')
            ->where('completed_at', '>=', $now->copy()->subDays(7))
            ->orderBy('completed_at', 'desc')
            ->limit(10)
            ->get();

        // Summary statistics
        $totalActiveChores = $activeChoresQuery->clone()->count();
        $overdueCount = $overdueChores->count();
        $dueTodayCount = $dueToday->count();
        $upcomingCount = $upcomingChores->count();

        return Inertia::render('Dashboard', [
            'overdue_chores' => $overdueChores,
            'due_today' => $dueToday,
            'upcoming_chores' => $upcomingChores,
            'recent_completions' => $recentCompletions,
            'stats' => [
                'total_active_chores' => $totalActiveChores,
                'overdue_count' => $overdueCount,
                'due_today_count' => $dueTodayCount,
                'upcoming_count' => $upcomingCount,
            ],
        ]);
    }
} 