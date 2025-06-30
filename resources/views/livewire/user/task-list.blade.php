<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b px-4 py-3">
        <h1 class="text-xl font-bold text-gray-900">My Tasks</h1>
        <p class="text-sm text-gray-600">Complete tasks to earn points</p>
    </div>

    <div class="p-4">
        <!-- Stats Cards -->
        <div class="grid grid-cols-3 gap-3 mb-6">
            <div class="bg-white rounded-lg p-3 text-center shadow">
                <div class="text-lg font-bold text-blue-600">{{ $stats['pending'] }}</div>
                <div class="text-xs text-gray-600">Pending</div>
            </div>
            
            <div class="bg-white rounded-lg p-3 text-center shadow">
                <div class="text-lg font-bold text-green-600">{{ $stats['completed'] }}</div>
                <div class="text-xs text-gray-600">Completed</div>
            </div>
            
            <div class="bg-white rounded-lg p-3 text-center shadow">
                <div class="text-lg font-bold text-purple-600">{{ $stats['total_points'] }}</div>
                <div class="text-xs text-gray-600">Total Points</div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="flex bg-gray-100 rounded-lg p-1 mb-4">
            <button wire:click="setFilter('pending')" 
                    class="flex-1 py-2 px-3 rounded-md text-sm font-medium {{ $filter === 'pending' ? 'bg-white text-gray-900 shadow' : 'text-gray-600' }}">
                Pending
            </button>
            <button wire:click="setFilter('completed')" 
                    class="flex-1 py-2 px-3 rounded-md text-sm font-medium {{ $filter === 'completed' ? 'bg-white text-gray-900 shadow' : 'text-gray-600' }}">
                Completed
            </button>
            <button wire:click="setFilter('all')" 
                    class="flex-1 py-2 px-3 rounded-md text-sm font-medium {{ $filter === 'all' ? 'bg-white text-gray-900 shadow' : 'text-gray-600' }}">
                All
            </button>
        </div>

        <!-- Task List -->
        @if($assignments->count() > 0)
            <div class="space-y-3">
                @foreach($assignments as $assignment)
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-semibold text-gray-900">{{ $assignment->task->title }}</h3>
                            <div class="flex items-center space-x-2">
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-semibold">
                                    {{ $assignment->task->points }} pts
                                </span>
                                @if($assignment->is_completed)
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-semibold">
                                        ✓ Done
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if($assignment->task->description)
                            <p class="text-sm text-gray-600 mb-3">{{ $assignment->task->description }}</p>
                        @endif

                        <div class="flex items-center justify-between">
                            <div class="text-xs text-gray-500">
                                <div>Assigned: {{ $assignment->assigned_date->format('M j, Y') }}</div>
                                @if($assignment->due_date)
                                    <div class="{{ $assignment->isOverdue() ? 'text-red-600 font-semibold' : '' }}">
                                        Due: {{ $assignment->due_date->format('M j, Y') }}
                                        @if($assignment->isOverdue()) • Overdue @endif
                                    </div>
                                @endif
                                @if($assignment->task->type === 'recurring')
                                    <div class="text-blue-600">{{ ucfirst($assignment->task->recurring_frequency) }} recurring</div>
                                @endif
                            </div>

                            <div class="flex items-center space-x-2">
                                @if($assignment->completion)
                                    @if($assignment->completion->verification_status === 'pending')
                                        <span class="text-orange-600 text-xs font-medium">📸 Pending Review</span>
                                    @elseif($assignment->completion->verification_status === 'approved')
                                        <span class="text-green-600 text-xs font-medium">✅ Approved</span>
                                    @elseif($assignment->completion->verification_status === 'rejected')
                                        <span class="text-red-600 text-xs font-medium">❌ Rejected</span>
                                    @endif
                                @elseif(!$assignment->is_completed)
                                    <a href="{{ route('user.task.complete', $assignment->id) }}" 
                                       class="bg-green-600 text-white px-3 py-1 rounded text-xs font-semibold">
                                        Complete Task
                                    </a>
                                @endif
                            </div>
                        </div>

                        @if($assignment->completion && $assignment->completion->admin_notes)
                            <div class="mt-3 p-2 bg-yellow-50 border border-yellow-200 rounded">
                                <div class="text-xs font-semibold text-yellow-800">Admin Notes:</div>
                                <div class="text-xs text-yellow-700">{{ $assignment->completion->admin_notes }}</div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $assignments->links() }}
            </div>
        @else
            <div class="bg-white rounded-lg shadow p-8 text-center">
                <div class="text-6xl mb-4">📝</div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                    @if($filter === 'pending')
                        No pending tasks
                    @elseif($filter === 'completed')
                        No completed tasks yet
                    @else
                        No tasks assigned
                    @endif
                </h3>
                <p class="text-gray-600">
                    @if($filter === 'pending')
                        All caught up! Check back later for new tasks.
                    @elseif($filter === 'completed')
                        Complete some tasks to see them here.
                    @else
                        Ask an admin to assign some tasks to you.
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>
