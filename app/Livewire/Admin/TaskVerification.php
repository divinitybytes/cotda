<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\TaskCompletion;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class TaskVerification extends Component
{
    use WithPagination;

    public $selectedCompletion = null;
    public $adminNotes = [];
    public $filter = 'pending'; // pending, all, approved, rejected
    
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

    public function render()
    {
        $query = TaskCompletion::with([
            'assignment.user',
            'assignment.task',
            'verifiedBy'
        ]);

        // Apply filter
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

        $completions = $query->latest('completed_at')->paginate(10);

        // Get quick stats
        $stats = [
            'pending' => TaskCompletion::where('verification_status', 'pending')->count(),
            'approved_today' => TaskCompletion::where('verification_status', 'approved')
                ->whereDate('verified_at', now())
                ->count(),
            'total_today' => TaskCompletion::whereDate('completed_at', now())
                ->count(),
        ];

        return view('livewire.admin.task-verification', [
            'completions' => $completions,
            'stats' => $stats,
        ]);
    }
}
