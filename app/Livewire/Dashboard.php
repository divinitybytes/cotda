<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\TaskCompletion;
use App\Models\DailyAward;
use App\Models\CashOutRequest;
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
    public $isAwarding = false;

    public function mount()
    {
        $this->loadUserStats();
    }

    public function loadUserStats()
    {
        $user = Auth::user();
        
        if ($user->isUser()) {
            // Load user statistics
            $this->totalPoints = $user->points;
            $this->ranking = $user->getTodayRanking();
            $this->balance = $user->balance;
            
            // Get today's points
            $today = now()->toDateString();
            $this->todayPoints = TaskCompletion::where('task_completions.user_id', $user->id)
                ->where('verification_status', 'approved')
                ->whereDate('completed_at', $today)
                ->join('task_assignments', 'task_completions.task_assignment_id', '=', 'task_assignments.id')
                ->join('tasks', 'task_assignments.task_id', '=', 'tasks.id')
                ->sum('tasks.points');

            // Get task statistics
            $this->pendingTasks = TaskAssignment::where('user_id', $user->id)
                ->where('is_completed', false)
                ->count();
                
            $this->completedTasks = TaskAssignment::where('user_id', $user->id)
                ->where('is_completed', true)
                ->count();

            // Get recent award
            $this->recentAward = DailyAward::where('user_id', $user->id)
                ->latest('award_date')
                ->first();
        }
    }

    public function awardDailyWinner()
    {
        if (Auth::user()->isAdmin()) {
            $this->isAwarding = true;
            
            try {
                $award = DailyAward::awardDailyWinner();
                
                if ($award) {
                    session()->flash('message', "Daily winner awarded! {$award->user->name} earned ${$award->cash_amount} for {$award->points_earned} points!");
                    $this->dispatch('award-created');
                    $this->loadUserStats();
                } else {
                    session()->flash('error', 'Daily award has already been given for today or no eligible users found.');
                }
            } catch (\Exception $e) {
                session()->flash('error', 'An error occurred while awarding the daily winner. Please try again.');
            } finally {
                $this->isAwarding = false;
            }
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
            // User dashboard data
            $data['pendingAssignments'] = TaskAssignment::with(['task'])
                ->where('user_id', $user->id)
                ->where('is_completed', false)
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
