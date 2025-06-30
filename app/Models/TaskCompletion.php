<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaskCompletion extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_assignment_id',
        'user_id',
        'photo_path',
        'completion_notes',
        'verification_status',
        'admin_notes',
        'verified_by',
        'verified_at',
        'completed_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the task assignment
     */
    public function taskAssignment(): BelongsTo
    {
        return $this->belongsTo(TaskAssignment::class);
    }

    /**
     * Get the task assignment (alias for convenience)
     */
    public function assignment(): BelongsTo
    {
        return $this->taskAssignment();
    }

    /**
     * Get the user who completed the task
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who verified the completion
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the admin who verified the completion (alias for convenience)
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->verifier();
    }

    /**
     * Scope for pending completions
     */
    public function scopePending($query)
    {
        return $query->where('verification_status', 'pending');
    }

    /**
     * Scope for approved completions
     */
    public function scopeApproved($query)
    {
        return $query->where('verification_status', 'approved');
    }

    /**
     * Scope for rejected completions
     */
    public function scopeRejected($query)
    {
        return $query->where('verification_status', 'rejected');
    }

    /**
     * Check if completion is pending
     */
    public function isPending(): bool
    {
        return $this->verification_status === 'pending';
    }

    /**
     * Check if completion is approved
     */
    public function isApproved(): bool
    {
        return $this->verification_status === 'approved';
    }

    /**
     * Check if completion is rejected
     */
    public function isRejected(): bool
    {
        return $this->verification_status === 'rejected';
    }

    /**
     * Approve the completion
     */
    public function approve(User $admin, string $adminNotes = null): void
    {
        $this->update([
            'verification_status' => 'approved',
            'verified_by' => $admin->id,
            'verified_at' => now(),
            'admin_notes' => $adminNotes,
        ]);

        // Add points to user and mark assignment as completed
        $task = $this->taskAssignment->task;
        $this->user->addPoints($task->points);
        $this->taskAssignment->markCompleted();
    }

    /**
     * Reject the completion
     */
    public function reject(User $admin, string $adminNotes = null): void
    {
        $this->update([
            'verification_status' => 'rejected',
            'verified_by' => $admin->id,
            'verified_at' => now(),
            'admin_notes' => $adminNotes,
        ]);
    }

    /**
     * Get photo URL
     */
    public function getPhotoUrlAttribute(): string
    {
        return asset('storage/' . $this->photo_path);
    }
}
