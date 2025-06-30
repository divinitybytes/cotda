<?php

namespace App\Livewire\User;

use Livewire\Component;
use App\Models\UserBalance as UserBalanceModel;
use App\Models\DailyAward;
use App\Models\CashOutRequest;
use Illuminate\Support\Facades\Auth;

class UserBalance extends Component
{
    public $balance;
    public $showCashOutModal = false;
    public $userNotes = '';
    public $pendingCashOutRequest;


    public function mount()
    {
        $this->loadBalance();
    }

    public function loadBalance()
    {
        $this->balance = UserBalanceModel::firstOrCreate([
            'user_id' => Auth::id()
        ]);
        
        // Update vested amount to reflect current status
        $this->balance->updateVestedAmount();
        
        // Check for pending cash out request
        $this->pendingCashOutRequest = CashOutRequest::where('user_id', Auth::id())
            ->pending()
            ->latest()
            ->first();
    }

    public function hideCashOutModal()
    {
        $this->showCashOutModal = false;
        $this->userNotes = '';
    }

    public function cashOut()
    {
        $vestedAmount = $this->balance->getEarlyCashOutValue();
        
        if ($vestedAmount <= 0) {
            session()->flash('error', 'No vested amount available for cash out.');
            $this->hideCashOutModal();
            return;
        }
        
        // Check if user already has a pending request
        if ($this->pendingCashOutRequest) {
            session()->flash('error', 'You already have a pending cash out request.');
            $this->hideCashOutModal();
            return;
        }
        
        // Create the cash out request
        CashOutRequest::create([
            'user_id' => Auth::id(),
            'amount' => $vestedAmount,
            'user_notes' => $this->userNotes,
        ]);
        
        session()->flash('message', "Cash out request submitted for $" . number_format($vestedAmount, 2) . ". An admin will review it shortly.");
        
        $this->userNotes = '';
        $this->loadBalance();
        $this->hideCashOutModal();
    }

    public function openCashOutModal()
    {
        $this->showCashOutModal = true;
    }

    public function render()
    {
        $recentAwards = DailyAward::where('user_id', Auth::id())
            ->with('user')
            ->latest('award_date')
            ->take(10)
            ->get();

        $monthlyEarnings = DailyAward::where('user_id', Auth::id())
            ->whereMonth('award_date', now()->month)
            ->whereYear('award_date', now()->year)
            ->sum('cash_amount');

        $yearlyEarnings = DailyAward::where('user_id', Auth::id())
            ->whereYear('award_date', now()->year)
            ->sum('cash_amount');

        $recentCashOutRequests = CashOutRequest::where('user_id', Auth::id())
            ->with('processedBy')
            ->latest()
            ->take(5)
            ->get();

        return view('livewire.user.user-balance', [
            'recentAwards' => $recentAwards,
            'monthlyEarnings' => $monthlyEarnings,
            'yearlyEarnings' => $yearlyEarnings,
            'recentCashOutRequests' => $recentCashOutRequests,
        ]);
    }
}
