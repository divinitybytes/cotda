<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\UserBalance;

class CashOutRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'status',
        'user_notes',
        'admin_notes',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    /**
     * Get the user who made the request
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who processed the request
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Scope for pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved requests
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for rejected requests
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Check if request is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if request is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if request is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Approve the cash out request
     */
    public function approve($adminId, $adminNotes = null): bool
    {
        if (!$this->isPending()) {
            return false;
        }

        // Process the actual cash out from user balance
        $userBalance = UserBalance::where('user_id', $this->user_id)->first();
        if (!$userBalance || $userBalance->getEarlyCashOutValue() < $this->amount) {
            return false;
        }

        // Perform the cash out
        $cashedOut = $userBalance->cashOut();
        
        // Update the request
        $this->update([
            'status' => 'approved',
            'admin_notes' => $adminNotes,
            'processed_by' => $adminId,
            'processed_at' => now(),
        ]);

        return true;
    }

    /**
     * Reject the cash out request
     */
    public function reject($adminId, $adminNotes = null): bool
    {
        if (!$this->isPending()) {
            return false;
        }

        $this->update([
            'status' => 'rejected',
            'admin_notes' => $adminNotes,
            'processed_by' => $adminId,
            'processed_at' => now(),
        ]);

        return true;
    }
}
