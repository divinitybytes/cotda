<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use App\Models\UserBalance;
use App\Models\DailyAward;
use App\Models\SpotBonus;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class UserBalanceOverview extends Component
{
    use WithPagination;

    public $sortBy = 'current_balance';
    public $sortDirection = 'desc';
    public $search = '';

    public function mount()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }
    }

    public function sortByField($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'desc';
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $users = User::where('role', 'user')
            ->when($this->search, function($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->with(['balance'])
            ->get();

        // Create balance records for users who don't have one
        foreach ($users as $user) {
            if (!$user->balance) {
                UserBalance::create(['user_id' => $user->id]);
                $user->load('balance');
            }
            
            // Update vested amounts to current values
            if ($user->balance) {
                $user->balance->updateVestedAmount();
            }
        }

        // Apply sorting
        $users = $users->sortBy(function($user) {
            switch ($this->sortBy) {
                case 'name':
                    return $user->name;
                case 'current_balance':
                    return $user->balance->current_balance ?? 0;
                case 'vested_amount':
                    return $user->balance->vested_amount ?? 0;
                case 'vesting_percentage':
                    return $user->balance ? $user->balance->getVestingPercentage() : 0;
                case 'total_awards':
                    return $user->balance->total_awards ?? 0;
                case 'total_earned':
                    return $user->balance->total_earned ?? 0;
                default:
                    return $user->balance->current_balance ?? 0;
            }
        });

        if ($this->sortDirection === 'desc') {
            $users = $users->reverse();
        }

        // Calculate totals
        $totalCurrentBalance = $users->sum(function($user) {
            return $user->balance->current_balance ?? 0;
        });

        $totalVestedBalance = $users->sum(function($user) {
            return $user->balance->vested_amount ?? 0;
        });

        $totalUnvestedBalance = $totalCurrentBalance - $totalVestedBalance;

        $totalAwards = $users->sum(function($user) {
            return $user->balance->total_awards ?? 0;
        });

        return view('livewire.admin.user-balance-overview', [
            'users' => $users,
            'totalCurrentBalance' => $totalCurrentBalance,
            'totalVestedBalance' => $totalVestedBalance,
            'totalUnvestedBalance' => $totalUnvestedBalance,
            'totalAwards' => $totalAwards,
        ]);
    }
} 