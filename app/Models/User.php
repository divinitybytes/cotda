<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'points',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is a regular user
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Get the tasks created by this user (admin only)
     */
    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    /**
     * Get task assignments for this user
     */
    public function taskAssignments(): HasMany
    {
        return $this->hasMany(TaskAssignment::class);
    }

    /**
     * Get task completions for this user
     */
    public function taskCompletions(): HasMany
    {
        return $this->hasMany(TaskCompletion::class);
    }

    /**
     * Get daily awards for this user
     */
    public function dailyAwards(): HasMany
    {
        return $this->hasMany(DailyAward::class);
    }

    /**
     * Get spot bonuses for this user
     */
    public function spotBonuses(): HasMany
    {
        return $this->hasMany(SpotBonus::class);
    }

    /**
     * Get spot bonuses awarded by this admin
     */
    public function awardedSpotBonuses(): HasMany
    {
        return $this->hasMany(SpotBonus::class, 'admin_id');
    }

    /**
     * Get prize wheel spins for this user
     */
    public function prizeWheelSpins(): HasMany
    {
        return $this->hasMany(\App\Models\PrizeWheelSpin::class);
    }

    /**
     * Get user balance
     */
    public function balance(): HasOne
    {
        return $this->hasOne(UserBalance::class);
    }

    /**
     * Get pending task completions verified by this admin
     */
    public function verifiedCompletions(): HasMany
    {
        return $this->hasMany(TaskCompletion::class, 'verified_by');
    }

    /**
     * Get point adjustments for this user
     */
    public function pointAdjustments(): HasMany
    {
        return $this->hasMany(PointAdjustment::class);
    }

    /**
     * Get point adjustments made by this admin
     */
    public function madeAdjustments(): HasMany
    {
        return $this->hasMany(PointAdjustment::class, 'admin_id');
    }

    /**
     * Add points to user
     */
    public function addPoints(int $points): void
    {
        $this->increment('points', $points);
    }

    /**
     * Get user's ranking based on points today (including manual adjustments)
     */
    public function getTodayRanking(): int
    {
        $today = now()->toDateString();
        
        // Get all users and calculate their total points for today
        $usersWithPointsToday = collect();
        
        $allUsers = self::where('role', 'user')->get();
        
        foreach ($allUsers as $user) {
            // Get task completion points for today
            $taskPoints = TaskCompletion::where('task_completions.user_id', $user->id)
                ->where('verification_status', 'approved')
                ->whereDate('completed_at', $today)
                ->join('task_assignments', 'task_completions.task_assignment_id', '=', 'task_assignments.id')
                ->join('tasks', 'task_assignments.task_id', '=', 'tasks.id')
                ->sum('tasks.points');

            // Get manual adjustment points for today
            $adjustmentPoints = PointAdjustment::where('user_id', $user->id)
                ->whereDate('adjustment_date', $today)
                ->sum('points');

            $totalPoints = $taskPoints + $adjustmentPoints;
            
            if ($totalPoints > 0) {
                $usersWithPointsToday->put($user->id, $totalPoints);
            }
        }

        $userPoints = $usersWithPointsToday->get($this->id, 0);
        return $usersWithPointsToday->filter(fn($points) => $points > $userPoints)->count() + 1;
    }
}
