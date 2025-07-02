<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class UserBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_earned',
        'vested_amount',
        'current_balance',
        'total_awards',
        'first_award_date',
    ];

    protected $casts = [
        'total_earned' => 'decimal:2',
        'vested_amount' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'first_award_date' => 'date',
    ];

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Add a daily award to the balance
     */
    public function addAward(DailyAward $award): void
    {
        $this->increment('total_earned', $award->cash_amount);
        $this->increment('total_awards');
        $this->increment('current_balance', $award->cash_amount);

        // Set first award date if this is the first award (kept for display purposes)
        if (!$this->first_award_date) {
            $this->update(['first_award_date' => $award->award_date]);
        }

        $this->updateVestedAmount();
    }

    /**
     * Add a spot bonus to the balance
     */
    public function addSpotBonus(SpotBonus $bonus): void
    {
        $this->increment('total_earned', $bonus->cash_amount);
        $this->increment('total_awards');
        $this->increment('current_balance', $bonus->cash_amount);

        // Set first award date if this is the first award (kept for display purposes)
        if (!$this->first_award_date) {
            $this->update(['first_award_date' => $bonus->bonus_date]);
        }

        $this->updateVestedAmount();
    }

    /**
     * Remove a spot bonus from the balance
     */
    public function removeSpotBonus(SpotBonus $bonus): void
    {
        $this->decrement('total_earned', $bonus->cash_amount);
        $this->decrement('total_awards');
        $this->decrement('current_balance', $bonus->cash_amount);
        
        $this->updateVestedAmount();
    }

    /**
     * Update vested amount based on individual award vesting periods
     */
    public function updateVestedAmount(): void
    {
        // Calculate vested amount by summing individual award vesting
        $totalVestedAmount = $this->calculateTotalVestedAmount();
        $this->update(['vested_amount' => $totalVestedAmount]);
    }

    /**
     * Calculate total vested amount from all individual awards and bonuses
     */
    public function calculateTotalVestedAmount(): float
    {
        $dailyAwards = DailyAward::where('user_id', $this->user_id)->get();
        $spotBonuses = SpotBonus::where('user_id', $this->user_id)->get();
        $totalVested = 0;

        foreach ($dailyAwards as $award) {
            $totalVested += $this->calculateAwardVestedAmount($award);
        }

        foreach ($spotBonuses as $bonus) {
            $totalVested += $this->calculateBonusVestedAmount($bonus);
        }

        return $totalVested;
    }

    /**
     * Calculate vested amount for a single award
     */
    public function calculateAwardVestedAmount(DailyAward $award): float
    {
        $daysElapsed = $award->award_date->diffInDays(now());
        $vestingDays = 180; // 6 months vesting period per award
        
        $vestingPercentage = min(100, ($daysElapsed / $vestingDays) * 100);
        return ($award->cash_amount * $vestingPercentage) / 100;
    }

    /**
     * Calculate vested amount for a single spot bonus
     */
    public function calculateBonusVestedAmount(SpotBonus $bonus): float
    {
        $daysElapsed = $bonus->bonus_date->diffInDays(now());
        $vestingDays = 180; // 6 months vesting period per bonus
        
        $vestingPercentage = min(100, ($daysElapsed / $vestingDays) * 100);
        return ($bonus->cash_amount * $vestingPercentage) / 100;
    }

    /**
     * Get early cash-out value (vested amount)
     */
    public function getEarlyCashOutValue(): float
    {
        $this->updateVestedAmount();
        return (float) $this->vested_amount;
    }

    /**
     * Get average vesting percentage across all awards
     */
    public function getVestingPercentage(): float
    {
        if ($this->current_balance <= 0) {
            return 0;
        }

        $totalVested = $this->calculateTotalVestedAmount();
        return min(100, ($totalVested / $this->current_balance) * 100);
    }

    /**
     * Get days until the next award/bonus becomes fully vested
     */
    public function getDaysUntilFullyVested(): int
    {
        $dailyAwards = DailyAward::where('user_id', $this->user_id)
            ->orderBy('award_date', 'asc')
            ->get();
        
        $spotBonuses = SpotBonus::where('user_id', $this->user_id)
            ->orderBy('bonus_date', 'asc')
            ->get();

        $allDates = [];
        
        foreach ($dailyAwards as $award) {
            $allDates[] = $award->award_date;
        }
        
        foreach ($spotBonuses as $bonus) {
            $allDates[] = $bonus->bonus_date;
        }

        if (empty($allDates)) {
            return 180;
        }

        sort($allDates);

        foreach ($allDates as $date) {
            $daysElapsed = $date->diffInDays(now());
            $vestingDays = 180;
            
            if ($daysElapsed < $vestingDays) {
                return $vestingDays - $daysElapsed;
            }
        }

        return 0; // All awards/bonuses are fully vested
    }

    /**
     * Check if all awards are fully vested
     */
    public function isFullyVested(): bool
    {
        return $this->getDaysUntilFullyVested() <= 0;
    }

    /**
     * Cash out vested amount and forfeit all remaining balance
     */
    public function cashOut(): float
    {
        $vestedAmount = $this->getEarlyCashOutValue();
        
        if ($vestedAmount > 0) {
            // User forfeits ALL balance and only gets the vested amount
            // Delete all associated daily awards and spot bonuses since they're being cashed out
            DailyAward::where('user_id', $this->user_id)->delete();
            SpotBonus::where('user_id', $this->user_id)->delete();
            
            // Reset all balance fields to 0
            $this->update([
                'current_balance' => 0,
                'vested_amount' => 0,
                'total_awards' => 0,
                'first_award_date' => null,
            ]);
        }

        return $vestedAmount;
    }

    /**
     * Get detailed breakdown of award vesting status
     */
    public function getAwardVestingDetails(): array
    {
        $dailyAwards = DailyAward::where('user_id', $this->user_id)
            ->orderBy('award_date', 'desc')
            ->get();

        $spotBonuses = SpotBonus::where('user_id', $this->user_id)
            ->orderBy('bonus_date', 'desc')
            ->get();

        $details = [];
        
        foreach ($dailyAwards as $award) {
            $daysElapsed = $award->award_date->diffInDays(now());
            $vestingDays = 180;
            $vestingPercentage = min(100, ($daysElapsed / $vestingDays) * 100);
            $vestedAmount = $this->calculateAwardVestedAmount($award);
            $unvestedAmount = $award->cash_amount - $vestedAmount;

            $details[] = [
                'type' => 'daily_award',
                'award_date' => $award->award_date,
                'cash_amount' => $award->cash_amount,
                'days_elapsed' => $daysElapsed,
                'vesting_percentage' => $vestingPercentage,
                'vested_amount' => $vestedAmount,
                'unvested_amount' => $unvestedAmount,
                'days_until_fully_vested' => max(0, $vestingDays - $daysElapsed),
                'description' => 'Child of the Day',
            ];
        }

        foreach ($spotBonuses as $bonus) {
            $daysElapsed = $bonus->bonus_date->diffInDays(now());
            $vestingDays = 180;
            $vestingPercentage = min(100, ($daysElapsed / $vestingDays) * 100);
            $vestedAmount = $this->calculateBonusVestedAmount($bonus);
            $unvestedAmount = $bonus->cash_amount - $vestedAmount;

            $details[] = [
                'type' => 'spot_bonus',
                'award_date' => $bonus->bonus_date,
                'cash_amount' => $bonus->cash_amount,
                'days_elapsed' => $daysElapsed,
                'vesting_percentage' => $vestingPercentage,
                'vested_amount' => $vestedAmount,
                'unvested_amount' => $unvestedAmount,
                'days_until_fully_vested' => max(0, $vestingDays - $daysElapsed),
                'description' => $bonus->reason,
            ];
        }

        // Sort by date descending
        usort($details, function($a, $b) {
            return $b['award_date']->compare($a['award_date']);
        });

        return $details;
    }
}
