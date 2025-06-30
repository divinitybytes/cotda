<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\CashOutRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class CashOutManager extends Component
{
    use WithPagination;

    public $isModalVisible = false;
    public $selectedRequest = null;
    public $adminNotes = '';
    public $action = '';
    public $filter = 'pending'; // pending, approved, rejected, all

    protected $rules = [
        'adminNotes' => 'nullable|string|max:1000',
    ];

    public function mount()
    {
        // Ensure only admins can access this component
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }
    }

    public function setFilter($filter)
    {
        $this->filter = $filter;
        $this->resetPage();
    }

    public function showProcessModal($requestId, $action)
    {
        $this->selectedRequest = CashOutRequest::with(['user', 'processedBy'])->findOrFail($requestId);
        $this->action = $action;
        $this->adminNotes = '';
        $this->isModalVisible = true;
    }

    public function hideProcessModal()
    {
        $this->isModalVisible = false;
        $this->selectedRequest = null;
        $this->adminNotes = '';
        $this->action = '';
    }

    public function processRequest()
    {
        $this->validate();

        if (!$this->selectedRequest || !$this->selectedRequest->isPending()) {
            session()->flash('error', 'Invalid request or request already processed.');
            $this->hideProcessModal();
            return;
        }

        $success = false;
        $message = '';

        if ($this->action === 'approve') {
            $success = $this->selectedRequest->approve(Auth::id(), $this->adminNotes);
            $message = $success 
                ? "Cash out request approved and processed for {$this->selectedRequest->user->name}."
                : "Failed to approve cash out request. User may not have sufficient vested balance.";
        } elseif ($this->action === 'reject') {
            $success = $this->selectedRequest->reject(Auth::id(), $this->adminNotes);
            $message = $success 
                ? "Cash out request rejected for {$this->selectedRequest->user->name}."
                : "Failed to reject cash out request.";
        }

        if ($success) {
            session()->flash('message', $message);
        } else {
            session()->flash('error', $message);
        }

        $this->hideProcessModal();
    }

    public function render()
    {
        $query = CashOutRequest::with(['user', 'processedBy']);

        // Apply filter
        switch ($this->filter) {
            case 'pending':
                $query->pending();
                break;
            case 'approved':
                $query->approved();
                break;
            case 'rejected':
                $query->rejected();
                break;
            // 'all' shows everything, no additional filter needed
        }

        $requests = $query->latest()->paginate(10);

        // Get summary stats
        $stats = [
            'pending' => CashOutRequest::pending()->count(),
            'approved' => CashOutRequest::approved()->count(),
            'rejected' => CashOutRequest::rejected()->count(),
            'total' => CashOutRequest::count(),
        ];

        return view('livewire.admin.cash-out-manager', [
            'requests' => $requests,
            'stats' => $stats,
        ]);
    }
}
