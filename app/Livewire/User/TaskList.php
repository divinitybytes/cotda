<?php

namespace App\Livewire\User;

use Livewire\Component;
use App\Models\TaskAssignment;
use App\Models\TaskCompletion as TaskCompletionModel;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class TaskList extends Component
{
    use WithPagination;

    public $filter = 'pending'; // pending, completed, all

    public function setFilter($filter)
    {
        $this->filter = $filter;
        $this->resetPage();
    }

    public function render()
    {
        $query = TaskAssignment::with(['task', 'completion'])
            ->where('user_id', Auth::id());

        // Apply filter
        switch ($this->filter) {
            case 'pending':
                $query->where('is_completed', false);
                break;
            case 'completed':
                $query->where('is_completed', true);
                break;
            // 'all' shows everything
        }

        $assignments = $query->latest('assigned_date')->paginate(10);

        // Get quick stats
        $stats = [
            'pending' => TaskAssignment::where('user_id', Auth::id())
                ->where('is_completed', false)
                ->count(),
            'completed' => TaskAssignment::where('user_id', Auth::id())
                ->where('is_completed', true)
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
