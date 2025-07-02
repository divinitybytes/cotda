<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PrizeWheelSpin extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'spin_date',
        'prize_type',
        'prize_name',
        'points_awarded',
        'cash_awarded',
        'spot_bonus_id',
    ];

    protected $casts = [
        'spin_date' => 'date',
        'cash_awarded' => 'decimal:2',
    ];

    /**
     * Get the user who spun the wheel
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the associated spot bonus (if any)
     */
    public function spotBonus(): BelongsTo
    {
        return $this->belongsTo(SpotBonus::class);
    }

    /**
     * Check if user has spun today
     */
    public static function hasSpunToday(int $userId): bool
    {
        return self::where('user_id', $userId)
            ->whereDate('spin_date', now()->toDateString())
            ->exists();
    }

    /**
     * Get all available prizes with their probabilities
     */
    public static function getPrizeDefinitions(): array
    {
        return [
            [
                'name' => 'No Prize',
                'type' => 'none',
                'points' => 0,
                'cash' => 0,
                'probability' => 35, // 35% chance
                'color' => '#6B7280',
                'icon' => '😕'
            ],
            [
                'name' => '+10 Points',
                'type' => 'points',
                'points' => 10,
                'cash' => 0,
                'probability' => 25, // 25% chance
                'color' => '#3B82F6',
                'icon' => '⭐'
            ],
            [
                'name' => '+20 Points',
                'type' => 'points',
                'points' => 20,
                'cash' => 0,
                'probability' => 15, // 15% chance
                'color' => '#8B5CF6',
                'icon' => '🌟'
            ],
            [
                'name' => '+30 Points',
                'type' => 'points',
                'points' => 30,
                'cash' => 0,
                'probability' => 10, // 10% chance
                'color' => '#10B981',
                'icon' => '💫'
            ],
            [
                'name' => 'Whataburger Kids Meal',
                'type' => 'spot_bonus',
                'points' => 0,
                'cash' => 9.99,
                'probability' => 7, // 7% chance
                'color' => '#F59E0B',
                'icon' => '🍔'
            ],
            [
                'name' => '+$1 Spot Bonus',
                'type' => 'spot_bonus',
                'points' => 0,
                'cash' => 1.00,
                'probability' => 4, // 4% chance
                'color' => '#EF4444',
                'icon' => '💵'
            ],
            [
                'name' => 'Whataburger Shake',
                'type' => 'spot_bonus',
                'points' => 0,
                'cash' => 4.99,
                'probability' => 3, // 3% chance
                'color' => '#EC4899',
                'icon' => '🥤'
            ],
            [
                'name' => '+$5 Spot Bonus',
                'type' => 'spot_bonus',
                'points' => 0,
                'cash' => 5.00,
                'probability' => 1, // 1% chance
                'color' => '#84CC16',
                'icon' => '💰'
            ]
        ];
    }

    /**
     * Spin the wheel and determine prize
     */
    public static function spin(int $userId): array
    {
        $prizes = self::getPrizeDefinitions();
        
        // Generate random number between 1-100
        $random = rand(1, 100);
        $cumulative = 0;
        $wonPrize = null;
        
        // Find the winning prize based on probabilities
        foreach ($prizes as $prize) {
            $cumulative += $prize['probability'];
            if ($random <= $cumulative) {
                $wonPrize = $prize;
                break;
            }
        }
        
        // Fallback to "No Prize" if something goes wrong
        if (!$wonPrize) {
            $wonPrize = $prizes[0];
        }
        
        return $wonPrize;
    }
} 