<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RecurringTaskService;

class CreateRecurringTaskAssignments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:create-recurring {--date= : Date to create assignments for (YYYY-MM-DD format)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create recurring task assignments for all users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = $this->option('date') ?? now()->toDateString();
        
        $this->info("Creating recurring task assignments for {$date}...");
        
        $created = RecurringTaskService::createRecurringAssignments($date);
        
        if ($created > 0) {
            $this->info("✅ Created {$created} recurring task assignments successfully!");
        } else {
            $this->info("ℹ️  No new recurring task assignments needed for {$date}");
        }

        // Optional: Clean up old assignments
        if ($this->confirm('Would you like to clean up old completed assignments? (keeps last 90 days)', false)) {
            $deleted = RecurringTaskService::cleanupOldAssignments();
            $this->info("🧹 Cleaned up {$deleted} old completed assignments");
        }

        return 0;
    }
} 