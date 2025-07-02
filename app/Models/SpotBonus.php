<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SpotBonus extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'admin_id',
        'bonus_date',
        'cash_amount',
        'reason',
        'notes',
    ];

    protected $casts = [
        'bonus_date' => 'date',
        'cash_amount' => 'decimal:2',
    ];

    /**
     * Get the user who received the bonus
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who awarded the bonus
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Scope for recent bonuses
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('bonus_date', '>=', now()->subDays($days));
    }
} 