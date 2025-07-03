<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\TaskCompletion;
use App\Models\PointAdjustment;
use App\Models\PrizeWheelSpin;

class RecalculateUserPoints extends Command
{
    protected $signature = 'users:recalculate-points {--dry-run : Show what would be changed without making changes}';
    protected $description = 'Recalculate user points from task completions, adjustments, and prize wheel spins';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
            $this->info('');
        }

        $users = User::where('role', 'user')->get();
        $totalCorrections = 0;
        $totalPointsChanged = 0;

        $this->info('Recalculating user points...');
        $this->info('');

        foreach ($users as $user) {
            // Calculate correct points from all sources
            $taskPoints = TaskCompletion::where('task_completions.user_id', $user->id)
                ->where('verification_status', 'approved')
                ->join('task_assignments', 'task_completions.task_assignment_id', '=', 'task_assignments.id')
                ->join('tasks', 'task_assignments.task_id', '=', 'tasks.id')
                ->sum('tasks.points');

            $adjustmentPoints = PointAdjustment::where('user_id', $user->id)
                ->sum('points');

            $prizeWheelPoints = PrizeWheelSpin::where('user_id', $user->id)
                ->sum('points_awarded');

            $correctTotal = $taskPoints + $adjustmentPoints + $prizeWheelPoints;
            $currentTotal = $user->points;
            $difference = $currentTotal - $correctTotal;

            if ($difference != 0) {
                $totalCorrections++;
                $totalPointsChanged += abs($difference);

                $status = $difference > 0 ? 'OVER-AWARDED' : 'UNDER-AWARDED';
                $this->warn("❌ {$user->name}: Current={$currentTotal}, Correct={$correctTotal}, Diff={$difference} ({$status})");
                $this->line("   Task Points: {$taskPoints}");
                $this->line("   Adjustments: {$adjustmentPoints}");
                $this->line("   Prize Wheel: {$prizeWheelPoints}");
                
                if (!$isDryRun) {
                    $user->update(['points' => $correctTotal]);
                    $this->info("   ✅ Fixed: Updated to {$correctTotal} points");
                }
                $this->line('');
            } else {
                $this->info("✅ {$user->name}: Correct ({$currentTotal} points)");
            }
        }

        $this->info('');
        $this->info('Summary:');
        $this->info("Users checked: {$users->count()}");
        $this->info("Users with incorrect points: {$totalCorrections}");
        $this->info("Total point discrepancy: {$totalPointsChanged}");

        if ($isDryRun) {
            $this->info('');
            $this->comment('Run without --dry-run to apply the corrections');
        } elseif ($totalCorrections > 0) {
            $this->info('');
            $this->comment('Point corrections have been applied successfully!');
        }

        return 0;
    }
} 