<?php

namespace App\Livewire\User;

use Livewire\Component;
use App\Models\User;
use App\Models\TaskCompletion as TaskCompletionModel;
use App\Models\DailyAward;
use App\Models\PointAdjustment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Rankings extends Component
{
    public $timeframe = 'today'; // today, week, month, all_time
    public $selectedUserId = null;
    public $selectedUserTasks = [];
    public $selectedUserName = '';
    public $showTaskModal = false;

    public function setTimeframe($timeframe)
    {
        $this->timeframe = $timeframe;
    }

    public function showUserTasks($userId)
    {
        $this->selectedUserId = $userId;
        $user = User::find($userId);
        $this->selectedUserName = $user->name;
        
        // Fetch user's completed tasks for the current timeframe
        $this->selectedUserTasks = $this->getUserCompletedTasks($userId);
        $this->showTaskModal = true;
    }

    public function closeTaskModal()
    {
        $this->showTaskModal = false;
        $this->selectedUserId = null;
        $this->selectedUserTasks = [];
        $this->selectedUserName = '';
    }

    private function getUserCompletedTasks($userId)
    {
        $query = TaskCompletionModel::where('task_completions.user_id', $userId)
            ->where('verification_status', 'approved')
            ->join('task_assignments', 'task_completions.task_assignment_id', '=', 'task_assignments.id')
            ->join('tasks', 'task_assignments.task_id', '=', 'tasks.id')
            ->select([
                'tasks.title',
                'tasks.points',
                'task_completions.completed_at',
                'task_completions.completion_notes'
            ]);

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

        return $query->orderBy('task_completions.completed_at', 'desc')->get();
    }

    public function render()
    {
        $currentUser = Auth::user();

        // Get all users and calculate their points based on timeframe
        $users = User::where('role', 'user')->get();
        $userPoints = collect();

        foreach ($users as $user) {
            $taskPoints = 0;
            $adjustmentPoints = 0;

            // Build task completions query based on timeframe
            $taskQuery = TaskCompletionModel::where('task_completions.user_id', $user->id)
                ->where('verification_status', 'approved')
                ->join('task_assignments', 'task_completions.task_assignment_id', '=', 'task_assignments.id')
                ->join('tasks', 'task_assignments.task_id', '=', 'tasks.id');

            // Build point adjustments query based on timeframe
            $adjustmentQuery = PointAdjustment::where('user_id', $user->id);

            // Apply timeframe filter
            switch ($this->timeframe) {
                case 'today':
                    $taskQuery->whereDate('task_completions.completed_at', now());
                    $adjustmentQuery->whereDate('adjustment_date', now());
                    break;
                case 'week':
                    $taskQuery->whereBetween('task_completions.completed_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ]);
                    $adjustmentQuery->whereBetween('adjustment_date', [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ]);
                    break;
                case 'month':
                    $taskQuery->whereMonth('task_completions.completed_at', now()->month)
                         ->whereYear('task_completions.completed_at', now()->year);
                    $adjustmentQuery->whereMonth('adjustment_date', now()->month)
                         ->whereYear('adjustment_date', now()->year);
                    break;
                // 'all_time' has no additional filter
            }

            $taskPoints = $taskQuery->sum('tasks.points');
            $adjustmentPoints = $adjustmentQuery->sum('points');

            $totalPoints = $taskPoints + $adjustmentPoints;

            if ($totalPoints > 0) {
                $user->total_points = $totalPoints;
                $userPoints->push($user);
            }
        }

        // Sort users by total points and assign ranks
        $rankings = $userPoints->sortByDesc('total_points')
            ->values()
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
        // Get daily points from task completions
        $taskPoints = TaskCompletionModel::where('task_completions.user_id', $userId)
            ->where('verification_status', 'approved')
            ->join('task_assignments', 'task_completions.task_assignment_id', '=', 'task_assignments.id')
            ->join('tasks', 'task_assignments.task_id', '=', 'tasks.id')
            ->selectRaw('DATE(task_completions.completed_at) as completion_date, SUM(tasks.points) as daily_points')
            ->groupBy('completion_date')
            ->pluck('daily_points', 'completion_date');

        // Get daily points from adjustments
        $adjustmentPoints = PointAdjustment::where('user_id', $userId)
            ->selectRaw('DATE(adjustment_date) as adjustment_date, SUM(points) as daily_adjustments')
            ->groupBy('adjustment_date')
            ->pluck('daily_adjustments', 'adjustment_date');

        // Combine both and find the best day
        $allDates = $taskPoints->keys()->merge($adjustmentPoints->keys())->unique();
        $bestDayPoints = 0;

        foreach ($allDates as $date) {
            $totalPointsForDay = ($taskPoints->get($date, 0) + $adjustmentPoints->get($date, 0));
            if ($totalPointsForDay > $bestDayPoints) {
                $bestDayPoints = $totalPointsForDay;
            }
        }

        return $bestDayPoints;
    }
}
