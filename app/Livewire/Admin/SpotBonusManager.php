<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use App\Models\SpotBonus;
use App\Models\UserBalance;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\WithPagination;

class SpotBonusManager extends Component
{
    use WithPagination;

    #[Validate('required|exists:users,id')]
    public $selectedUserId = '';
    
    #[Validate('required|numeric|min:0.01|max:500')]
    public $bonusAmount = '';
    
    #[Validate('required|string|max:255')]
    public $reason = '';
    
    #[Validate('nullable|string|max:500')]
    public $notes = '';
    
    public $showAwardModal = false;
    public $isAwarding = false;
    public $users = [];

    public function mount()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }
        
        $this->loadUsers();
    }

    public function loadUsers()
    {
        $this->users = User::where('role', 'user')
            ->orderBy('name')
            ->get();
    }

    public function openAwardModal()
    {
        $this->showAwardModal = true;
        $this->resetForm();
    }

    public function closeAwardModal()
    {
        $this->showAwardModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->selectedUserId = '';
        $this->bonusAmount = '';
        $this->reason = '';
        $this->notes = '';
        $this->resetValidation();
    }

    public function awardBonus()
    {
        $this->validate();
        
        $this->isAwarding = true;
        
        try {
            // Create the spot bonus
            $bonus = SpotBonus::create([
                'user_id' => $this->selectedUserId,
                'admin_id' => Auth::id(),
                'bonus_date' => now()->toDateString(),
                'cash_amount' => $this->bonusAmount,
                'reason' => $this->reason,
                'notes' => $this->notes,
            ]);

            // Update user balance using the existing vesting logic
            $userBalance = UserBalance::firstOrCreate(['user_id' => $this->selectedUserId]);
            $userBalance->addSpotBonus($bonus);
            
            $user = User::find($this->selectedUserId);
            session()->flash('message', "Spot bonus awarded! {$user->name} earned \${$bonus->cash_amount} for: {$bonus->reason}");
            
            $this->closeAwardModal();
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while awarding the spot bonus. Please try again.');
        } finally {
            $this->isAwarding = false;
        }
    }

    public function deleteBonus($bonusId)
    {
        $bonus = SpotBonus::findOrFail($bonusId);
        
        // Remove the bonus from user balance
        $userBalance = UserBalance::where('user_id', $bonus->user_id)->first();
        if ($userBalance) {
            $userBalance->removeSpotBonus($bonus);
        }

        $bonus->delete();
        session()->flash('message', 'Spot bonus deleted successfully!');
    }

    public function render()
    {
        $recentBonuses = SpotBonus::with(['user', 'admin'])
            ->latest('bonus_date')
            ->latest('created_at')
            ->paginate(20);

        return view('livewire.admin.spot-bonus-manager', [
            'recentBonuses' => $recentBonuses,
        ]);
    }
} 