<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaskAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'assigned_date',
        'due_date',
        'is_completed',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'due_date' => 'date',
        'is_completed' => 'boolean',
    ];

    /**
     * Get the task
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get the assigned user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the task completion
     */
    public function completion(): HasOne
    {
        return $this->hasOne(TaskCompletion::class);
    }

    /**
     * Check if task is overdue
     */
    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && !$this->is_completed;
    }

    /**
     * Check if task is completed
     */
    public function isCompleted(): bool
    {
        return $this->is_completed;
    }

    /**
     * Check if task has pending completion
     */
    public function hasPendingCompletion(): bool
    {
        return $this->completion && $this->completion->verification_status === 'pending';
    }

    /**
     * Mark as completed
     */
    public function markCompleted(): void
    {
        $this->update(['is_completed' => true]);
    }
}
