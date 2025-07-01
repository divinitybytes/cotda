<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PointAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'admin_id',
        'points',
        'adjustment_date',
        'reason',
        'notes',
        'type',
    ];

    protected $casts = [
        'adjustment_date' => 'date',
    ];

    /**
     * Get the user who received the point adjustment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who made the adjustment
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Check if this is a positive adjustment (bonus points)
     */
    public function isPositive(): bool
    {
        return $this->points > 0;
    }

    /**
     * Check if this is a negative adjustment (deduction)
     */
    public function isNegative(): bool
    {
        return $this->points < 0;
    }

    /**
     * Get the absolute value of points
     */
    public function getAbsolutePointsAttribute(): int
    {
        return abs($this->points);
    }

    /**
     * Get a formatted display of the point change
     */
    public function getFormattedPointsAttribute(): string
    {
        return ($this->points >= 0 ? '+' : '') . $this->points . ' pts';
    }

    /**
     * Scope for a specific date
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('adjustment_date', $date);
    }

    /**
     * Scope for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for recent adjustments
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('adjustment_date', '>=', now()->subDays($days));
    }

    /**
     * Get the color class for display based on type
     */
    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'bonus' => 'text-green-600 bg-green-100',
            'penalty' => 'text-red-600 bg-red-100',
            'correction' => 'text-blue-600 bg-blue-100',
            default => 'text-gray-600 bg-gray-100',
        };
    }

    /**
     * Get display emoji for the type
     */
    public function getTypeEmojiAttribute(): string
    {
        return match($this->type) {
            'bonus' => '🎉',
            'penalty' => '⚠️',
            'correction' => '🔧',
            default => '📝',
        };
    }
}
