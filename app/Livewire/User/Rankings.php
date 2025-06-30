<?php

namespace App\Livewire\User;

use Livewire\Component;
use App\Models\User;
use App\Models\TaskCompletion as TaskCompletionModel;
use App\Models\DailyAward;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Rankings extends Component
{
    public $timeframe = 'today'; // today, week, month, all_time

    public function setTimeframe($timeframe)
    {
        $this->timeframe = $timeframe;
    }

    public function render()
    {
        $currentUser = Auth::user();

        // Get rankings based on timeframe
        $query = User::select('users.*')
            ->join('task_completions', 'users.id', '=', 'task_completions.user_id')
            ->join('task_assignments', 'task_completions.task_assignment_id', '=', 'task_assignments.id')
            ->join('tasks', 'task_assignments.task_id', '=', 'tasks.id')
            ->where('task_completions.verification_status', 'approved')
            ->where('users.role', 'user');

        // Apply timeframe filter
        switch ($this->timeframe) {
            case 'today':
                $query->whereDate('task_completions.completed_at', now());
                break;
            case 'week':
                $query->whereBetween('task_completions.completed_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ]);
                break;
            case 'month':
                $query->whereMonth('task_completions.completed_at', now()->month)
                     ->whereYear('task_completions.completed_at', now()->year);
                break;
            // 'all_time' has no additional filter
        }

        $rankings = $query->selectRaw('users.*, SUM(tasks.points) as total_points')
            ->groupBy('users.id', 'users.name', 'users.email', 'users.email_verified_at', 'users.password', 'users.remember_token', 'users.created_at', 'users.updated_at', 'users.role', 'users.points')
            ->orderByDesc('total_points')
            ->get()
            ->map(function ($user, $index) {
                $user->rank = $index + 1;
                return $user;
            });

        // Get current user's rank
        $currentUserRank = $rankings->where('id', $currentUser->id)->first();

        // Get recent daily awards
        $recentAwards = DailyAward::with('user')
            ->latest('award_date')
            ->take(7)
            ->get();

        // Get achievement stats
        $achievements = [
            'total_completions' => TaskCompletionModel::where('user_id', $currentUser->id)
                ->where('verification_status', 'approved')
                ->count(),
            'daily_awards' => DailyAward::where('user_id', $currentUser->id)->count(),
            'streak_days' => $this->calculateStreak($currentUser->id),
            'best_day_points' => $this->getBestDayPoints($currentUser->id),
        ];

        return view('livewire.user.rankings', [
            'rankings' => $rankings,
            'currentUserRank' => $currentUserRank,
            'recentAwards' => $recentAwards,
            'achievements' => $achievements,
        ]);
    }

    private function calculateStreak($userId)
    {
        $completions = TaskCompletionModel::where('user_id', $userId)
            ->where('verification_status', 'approved')
            ->selectRaw('DATE(completed_at) as completion_date')
            ->distinct()
            ->orderByDesc('completion_date')
            ->pluck('completion_date');

        $streak = 0;
        $currentDate = now()->toDateString();

        foreach ($completions as $completionDate) {
            if ($completionDate === $currentDate) {
                $streak++;
                $currentDate = now()->subDays($streak)->toDateString();
            } else {
                break;
            }
        }

        return $streak;
    }

    private function getBestDayPoints($userId)
    {
        return TaskCompletionModel::where('task_completions.user_id', $userId)
            ->where('verification_status', 'approved')
            ->join('task_assignments', 'task_completions.task_assignment_id', '=', 'task_assignments.id')
            ->join('tasks', 'task_assignments.task_id', '=', 'tasks.id')
            ->selectRaw('DATE(task_completions.completed_at) as completion_date, SUM(tasks.points) as daily_points')
            ->groupBy('completion_date')
            ->orderByDesc('daily_points')
            ->value('daily_points') ?? 0;
    }
}
