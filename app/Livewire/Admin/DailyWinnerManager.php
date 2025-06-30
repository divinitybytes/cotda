<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use App\Models\DailyAward;
use App\Models\UserBalance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Validate;

class DailyWinnerManager extends Component
{
    #[Validate('required|date')]
    public $selectedDate;
    
    public $topPerformers = [];
    public $existingAward = null;
    public $isAwarding = false;
    public $overrideWinner = null;
    public $showOverrideModal = false;
    
    #[Validate('nullable|string|max:500')]
    public $awardNotes = '';

    public function mount()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }
        
        $this->selectedDate = now()->toDateString();
        $this->loadDayData();
    }

    public function updatedSelectedDate()
    {
        $this->loadDayData();
    }

    public function loadDayData()
    {
        // Check if there's already an award for this date
        $this->existingAward = DailyAward::where('award_date', $this->selectedDate)
            ->with('user')
            ->first();

        // Get top performers for the selected date
        $this->topPerformers = User::join('task_completions', 'users.id', '=', 'task_completions.user_id')
            ->join('task_assignments', 'task_completions.task_assignment_id', '=', 'task_assignments.id')
            ->join('tasks', 'task_assignments.task_id', '=', 'tasks.id')
            ->where('task_completions.verification_status', 'approved')
            ->whereDate('task_completions.completed_at', $this->selectedDate)
            ->where('users.role', 'user')
            ->select('users.id', 'users.name', DB::raw('SUM(tasks.points) as daily_points'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('daily_points')
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function awardAutomaticWinner()
    {
        if (empty($this->topPerformers)) {
            session()->flash('error', 'No eligible users found for this date.');
            return;
        }

        $topUser = $this->topPerformers[0];
        $this->awardWinner($topUser['id'], "Child of the Day with {$topUser['daily_points']} points");
    }

    public function openOverrideModal()
    {
        $this->showOverrideModal = true;
        $this->overrideWinner = null;
        $this->awardNotes = '';
    }

    public function closeOverrideModal()
    {
        $this->showOverrideModal = false;
        $this->overrideWinner = null;
        $this->awardNotes = '';
        $this->resetValidation();
    }

    public function awardOverrideWinner()
    {
        $this->validate([
            'overrideWinner' => 'required|exists:users,id',
            'awardNotes' => 'nullable|string|max:500'
        ]);

        $user = User::find($this->overrideWinner);
        $userPoints = collect($this->topPerformers)->where('id', $this->overrideWinner)->first()['daily_points'] ?? 0;
        
        $notes = $this->awardNotes ?: "Admin override: Child of the Day with {$userPoints} points";
        
        $this->awardWinner($this->overrideWinner, $notes);
        $this->closeOverrideModal();
    }

    public function deleteAward()
    {
        if ($this->existingAward) {
            // Remove the award from user balance
            $userBalance = UserBalance::where('user_id', $this->existingAward->user_id)->first();
            if ($userBalance) {
                $userBalance->decrement('total_earned', $this->existingAward->cash_amount);
                $userBalance->decrement('current_balance', $this->existingAward->cash_amount);
                $userBalance->decrement('total_awards');
                $userBalance->updateVestedAmount();
            }

            $this->existingAward->delete();
            session()->flash('message', 'Daily award deleted successfully!');
            $this->loadDayData();
        }
    }

    private function awardWinner($userId, $notes)
    {
        $this->isAwarding = true;
        
        try {
            // Delete existing award if it exists
            if ($this->existingAward) {
                $this->deleteAward();
            }

            $userPoints = collect($this->topPerformers)->where('id', $userId)->first()['daily_points'] ?? 0;
            
            // Create new award
            $award = DailyAward::create([
                'user_id' => $userId,
                'award_date' => $this->selectedDate,
                'points_earned' => $userPoints,
                'cash_amount' => 5.00,
                'notes' => $notes,
            ]);

            // Update user balance
            $userBalance = UserBalance::firstOrCreate(['user_id' => $userId]);
            $userBalance->addAward($award);
            
            $user = User::find($userId);
            session()->flash('message', "Daily winner awarded! {$user->name} earned \${$award->cash_amount} for {$award->points_earned} points!");
            
            $this->loadDayData();
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while awarding the daily winner. Please try again.');
        } finally {
            $this->isAwarding = false;
        }
    }

    public function render()
    {
        $users = User::where('role', 'user')->orderBy('name')->get();
        
        return view('livewire.admin.daily-winner-manager', [
            'users' => $users,
        ]);
    }
} 