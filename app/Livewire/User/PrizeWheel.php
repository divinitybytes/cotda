<?php

namespace App\Livewire\User;

use Livewire\Component;
use App\Models\User;
use App\Models\TaskCompletion;
use App\Models\PrizeWheelSpin;
use App\Models\SpotBonus;
use App\Models\UserBalance;
use App\Models\PointAdjustment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PrizeWheel extends Component
{
    public $showWheel = false;
    public $isSpinning = false;
    public $completedTasks = 0;
    public $canSpin = false;
    public $hasSpunToday = false;
    public $lastSpin = null;
    public $wonPrize = null;
    public $showPrizeModal = false;
    public $wheelRotation = 0;

    public function mount()
    {
        $this->loadUserStatus();
    }

    public function loadUserStatus()
    {
        $user = Auth::user();
        
        // Count completed tasks today
        $this->completedTasks = TaskCompletion::where('user_id', $user->id)
            ->where('verification_status', 'approved')
            ->whereDate('completed_at', now()->toDateString())
            ->count();
        
        // Check if user can spin
        $this->canSpin = $this->completedTasks >= 2;
        
        // Check if user has spun today
        $this->hasSpunToday = PrizeWheelSpin::hasSpunToday($user->id);
        
        // Get last spin if exists
        $this->lastSpin = PrizeWheelSpin::where('user_id', $user->id)
            ->whereDate('spin_date', now()->toDateString())
            ->first();
    }

    public function openWheel()
    {
        if (!$this->canSpin || $this->hasSpunToday) {
            return;
        }
        
        $this->showWheel = true;
    }

    public function closeWheel()
    {
        $this->showWheel = false;
        $this->wonPrize = null;
        $this->showPrizeModal = false;
        $this->isSpinning = false;
    }

    public function spinWheel()
    {
        if (!$this->canSpin || $this->hasSpunToday || $this->isSpinning) {
            return;
        }

        $user = Auth::user();
        $this->isSpinning = true;
        
        try {
            DB::transaction(function () use ($user) {
                // Determine the prize
                $wonPrize = PrizeWheelSpin::spin($user->id);
                $this->wonPrize = $wonPrize;
                
                // Calculate wheel rotation (simulate spinning)
                $this->wheelRotation = rand(1440, 2160); // 4-6 full rotations
                
                // Create spin record
                $spinData = [
                    'user_id' => $user->id,
                    'spin_date' => now()->toDateString(),
                    'prize_type' => $wonPrize['type'],
                    'prize_name' => $wonPrize['name'],
                    'points_awarded' => $wonPrize['points'] ?? 0,
                    'cash_awarded' => $wonPrize['cash'] ?? 0,
                ];
                
                // Award the prize
                if ($wonPrize['type'] === 'points' && isset($wonPrize['points']) && $wonPrize['points'] > 0) {
                    // Award points to user's total
                    $user->increment('points', $wonPrize['points']);
                    
                    // Find or create a system admin user for the point adjustment
                    $systemAdmin = User::where('role', 'admin')->first();
                    if (!$systemAdmin) {
                        $systemAdmin = User::where('id', 1)->first(); // Fallback to user ID 1
                    }
                    
                    // Create a point adjustment record so it shows up in today's balance
                    PointAdjustment::create([
                        'user_id' => $user->id,
                        'admin_id' => $systemAdmin ? $systemAdmin->id : 1,
                        'points' => $wonPrize['points'],
                        'adjustment_date' => now()->toDateString(),
                        'reason' => 'Prize Wheel: ' . $wonPrize['name'],
                        'notes' => 'Awarded via daily prize wheel spin',
                        'type' => 'bonus',
                    ]);
                    
                    logger('Prize wheel: Awarded ' . $wonPrize['points'] . ' points to user ' . $user->id);
                } elseif ($wonPrize['type'] === 'spot_bonus' && isset($wonPrize['cash']) && $wonPrize['cash'] > 0) {
                    // Find or create a system admin user for prize wheel bonuses
                    $systemAdmin = User::where('role', 'admin')->first();
                    if (!$systemAdmin) {
                        $systemAdmin = User::where('id', 1)->first(); // Fallback to user ID 1
                    }
                    
                    // Create spot bonus
                    $spotBonus = SpotBonus::create([
                        'user_id' => $user->id,
                        'admin_id' => $systemAdmin ? $systemAdmin->id : 1,
                        'bonus_date' => now()->toDateString(),
                        'cash_amount' => $wonPrize['cash'],
                        'reason' => 'Prize Wheel: ' . $wonPrize['name'],
                        'notes' => 'Awarded via daily prize wheel spin',
                    ]);
                    
                    // Update user balance
                    $userBalance = UserBalance::firstOrCreate(['user_id' => $user->id]);
                    $userBalance->addSpotBonus($spotBonus);
                    
                    $spinData['spot_bonus_id'] = $spotBonus->id;
                    logger('Prize wheel: Created spot bonus $' . $wonPrize['cash'] . ' for user ' . $user->id);
                }
                
                // Record the spin
                $spin = PrizeWheelSpin::create($spinData);
                $this->lastSpin = $spin;
                logger('Prize wheel: Recorded spin for user ' . $user->id . ', prize: ' . $wonPrize['name']);
            });
            
            // Update status
            $this->hasSpunToday = true;
            
            // Start the wheel animation immediately
            $this->dispatch('startWheelAnimation', $this->wheelRotation);
            
        } catch (\Exception $e) {
            // Reset spinning state on error
            $this->isSpinning = false;
            logger('Prize wheel error: ' . $e->getMessage());
            session()->flash('error', 'An error occurred while spinning the wheel: ' . $e->getMessage());
        }
    }

    public function showPrize()
    {
        $this->isSpinning = false;
        $this->showPrizeModal = true;
    }

    public function render()
    {
        $prizes = PrizeWheelSpin::getPrizeDefinitions();
        
        return view('livewire.user.prize-wheel', [
            'prizes' => $prizes,
        ]);
    }
} 