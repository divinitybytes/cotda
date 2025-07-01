<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'points',
        'type',
        'recurring_frequency',
        'due_date',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the admin who created this task
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get task assignments
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(TaskAssignment::class);
    }

    /**
     * Scope for active tasks
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for recurring tasks
     */
    public function scopeRecurring($query)
    {
        return $query->where('type', 'recurring');
    }

    /**
     * Scope for one-time tasks
     */
    public function scopeOneTime($query)
    {
        return $query->where('type', 'one_time');
    }

    /**
     * Check if task is recurring
     */
    public function isRecurring(): bool
    {
        return $this->type === 'recurring';
    }

    /**
     * Check if task is one-time
     */
    public function isOneTime(): bool
    {
        return $this->type === 'one_time';
    }

    /**
     * Assign task to users
     */
    public function assignToUsers(array $userIds, $assignedDate = null, $dueDate = null): void
    {
        $assignedDate = \Carbon\Carbon::parse($assignedDate ?? now())->startOfDay()->format('Y-m-d 00:00:00');
        // Remove assignments for users not in the new list
        TaskAssignment::where('task_id', $this->id)
            ->where('assigned_date', $assignedDate)
            ->whereNotIn('user_id', $userIds)
            ->delete();
        foreach ($userIds as $userId) {
            TaskAssignment::updateOrCreate(
                [
                    'task_id' => $this->id,
                    'user_id' => $userId,
                    'assigned_date' => $assignedDate,
                ],
                [
                    'due_date' => $dueDate ?? $this->due_date,
                ]
            );
        }
    }
}
