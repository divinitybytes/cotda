<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\TaskCompletion;
use App\Models\DailyAward;
use App\Models\CashOutRequest;
use App\Models\PointAdjustment;
use App\Services\RecurringTaskService;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    public $todayPoints = 0;
    public $totalPoints = 0;
    public $ranking = 0;
    public $balance = null;
    public $pendingTasks = 0;
    public $completedTasks = 0;
    public $recentAward = null;
    public $showConfetti = false;

    public function mount()
    {
        $user = Auth::user();
        
        // For regular users, ensure they have fresh recurring task assignments
        if ($user->isUser()) {
            RecurringTaskService::createUserRecurringAssignments($user);
        }
        
        $this->loadUserStats();
    }

    public function createTodaysRecurringTasks()
    {
        if (!Auth::user()->isAdmin()) {
            return;
        }

        $created = RecurringTaskService::createRecurringAssignments();
        
        if ($created > 0) {
            session()->flash('message', "Created {$created} recurring task assignments for today!");
        } else {
            session()->flash('message', 'All recurring task assignments are up to date.');
        }
    }

    public function loadUserStats()
    {
        $user = Auth::user();
        
        if ($user->isUser()) {
            // Load user statistics
            $this->totalPoints = $user->points;
            $this->ranking = $user->getTodayRanking();
            $this->balance = $user->balance;
            
            // Get today's points from task completions
            $today = now()->toDateString();
            $taskPoints = TaskCompletion::where('task_completions.user_id', $user->id)
                ->where('verification_status', 'approved')
                ->whereDate('completed_at', $today)
                ->join('task_assignments', 'task_completions.task_assignment_id', '=', 'task_assignments.id')
                ->join('tasks', 'task_assignments.task_id', '=', 'tasks.id')
                ->sum('tasks.points');

            // Get today's manual point adjustments
            $adjustmentPoints = PointAdjustment::where('user_id', $user->id)
                ->whereDate('adjustment_date', $today)
                ->sum('points');

            // Total today's points
            $this->todayPoints = $taskPoints + $adjustmentPoints;

            // Get task statistics - only count today's tasks
            $this->pendingTasks = TaskAssignment::where('user_id', $user->id)
                ->whereDate('assigned_date', now()->toDateString())
                ->where(function($q) {
                    $q->where('is_completed', false)
                      ->orWhereHas('completion', function($completionQuery) {
                          $completionQuery->where('verification_status', 'pending');
                      });
                })
                ->count();
                
            $this->completedTasks = TaskAssignment::where('user_id', $user->id)
                ->whereDate('assigned_date', now()->toDateString())
                ->whereHas('completion', function($completionQuery) {
                    $completionQuery->whereIn('verification_status', ['approved', 'rejected']);
                })
                ->count();

            // Get recent award
            $this->recentAward = DailyAward::where('user_id', $user->id)
                ->latest('award_date')
                ->first();

            // Check if user won today's award (show confetti)
            $this->showConfetti = DailyAward::where('user_id', $user->id)
                ->whereDate('award_date', $today)
                ->exists();
        }
    }

    public function render()
    {
        $user = Auth::user();
        
        $data = [
            'user' => $user,
            'isAdmin' => $user->isAdmin(),
        ];

        if ($user->isAdmin()) {
            // Admin dashboard data
            $data['totalUsers'] = User::where('role', 'user')->count();
            $data['totalTasks'] = Task::active()->count();
            $data['pendingVerifications'] = TaskCompletion::pending()->count();
            $data['pendingCashOuts'] = CashOutRequest::pending()->count();
            $data['todayCompletions'] = TaskCompletion::whereDate('completed_at', now())->count();
            $data['recentCompletions'] = TaskCompletion::with(['user', 'taskAssignment.task'])
                ->pending()
                ->latest()
                ->take(5)
                ->get();
        } else {
            // User dashboard data - only show today's tasks
            $data['pendingAssignments'] = TaskAssignment::with(['task'])
                ->where('user_id', $user->id)
                ->whereDate('assigned_date', now()->toDateString())
                ->where(function($q) {
                    $q->where('is_completed', false)
                      ->orWhereHas('completion', function($completionQuery) {
                          $completionQuery->where('verification_status', 'pending');
                      });
                })
                ->latest()
                ->take(5)
                ->get();
            
            $data['recentCompletions'] = TaskCompletion::with(['taskAssignment.task'])
                ->where('user_id', $user->id)
                ->latest()
                ->take(5)
                ->get();
        }

        return view('livewire.dashboard', $data);
    }
}
