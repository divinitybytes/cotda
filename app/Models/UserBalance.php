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
     * Update vested amount based on individual award vesting periods
     */
    public function updateVestedAmount(): void
    {
        // Calculate vested amount by summing individual award vesting
        $totalVestedAmount = $this->calculateTotalVestedAmount();
        $this->update(['vested_amount' => $totalVestedAmount]);
    }

    /**
     * Calculate total vested amount from all individual awards
     */
    public function calculateTotalVestedAmount(): float
    {
        $dailyAwards = DailyAward::where('user_id', $this->user_id)->get();
        $totalVested = 0;

        foreach ($dailyAwards as $award) {
            $totalVested += $this->calculateAwardVestedAmount($award);
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
     * Get days until the next award becomes fully vested
     */
    public function getDaysUntilFullyVested(): int
    {
        $dailyAwards = DailyAward::where('user_id', $this->user_id)
            ->orderBy('award_date', 'asc')
            ->get();

        if ($dailyAwards->isEmpty()) {
            return 180;
        }

        foreach ($dailyAwards as $award) {
            $daysElapsed = $award->award_date->diffInDays(now());
            $vestingDays = 180;
            
            if ($daysElapsed < $vestingDays) {
                return $vestingDays - $daysElapsed;
            }
        }

        return 0; // All awards are fully vested
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
            // Delete all associated daily awards since they're being cashed out
            DailyAward::where('user_id', $this->user_id)->delete();
            
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

        $details = [];
        foreach ($dailyAwards as $award) {
            $daysElapsed = $award->award_date->diffInDays(now());
            $vestingDays = 180;
            $vestingPercentage = min(100, ($daysElapsed / $vestingDays) * 100);
            $vestedAmount = $this->calculateAwardVestedAmount($award);
            $unvestedAmount = $award->cash_amount - $vestedAmount;

            $details[] = [
                'award_date' => $award->award_date,
                'cash_amount' => $award->cash_amount,
                'days_elapsed' => $daysElapsed,
                'vesting_percentage' => $vestingPercentage,
                'vested_amount' => $vestedAmount,
                'unvested_amount' => $unvestedAmount,
                'days_until_fully_vested' => max(0, $vestingDays - $daysElapsed),
            ];
        }

        return $details;
    }
}
