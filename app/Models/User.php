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
     * Add points to user
     */
    public function addPoints(int $points): void
    {
        $this->increment('points', $points);
    }

    /**
     * Get user's ranking based on points today
     */
    public function getTodayRanking(): int
    {
        $today = now()->toDateString();
        
        $usersWithPointsToday = self::join('task_completions', 'users.id', '=', 'task_completions.user_id')
            ->join('task_assignments', 'task_completions.task_assignment_id', '=', 'task_assignments.id')
            ->join('tasks', 'task_assignments.task_id', '=', 'tasks.id')
            ->where('task_completions.verification_status', 'approved')
            ->whereDate('task_completions.completed_at', $today)
            ->selectRaw('users.id, SUM(tasks.points) as daily_points')
            ->groupBy('users.id')
            ->orderByDesc('daily_points')
            ->pluck('daily_points', 'users.id');

        $userPoints = $usersWithPointsToday->get($this->id, 0);
        return $usersWithPointsToday->filter(fn($points) => $points > $userPoints)->count() + 1;
    }
}
