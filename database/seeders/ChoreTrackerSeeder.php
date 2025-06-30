<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Task;
use App\Models\UserBalance;
use Illuminate\Support\Facades\Hash;

class ChoreTrackerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@chores.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Create regular users (children)
        $users = [];
        $userNames = ['Alice Smith', 'Bob Johnson', 'Charlie Brown', 'Dana Wilson'];
        
        foreach ($userNames as $index => $name) {
            $users[] = User::create([
                'name' => $name,
                'email' => strtolower(str_replace(' ', '.', $name)) . '@chores.test',
                'password' => Hash::make('password'),
                'role' => 'user',
                'email_verified_at' => now(),
                'points' => rand(0, 100),
            ]);
        }

        // Create user balances
        foreach ($users as $user) {
            UserBalance::create([
                'user_id' => $user->id,
                'total_earned' => rand(0, 50) * 5, // Random earnings between $0-$250
                'current_balance' => rand(0, 50) * 5,
                'vested_amount' => rand(0, 25) * 5, // Up to half vested
                'total_awards' => rand(0, 50),
                'first_award_date' => now()->subDays(rand(1, 365)),
            ]);
        }

        // Create sample tasks
        $tasks = [
            [
                'title' => 'Clean Your Room',
                'description' => 'Make bed, organize clothes, vacuum floor, and tidy desk area',
                'points' => 15,
                'type' => 'recurring',
                'recurring_frequency' => 'daily',
            ],
            [
                'title' => 'Take Out Trash',
                'description' => 'Empty all wastebaskets and take trash bins to curb',
                'points' => 10,
                'type' => 'recurring',
                'recurring_frequency' => 'weekly',
            ],
            [
                'title' => 'Load/Unload Dishwasher',
                'description' => 'Load dirty dishes and unload clean ones',
                'points' => 8,
                'type' => 'recurring',
                'recurring_frequency' => 'daily',
            ],
            [
                'title' => 'Feed Pets',
                'description' => 'Give food and fresh water to pets',
                'points' => 5,
                'type' => 'recurring',
                'recurring_frequency' => 'daily',
            ],
            [
                'title' => 'Vacuum Living Room',
                'description' => 'Vacuum the entire living room including under furniture',
                'points' => 12,
                'type' => 'recurring',
                'recurring_frequency' => 'weekly',
            ],
            [
                'title' => 'Wash Car',
                'description' => 'Wash, rinse, and dry the family car',
                'points' => 25,
                'type' => 'one_time',
                'due_date' => now()->addDays(7),
            ],
            [
                'title' => 'Organize Garage',
                'description' => 'Clean and organize the garage, sort items into categories',
                'points' => 35,
                'type' => 'one_time',
                'due_date' => now()->addDays(14),
            ],
            [
                'title' => 'Weed Garden',
                'description' => 'Remove weeds from flower beds and vegetable garden',
                'points' => 20,
                'type' => 'recurring',
                'recurring_frequency' => 'weekly',
            ],
        ];

        foreach ($tasks as $taskData) {
            $task = Task::create(array_merge($taskData, [
                'created_by' => $admin->id,
                'is_active' => true,
            ]));

            // Assign tasks to random users
            $randomUsers = collect($users)->random(rand(1, 3))->pluck('id')->toArray();
            $task->assignToUsers($randomUsers, now()->toDateString());
        }

        $this->command->info('Chore Tracker seeded successfully!');
        $this->command->info('Admin: admin@chores.test / password');
        $this->command->info('Users: alice.smith@chores.test, bob.johnson@chores.test, etc. / password');
    }
}
