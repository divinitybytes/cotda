<?php

/**
 * TaskVerification Component
 * 
 * Enhanced admin interface for task completion verification with comprehensive
 * sorting and filtering capabilities.
 * 
 * Features:
 * - Status filtering (pending, approved, rejected, all)
 * - User-based filtering
 * - Task-based filtering
 * - Date range filtering
 * - Task type filtering (one-time, recurring)
 * - Points range filtering
 * - Photo evidence filtering
 * - Late submission filtering
 * - Multiple sorting options (date, user, task, points, status, verification date)
 * - Quick filter presets for common use cases
 * 
 * @author Your Name
 * @version 1.1.0
 */

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\TaskCompletion;
use App\Models\User;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Livewire\Attributes\Validate;

class TaskVerification extends Component
{
    use WithPagination;

    public $selectedCompletion = null;
    public $adminNotes = [];
    public $filter = 'pending'; // pending, all, approved, rejected
    
    // Sorting and Filtering
    public $sortBy = 'completed_at';
    public $sortDirection = 'desc';
    public $showFilters = false;
    
    // Filter properties
    #[Validate('nullable|exists:users,id')]
    public $filterUser = '';
    
    #[Validate('nullable|exists:tasks,id')]
    public $filterTask = '';
    
    #[Validate('nullable|date')]
    public $dateFrom = '';
    
    #[Validate('nullable|date')]
    public $dateTo = '';
    
    public $filterTaskType = ''; // '', 'one_time', 'recurring'
    public $filterLateOnly = false;
    public $filterWithPhoto = ''; // '', 'with', 'without'
    public $minPoints = '';
    public $maxPoints = '';
    
    // Photo modal properties
    public $showPhotoModal = false;
    public $modalPhotoUrl = '';
    
    // Bulk reject modal properties
    public $showBulkRejectModal = false;
    public $bulkRejectReason = '';

    public function mount()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }
    }

    public function viewCompletion($completionId)
    {
        $this->selectedCompletion = TaskCompletion::with([
            'user', 
            'taskAssignment.task', 
            'verifier'
        ])->findOrFail($completionId);
        $this->adminNotes = $this->selectedCompletion->admin_notes ?? '';
    }

    public function closeModal()
    {
        $this->selectedCompletion = null;
        $this->adminNotes = [];
    }

    // Photo modal methods
    public function showPhotoModal($url)
    {
        $this->modalPhotoUrl = $url;
        $this->showPhotoModal = true;
    }

    public function hidePhotoModal()
    {
        $this->showPhotoModal = false;
        $this->modalPhotoUrl = '';
    }

    // Bulk reject modal methods
    public function showBulkRejectModal()
    {
        $this->showBulkRejectModal = true;
    }

    public function hideBulkRejectModal()
    {
        $this->showBulkRejectModal = false;
        $this->bulkRejectReason = '';
    }

    // Individual completion actions
    public function approve($completionId)
    {
        $completion = TaskCompletion::findOrFail($completionId);
        
        if ($completion->verification_status === 'pending') {
            $adminNotes = $this->adminNotes[$completionId] ?? '';
            $completion->update([
                'verification_status' => 'approved',
                'verified_at' => now(),
                'verified_by' => Auth::id(),
                'admin_notes' => $adminNotes
            ]);
            
            // Award points to user
            $completion->assignment->user->increment('points', $completion->assignment->task->points);
            
            session()->flash('message', 'Task completion approved successfully!');
            $this->adminNotes[$completionId] = '';
        }
    }

    public function reject($completionId)
    {
        $completion = TaskCompletion::findOrFail($completionId);
        
        if ($completion->verification_status === 'pending') {
            $adminNotes = $this->adminNotes[$completionId] ?? '';
            $completion->update([
                'verification_status' => 'rejected',
                'verified_at' => now(),
                'verified_by' => Auth::id(),
                'admin_notes' => $adminNotes
            ]);
            
            session()->flash('message', 'Task completion rejected.');
            $this->adminNotes[$completionId] = '';
        }
    }

    // Bulk actions
    public function approveAll()
    {
        $completions = TaskCompletion::where('verification_status', 'pending')->get();
        
        foreach ($completions as $completion) {
            $completion->update([
                'verification_status' => 'approved',
                'verified_at' => now(),
                'verified_by' => Auth::id(),
                'admin_notes' => 'Bulk approved'
            ]);
            
            // Award points to user
            $completion->assignment->user->increment('points', $completion->assignment->task->points);
        }
        
        session()->flash('message', 'All pending completions approved successfully!');
    }

    public function bulkReject()
    {
        if (empty($this->bulkRejectReason)) {
            session()->flash('error', 'Please provide a reason for rejection.');
            return;
        }
        
        $completions = TaskCompletion::where('verification_status', 'pending')->get();
        
        foreach ($completions as $completion) {
            $completion->update([
                'verification_status' => 'rejected',
                'verified_at' => now(),
                'verified_by' => Auth::id(),
                'admin_notes' => $this->bulkRejectReason
            ]);
        }
        
        session()->flash('message', 'All pending completions rejected.');
        $this->hideBulkRejectModal();
    }



    public function setFilter($filter)
    {
        $this->filter = $filter;
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    public function clearFilters()
    {
        $this->filterUser = '';
        $this->filterTask = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->filterTaskType = '';
        $this->filterLateOnly = false;
        $this->filterWithPhoto = '';
        $this->minPoints = '';
        $this->maxPoints = '';
        $this->resetPage();
    }

    public function updatedFilterUser()
    {
        $this->resetPage();
    }

    public function updatedFilterTask()
    {
        $this->resetPage();
    }

    public function updatedDateFrom()
    {
        $this->resetPage();
    }

    public function updatedDateTo()
    {
        $this->resetPage();
    }

    public function updatedFilterTaskType()
    {
        $this->resetPage();
    }

    public function updatedFilterLateOnly()
    {
        $this->resetPage();
    }

    public function updatedFilterWithPhoto()
    {
        $this->resetPage();
    }

    public function updatedMinPoints()
    {
        $this->resetPage();
    }

    public function updatedMaxPoints()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = TaskCompletion::with([
            'assignment.user',
            'assignment.task',
            'verifiedBy'
        ]);

        // Apply status filter
        switch ($this->filter) {
            case 'pending':
                $query->where('verification_status', 'pending');
                break;
            case 'approved':
                $query->where('verification_status', 'approved');
                break;
            case 'rejected':
                $query->where('verification_status', 'rejected');
                break;
            // 'all' shows everything
        }

        // Apply user filter
        if ($this->filterUser) {
            $query->where('user_id', $this->filterUser);
        }

        // Apply task filter
        if ($this->filterTask) {
            $query->whereHas('assignment', function ($q) {
                $q->where('task_id', $this->filterTask);
            });
        }

        // Apply date range filter
        if ($this->dateFrom) {
            $query->whereDate('completed_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('completed_at', '<=', $this->dateTo);
        }

        // Apply task type filter
        if ($this->filterTaskType) {
            $query->whereHas('assignment.task', function ($q) {
                $q->where('type', $this->filterTaskType);
            });
        }

        // Apply late submissions filter
        if ($this->filterLateOnly) {
            $query->whereRaw('completed_at > (SELECT due_date FROM task_assignments WHERE task_assignments.id = task_completions.task_assignment_id AND due_date IS NOT NULL)');
        }

        // Apply photo filter
        if ($this->filterWithPhoto === 'with') {
            $query->whereNotNull('photo_path');
        } elseif ($this->filterWithPhoto === 'without') {
            $query->whereNull('photo_path');
        }

        // Apply points range filter
        if ($this->minPoints !== '') {
            $query->whereHas('assignment.task', function ($q) {
                $q->where('points', '>=', $this->minPoints);
            });
        }
        if ($this->maxPoints !== '') {
            $query->whereHas('assignment.task', function ($q) {
                $q->where('points', '<=', $this->maxPoints);
            });
        }

        // Apply sorting
        switch ($this->sortBy) {
            case 'user_name':
                $query->join('users', 'task_completions.user_id', '=', 'users.id')
                      ->orderBy('users.name', $this->sortDirection)
                      ->select('task_completions.*');
                break;
            case 'task_title':
                $query->join('task_assignments', 'task_completions.task_assignment_id', '=', 'task_assignments.id')
                      ->join('tasks', 'task_assignments.task_id', '=', 'tasks.id')
                      ->orderBy('tasks.title', $this->sortDirection)
                      ->select('task_completions.*');
                break;
            case 'points':
                $query->join('task_assignments', 'task_completions.task_assignment_id', '=', 'task_assignments.id')
                      ->join('tasks', 'task_assignments.task_id', '=', 'tasks.id')
                      ->orderBy('tasks.points', $this->sortDirection)
                      ->select('task_completions.*');
                break;
            case 'verification_status':
                $query->orderBy('verification_status', $this->sortDirection);
                break;
            case 'verified_at':
                $query->orderBy('verified_at', $this->sortDirection);
                break;
            default: // completed_at
                $query->orderBy('completed_at', $this->sortDirection);
                break;
        }

        $completions = $query->paginate(20);

        // Get quick stats
        $stats = [
            'pending' => TaskCompletion::where('verification_status', 'pending')->count(),
            'approved_today' => TaskCompletion::where('verification_status', 'approved')
                ->whereDate('verified_at', now())
                ->count(),
            'total_today' => TaskCompletion::whereDate('completed_at', now())
                ->count(),
        ];

        // Get filter options
        $users = User::where('role', 'user')->orderBy('name')->get();
        $tasks = Task::active()->orderBy('title')->get();

        return view('livewire.admin.task-verification', [
            'completions' => $completions,
            'stats' => $stats,
            'users' => $users,
            'tasks' => $tasks,
        ]);
    }
}
