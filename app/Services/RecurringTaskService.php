<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RecurringTaskService
{
    /**
     * Create task assignments for all users' recurring tasks that need them
     */
    public static function createRecurringAssignments(?string $date = null): int
    {
        $date = $date ?? now()->toDateString();
        $created = 0;

        // Get all active recurring tasks
        $recurringTasks = Task::where('type', 'recurring')
            ->where('is_active', true)
            ->get();

        // Get all regular users
        $users = User::where('role', 'user')->get();

        foreach ($recurringTasks as $task) {
            foreach ($users as $user) {
                if (self::shouldCreateAssignment($task, $user, $date)) {
                    try {
                        TaskAssignment::create([
                            'task_id' => $task->id,
                            'user_id' => $user->id,
                            'assigned_date' => $date,
                            'due_date' => self::calculateDueDate($task, $date),
                        ]);
                        $created++;
                    } catch (\Exception $e) {
                        // Assignment already exists (unique constraint) or other error
                        Log::debug("Assignment already exists or error: {$task->title} for {$user->name} on {$date}");
                    }
                }
            }
        }

        if ($created > 0) {
            Log::info("Created {$created} recurring task assignments for {$date}");
        }

        return $created;
    }

    /**
     * Create assignments for a specific user
     */
    public static function createUserRecurringAssignments(User $user, ?string $date = null): int
    {
        $date = $date ?? now()->toDateString();
        $created = 0;

        // Get all active recurring tasks
        $recurringTasks = Task::where('type', 'recurring')
            ->where('is_active', true)
            ->get();

        foreach ($recurringTasks as $task) {
            if (self::shouldCreateAssignment($task, $user, $date)) {
                try {
                    TaskAssignment::create([
                        'task_id' => $task->id,
                        'user_id' => $user->id,
                        'assigned_date' => $date,
                        'due_date' => self::calculateDueDate($task, $date),
                    ]);
                    $created++;
                } catch (\Exception $e) {
                    // Assignment already exists or other error
                    Log::debug("Assignment already exists: {$task->title} for {$user->name} on {$date}");
                }
            }
        }

        return $created;
    }

    /**
     * Check if we should create an assignment for this task/user/date combination
     */
    private static function shouldCreateAssignment(Task $task, User $user, string $date): bool
    {
        // Check if assignment already exists for this date
        $existingAssignment = TaskAssignment::where('task_id', $task->id)
            ->where('user_id', $user->id)
            ->where('assigned_date', $date)
            ->exists();

        if ($existingAssignment) {
            return false;
        }

        // For recurring tasks, check if this user should get this task based on frequency
        return self::shouldAssignBasedOnFrequency($task, $user, $date);
    }

    /**
     * Determine if a task should be assigned based on its frequency
     */
    private static function shouldAssignBasedOnFrequency(Task $task, User $user, string $date): bool
    {
        $assignmentDate = Carbon::parse($date);
        
        // Check if user has ever been assigned this task
        $lastAssignment = TaskAssignment::where('task_id', $task->id)
            ->where('user_id', $user->id)
            ->latest('assigned_date')
            ->first();

        switch ($task->recurring_frequency) {
            case 'daily':
                // Daily tasks should be assigned every day
                return true;

            case 'weekly':
                // Weekly tasks should be assigned if:
                // 1. Never been assigned before, OR
                // 2. It's been a week or more since last assignment
                if (!$lastAssignment) {
                    return true;
                }

                return $assignmentDate->diffInDays($lastAssignment->assigned_date) >= 7;

            case 'monthly':
                // Monthly tasks should be assigned if:
                // 1. Never been assigned before, OR
                // 2. It's been a month or more since last assignment
                if (!$lastAssignment) {
                    return true;
                }

                return $assignmentDate->diffInDays($lastAssignment->assigned_date) >= 30;

            default:
                return false;
        }
    }

    /**
     * Calculate due date for a recurring task assignment
     */
    private static function calculateDueDate(Task $task, string $assignedDate): ?string
    {
        if ($task->due_date) {
            return $task->due_date;
        }

        $assigned = Carbon::parse($assignedDate);

        switch ($task->recurring_frequency) {
            case 'daily':
                // Daily tasks due same day
                return $assigned->toDateString();

            case 'weekly':
                // Weekly tasks due in 7 days
                return $assigned->addDays(7)->toDateString();

            case 'monthly':
                // Monthly tasks due in 30 days
                return $assigned->addDays(30)->toDateString();

            default:
                return null;
        }
    }

    /**
     * Clean up old completed assignments (optional - prevents database bloat)
     */
    public static function cleanupOldAssignments(int $daysToKeep = 90): int
    {
        $cutoffDate = now()->subDays($daysToKeep)->toDateString();
        
        $deleted = TaskAssignment::where('is_completed', true)
            ->where('assigned_date', '<', $cutoffDate)
            ->delete();

        if ($deleted > 0) {
            Log::info("Cleaned up {$deleted} old completed task assignments");
        }

        return $deleted;
    }
} 