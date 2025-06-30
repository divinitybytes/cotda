<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <!-- Mobile Header -->
    <div class="bg-white shadow-sm border-b px-4 py-3">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">{{ $isAdmin ? 'Admin Dashboard' : 'My Dashboard' }}</h1>
                <p class="text-sm text-gray-600">{{ now()->format('l, F j') }}</p>
            </div>
            @if(!$isAdmin)
                <div class="text-right">
                    <div class="text-lg font-bold text-indigo-600">{{ $totalPoints }} pts</div>
                    <div class="text-xs text-gray-500">Rank #{{ $ranking }}</div>
                </div>
            @endif
        </div>
    </div>

    <div class="p-4 space-y-6">
        @if($isAdmin)
            <!-- Admin Dashboard -->
            <div class="grid grid-cols-2 gap-4">
                <!-- Quick Stats -->
                <div class="bg-white rounded-lg p-4 shadow">
                    <div class="text-2xl font-bold text-blue-600">{{ $totalUsers }}</div>
                    <div class="text-sm text-gray-600">Total Users</div>
                </div>
                
                <div class="bg-white rounded-lg p-4 shadow">
                    <div class="text-2xl font-bold text-green-600">{{ $totalTasks }}</div>
                    <div class="text-sm text-gray-600">Active Tasks</div>
                </div>
                
                <div class="bg-white rounded-lg p-4 shadow">
                    <div class="text-2xl font-bold text-orange-600">{{ $pendingVerifications }}</div>
                    <div class="text-sm text-gray-600">Pending Reviews</div>
                </div>
                
                <div class="bg-white rounded-lg p-4 shadow">
                    <div class="text-2xl font-bold text-green-600">{{ $pendingCashOuts }}</div>
                    <div class="text-sm text-gray-600">Cash Out Requests</div>
                </div>
                
                <div class="bg-white rounded-lg p-4 shadow">
                    <div class="text-2xl font-bold text-purple-600">{{ $todayCompletions }}</div>
                    <div class="text-sm text-gray-600">Today's Completions</div>
                </div>
            </div>

            <!-- Admin Actions -->
            <div class="space-y-3">
                <a href="{{ route('admin.tasks') }}" class="block bg-blue-600 text-white rounded-lg p-4 text-center font-semibold">
                    🎯 Manage Tasks
                </a>
                
                <a href="{{ route('admin.verify') }}" class="block bg-green-600 text-white rounded-lg p-4 text-center font-semibold relative">
                    ✅ Verify Completions
                    @if($pendingVerifications > 0)
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-6 w-6 flex items-center justify-center">{{ $pendingVerifications }}</span>
                    @endif
                </a>
                
                <a href="{{ route('admin.cash-out') }}" class="block bg-emerald-600 text-white rounded-lg p-4 text-center font-semibold relative">
                    💰 Cash Out Requests
                    @if($pendingCashOuts > 0)
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-6 w-6 flex items-center justify-center">{{ $pendingCashOuts }}</span>
                    @endif
                </a>
                
                <button wire:click="awardDailyWinner" 
                        wire:loading.attr="disabled" 
                        wire:target="awardDailyWinner"
                        class="w-full bg-yellow-600 text-white rounded-lg p-4 text-center font-semibold transition-all duration-200
                               {{ $isAwarding ? 'bg-yellow-400 cursor-not-allowed' : 'hover:bg-yellow-700' }}">
                    <span wire:loading.remove wire:target="awardDailyWinner">
                        🏆 Award Daily Winner
                    </span>
                    <span wire:loading wire:target="awardDailyWinner" class="flex items-center justify-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Awarding Winner...
                    </span>
                </button>
            </div>

            @if($recentCompletions->count() > 0)
                <!-- Recent Pending Completions -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-4 border-b">
                        <h3 class="font-semibold text-gray-900">Recent Submissions</h3>
                    </div>
                    <div class="space-y-0">
                        @foreach($recentCompletions as $completion)
                            <div class="p-4 border-b last:border-b-0 flex items-center justify-between">
                                <div>
                                    <div class="font-medium">{{ $completion->user->name }}</div>
                                    <div class="text-sm text-gray-600">{{ $completion->taskAssignment->task->title }}</div>
                                    <div class="text-xs text-gray-500">{{ $completion->completed_at->diffForHumans() }}</div>
                                </div>
                                <div class="text-orange-600 text-sm font-medium">Pending</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        @else
            <!-- User Dashboard -->
            
            <!-- Today's Stats -->
            <div class="bg-white rounded-lg p-4 shadow">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold text-gray-900">Today's Progress</h2>
                    @if($recentAward && $recentAward->award_date->isToday())
                        <div class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs font-bold">
                            🏆 Child of the Day!
                        </div>
                    @endif
                </div>
                
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div>
                        <div class="text-xl font-bold text-blue-600">{{ $todayPoints }}</div>
                        <div class="text-xs text-gray-600">Today's Points</div>
                    </div>
                    <div>
                        <div class="text-xl font-bold text-green-600">{{ $completedTasks }}</div>
                        <div class="text-xs text-gray-600">Completed</div>
                    </div>
                    <div>
                        <div class="text-xl font-bold text-orange-600">{{ $pendingTasks }}</div>
                        <div class="text-xs text-gray-600">Pending</div>
                    </div>
                </div>
            </div>

            <!-- Cash Balance (if exists) -->
            @if($balance)
                <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-lg p-4 text-white shadow">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-semibold">Cash Balance</h3>
                        <a href="{{ route('user.balance') }}" class="text-sm underline">View Details</a>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-2xl font-bold">${{ number_format($balance->current_balance, 2) }}</div>
                            <div class="text-sm opacity-90">Total Balance</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold">${{ number_format($balance->getEarlyCashOutValue(), 2) }}</div>
                            <div class="text-sm opacity-90">Vested ({{ number_format($balance->getVestingPercentage(), 1) }}%)</div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Quick Actions -->
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('user.tasks') }}" class="bg-blue-600 text-white rounded-lg p-4 text-center font-semibold relative">
                    📝 My Tasks
                    @if($pendingTasks > 0)
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-6 w-6 flex items-center justify-center">{{ $pendingTasks }}</span>
                    @endif
                </a>
                
                <a href="{{ route('user.rankings') }}" class="bg-purple-600 text-white rounded-lg p-4 text-center font-semibold">
                    🏆 Rankings
                </a>
            </div>

            @if($pendingAssignments->count() > 0)
                <!-- Pending Tasks -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-4 border-b">
                        <h3 class="font-semibold text-gray-900">Tasks To Do</h3>
                    </div>
                    <div class="space-y-0">
                        @foreach($pendingAssignments as $assignment)
                            <div class="p-4 border-b last:border-b-0">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="font-medium">{{ $assignment->task->title }}</div>
                                    <div class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-semibold">
                                        {{ $assignment->task->points }} pts
                                    </div>
                                </div>
                                
                                @if($assignment->task->description)
                                    <p class="text-sm text-gray-600 mb-2">{{ $assignment->task->description }}</p>
                                @endif
                                
                                <div class="flex items-center justify-between">
                                    <div class="text-xs text-gray-500">
                                        @if($assignment->due_date)
                                            Due: {{ $assignment->due_date->format('M j') }}
                                            @if($assignment->isOverdue())
                                                <span class="text-red-600 font-semibold">• Overdue</span>
                                            @endif
                                        @endif
                                    </div>
                                    
                                    @if($assignment->hasPendingCompletion())
                                        <span class="text-orange-600 text-xs font-medium">📸 Pending Review</span>
                                    @else
                                        <a href="{{ route('user.task.complete', $assignment->id) }}" class="bg-green-600 text-white px-3 py-1 rounded text-xs font-semibold">
                                            Complete
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($recentCompletions->count() > 0)
                <!-- Recent Completions -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-4 border-b">
                        <h3 class="font-semibold text-gray-900">Recent Completions</h3>
                    </div>
                    <div class="space-y-0">
                        @foreach($recentCompletions as $completion)
                            <div class="p-4 border-b last:border-b-0 flex items-center justify-between">
                                <div>
                                    <div class="font-medium">{{ $completion->taskAssignment->task->title }}</div>
                                    <div class="text-xs text-gray-500">{{ $completion->completed_at->diffForHumans() }}</div>
                                </div>
                                <div class="text-xs font-medium
                                    @if($completion->verification_status === 'approved') text-green-600
                                    @elseif($completion->verification_status === 'rejected') text-red-600
                                    @else text-orange-600 @endif">
                                    @if($completion->verification_status === 'approved') ✅ Approved
                                    @elseif($completion->verification_status === 'rejected') ❌ Rejected
                                    @else ⏳ Pending @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    </div>

    <!-- Success/Error Messages -->
    @if (session()->has('message'))
        <div class="fixed bottom-4 left-4 right-4 bg-green-500 text-white p-4 rounded-lg shadow-lg">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="fixed bottom-4 left-4 right-4 bg-red-500 text-white p-4 rounded-lg shadow-lg">
            {{ session('error') }}
        </div>
    @endif
</div>
