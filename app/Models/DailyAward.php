<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\UserBalance;

class DailyAward extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'award_date',
        'points_earned',
        'cash_amount',
        'notes',
    ];

    protected $casts = [
        'award_date' => 'date',
        'cash_amount' => 'decimal:2',
    ];

    /**
     * Get the awarded user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create daily award for top performer
     */
    public static function awardDailyWinner($date = null): ?self
    {
        $date = $date ?? now()->toDateString();

        // Find user with most points for the day
        $topUser = User::join('task_completions', 'users.id', '=', 'task_completions.user_id')
            ->join('task_assignments', 'task_completions.task_assignment_id', '=', 'task_assignments.id')
            ->join('tasks', 'task_assignments.task_id', '=', 'tasks.id')
            ->where('task_completions.verification_status', 'approved')
            ->whereDate('task_completions.completed_at', $date)
            ->where('users.role', 'user')
            ->selectRaw('users.id, users.name, SUM(tasks.points) as daily_points')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('daily_points')
            ->first();

        if (!$topUser || $topUser->daily_points == 0) {
            return null;
        }

        try {
            // Use firstOrCreate to prevent unique constraint violations
            $award = self::firstOrCreate(
                [
                    'award_date' => $date,
                ],
                [
                    'user_id' => $topUser->id,
                    'points_earned' => $topUser->daily_points,
                    'cash_amount' => 10.00,
                    'notes' => "Child of the Day with {$topUser->daily_points} points",
                ]
            );

            // Only update balance if this is a newly created award
            if ($award->wasRecentlyCreated) {
                // Update user balance
                $userBalance = UserBalance::firstOrCreate(['user_id' => $topUser->id]);
                $userBalance->addAward($award);
                
                return $award;
            } else {
                // Award already existed for today
                return null;
            }
        } catch (\Exception $e) {
            // Log the error and return null to prevent crashes
            \Log::error('Failed to create daily award: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Scope for recent awards
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('award_date', '>=', now()->subDays($days));
    }
}
