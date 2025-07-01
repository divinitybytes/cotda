<?php

namespace App\Livewire\User;

use Livewire\Component;
use App\Models\TaskAssignment;
use App\Models\TaskCompletion as TaskCompletionModel;
use App\Services\RecurringTaskService;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class TaskList extends Component
{
    use WithPagination;

    public $filter = 'pending'; // pending, completed, all

    public function mount()
    {
        // Ensure user has fresh recurring task assignments
        RecurringTaskService::createUserRecurringAssignments(Auth::user());
    }

    public function setFilter($filter)
    {
        $this->filter = $filter;
        $this->resetPage();
    }

    public function render()
    {
        $query = TaskAssignment::with(['task', 'completion'])
            ->where('user_id', Auth::id())
            ->whereDate('assigned_date', now()->toDateString()); // Only show today's tasks

        // Apply filter
        switch ($this->filter) {
            case 'pending':
                // Show tasks that have no completion yet OR have a pending completion
                $query->where(function($q) {
                    $q->whereDoesntHave('completion')
                      ->orWhereHas('completion', function($completionQuery) {
                          $completionQuery->where('verification_status', 'pending');
                      });
                });
                break;
            case 'completed':
                // Show tasks that are completed with approved verification OR rejected (still considered complete attempt)
                $query->where(function($q) {
                    $q->whereHas('completion', function($completionQuery) {
                        $completionQuery->whereIn('verification_status', ['approved', 'rejected']);
                    });
                });
                break;
            // 'all' shows everything (but still only today's tasks)
        }

        $assignments = $query->latest('assigned_date')->paginate(10);

        // Get quick stats for today only
        $stats = [
            'pending' => TaskAssignment::where('user_id', Auth::id())
                ->whereDate('assigned_date', now()->toDateString())
                ->where(function($q) {
                    $q->whereDoesntHave('completion')
                      ->orWhereHas('completion', function($completionQuery) {
                          $completionQuery->where('verification_status', 'pending');
                      });
                })
                ->count(),
            'completed' => TaskAssignment::where('user_id', Auth::id())
                ->whereDate('assigned_date', now()->toDateString())
                ->whereHas('completion', function($completionQuery) {
                    $completionQuery->whereIn('verification_status', ['approved', 'rejected']);
                })
                ->count(),
            'total_points' => TaskCompletionModel::where('task_completions.user_id', Auth::id())
                ->where('verification_status', 'approved')
                ->join('task_assignments', 'task_completions.task_assignment_id', '=', 'task_assignments.id')
                ->join('tasks', 'task_assignments.task_id', '=', 'tasks.id')
                ->sum('tasks.points'),
            'today_points' => TaskCompletionModel::where('task_completions.user_id', Auth::id())
                ->where('verification_status', 'approved')
                ->whereDate('completed_at', now())
                ->join('task_assignments', 'task_completions.task_assignment_id', '=', 'task_assignments.id')
                ->join('tasks', 'task_assignments.task_id', '=', 'tasks.id')
                ->sum('tasks.points'),
        ];

        return view('livewire.user.task-list', [
            'assignments' => $assignments,
            'stats' => $stats,
        ]);
    }
}
