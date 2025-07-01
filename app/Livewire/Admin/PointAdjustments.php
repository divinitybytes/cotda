<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use App\Models\PointAdjustment;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Livewire\Attributes\Validate;

class PointAdjustments extends Component
{
    use WithPagination;

    // Form properties
    #[Validate('required|exists:users,id')]
    public $selectedUser = '';
    
    #[Validate('required|integer|min:-100|max:100')]
    public $points = '';
    
    #[Validate('required|date')]
    public $adjustmentDate;
    
    #[Validate('required|in:bonus,penalty,correction,other')]
    public $type = 'bonus';
    
    #[Validate('required|string|max:255')]
    public $reason = '';
    
    #[Validate('nullable|string|max:1000')]
    public $notes = '';

    // Filter properties
    public $filterUser = '';
    public $filterType = '';
    public $filterDate = '';
    public $showFilters = false;

    public function mount()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }
        
        $this->adjustmentDate = now()->toDateString();
    }

    public function addAdjustment()
    {
        $this->validate();

        try {
            $adjustment = PointAdjustment::create([
                'user_id' => $this->selectedUser,
                'admin_id' => Auth::id(),
                'points' => $this->points,
                'adjustment_date' => $this->adjustmentDate,
                'reason' => $this->reason,
                'notes' => $this->notes,
                'type' => $this->type,
            ]);

            // Update user's total points
            $user = User::find($this->selectedUser);
            $user->increment('points', $this->points);

            $pointsText = $this->points > 0 ? "+{$this->points}" : $this->points;
            session()->flash('message', "Successfully added {$pointsText} points to {$user->name}'s account!");
            
            $this->resetForm();
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while adding the point adjustment. Please try again.');
        }
    }

    public function resetForm()
    {
        $this->selectedUser = '';
        $this->points = '';
        $this->reason = '';
        $this->notes = '';
        $this->type = 'bonus';
        $this->adjustmentDate = now()->toDateString();
        $this->resetValidation();
    }

    public function setQuickAdjustment($userId, $points, $type, $reason)
    {
        $this->selectedUser = $userId;
        $this->points = $points;
        $this->type = $type;
        $this->reason = $reason;
    }

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    public function clearFilters()
    {
        $this->filterUser = '';
        $this->filterType = '';
        $this->filterDate = '';
        $this->resetPage();
    }

    public function updatedFilterUser()
    {
        $this->resetPage();
    }

    public function updatedFilterType()
    {
        $this->resetPage();
    }

    public function updatedFilterDate()
    {
        $this->resetPage();
    }

    public function render()
    {
        // Build query for adjustments
        $query = PointAdjustment::with(['user', 'admin']);

        // Apply filters
        if ($this->filterUser) {
            $query->where('user_id', $this->filterUser);
        }
        
        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }
        
        if ($this->filterDate) {
            $query->whereDate('adjustment_date', $this->filterDate);
        }

        $adjustments = $query->latest('adjustment_date')
                           ->latest('created_at')
                           ->paginate(15);

        // Get users for dropdowns
        $users = User::where('role', 'user')->orderBy('name')->get();

        // Get quick stats
        $stats = [
            'total_adjustments' => PointAdjustment::count(),
            'today_adjustments' => PointAdjustment::whereDate('adjustment_date', now())->count(),
            'positive_today' => PointAdjustment::whereDate('adjustment_date', now())->where('points', '>', 0)->sum('points'),
            'negative_today' => PointAdjustment::whereDate('adjustment_date', now())->where('points', '<', 0)->sum('points'),
        ];

        return view('livewire.admin.point-adjustments', [
            'adjustments' => $adjustments,
            'users' => $users,
            'stats' => $stats,
        ]);
    }
}
