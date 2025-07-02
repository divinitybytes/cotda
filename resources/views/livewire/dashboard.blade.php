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
                
                <a href="{{ route('admin.daily-winner') }}" class="block bg-yellow-600 text-white rounded-lg p-4 text-center font-semibold hover:bg-yellow-700">
                    🏆 Manage Daily Winner
                </a>
                
                <a href="{{ route('admin.point-adjustments') }}" class="block bg-purple-600 text-white rounded-lg p-4 text-center font-semibold hover:bg-purple-700">
                    ⚡ Point Adjustments
                </a>
                
                <a href="{{ route('admin.spot-bonuses') }}" class="block bg-orange-600 text-white rounded-lg p-4 text-center font-semibold hover:bg-orange-700">
                    💰 Spot Bonuses
                </a>
                
                <a href="{{ route('admin.user-balances') }}" class="block bg-indigo-600 text-white rounded-lg p-4 text-center font-semibold hover:bg-indigo-700">
                    💳 User Balances
                </a>
                
                <button wire:click="createTodaysRecurringTasks" class="block w-full bg-teal-600 text-white rounded-lg p-4 text-center font-semibold hover:bg-teal-700">
                    🔄 Create Today's Recurring Tasks
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
                    @if($showConfetti)
                        <div class="bg-gradient-to-r from-yellow-400 to-orange-500 text-white px-3 py-2 rounded-full text-sm font-bold shadow-lg animate-pulse">
                            🏆 Child of the Day! 🎉
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
                <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-lg p-4 text-white shadow {{ $receivedSpotBonusToday ? 'spot-bonus-shimmer' : '' }}">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-semibold">
                            Cash Balance
                            @if($receivedSpotBonusToday)
                                <span class="text-yellow-300 ml-1">💰✨</span>
                            @endif
                        </h3>
                        <a href="{{ route('user.balance') }}" class="text-sm underline">View Details</a>
                    </div>
                    
                    @if($receivedSpotBonusToday)
                        <div class="bg-yellow-400 bg-opacity-20 rounded-lg p-2 mb-3 border border-yellow-300 border-opacity-30">
                            <div class="text-center text-sm font-medium text-yellow-100">
                                🎉 You received a Spot Bonus today! 🎉
                            </div>
                        </div>
                    @endif
                    
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
                    📝 Today's Tasks
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

    @if(!$isAdmin && $showConfetti)
        <!-- Confetti Animation for Daily Winner -->
        <div id="confetti-container" class="fixed inset-0 pointer-events-none z-50"></div>
        
        <!-- Celebration Message -->
        <div id="celebration-message" class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-50 bg-gradient-to-r from-yellow-400 to-orange-500 text-white px-8 py-6 rounded-2xl shadow-2xl text-center max-w-sm mx-4 opacity-0 scale-75 transition-all duration-500">
            <div class="text-4xl mb-2">🏆</div>
            <h3 class="text-xl font-bold mb-1">Congratulations!</h3>
            <p class="text-sm opacity-90">You're today's Child of the Day!</p>
            <p class="text-xs opacity-75 mt-2">You earned $10.00 for being awesome! 🎉</p>
        </div>
        
        <style>
            .confetti {
                position: absolute;
                background-color: #f0c14b;
                border-radius: 50%;
                opacity: 0.9;
                animation: confetti-fall linear infinite;
            }
            
            .confetti:nth-child(odd) { background-color: #ff6b6b; }
            .confetti:nth-child(3n) { background-color: #4ecdc4; }
            .confetti:nth-child(4n) { background-color: #45b7d1; }
            .confetti:nth-child(5n) { background-color: #96ceb4; }
            .confetti:nth-child(6n) { background-color: #ffeaa7; }
            .confetti:nth-child(7n) { background-color: #fd79a8; }
            .confetti:nth-child(8n) { background-color: #fdcb6e; }
            
            @keyframes confetti-fall {
                0% {
                    transform: translateY(-100vh) rotate(0deg);
                    opacity: 1;
                }
                100% {
                    transform: translateY(100vh) rotate(720deg);
                    opacity: 0;
                }
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Check if we've already shown confetti today
                const today = new Date().toDateString();
                const confettiShown = sessionStorage.getItem('confetti_shown_' + today);
                
                if (!confettiShown) {
                    // Wait a moment for the page to load, then start celebration
                    setTimeout(function() {
                        showCelebration();
                        // Mark confetti as shown for this session
                        sessionStorage.setItem('confetti_shown_' + today, 'true');
                    }, 500);
                }

                function showCelebration() {
                    // Show celebration message first
                    const message = document.getElementById('celebration-message');
                    if (message) {
                        message.style.opacity = '1';
                        message.style.transform = 'translate(-50%, -50%) scale(1)';
                        
                        // Hide message after 4 seconds
                        setTimeout(() => {
                            message.style.opacity = '0';
                            message.style.transform = 'translate(-50%, -50%) scale(0.75)';
                        }, 4000);
                    }
                    
                    // Start confetti slightly after message appears
                    setTimeout(() => {
                        createConfetti();
                    }, 300);
                }

                function createConfetti() {
                    const container = document.getElementById('confetti-container');
                    if (!container) return;
                    
                    const confettiCount = 100;
                    const colors = ['#f0c14b', '#ff6b6b', '#4ecdc4', '#45b7d1', '#96ceb4', '#ffeaa7', '#fd79a8', '#fdcb6e'];
                    
                    for (let i = 0; i < confettiCount; i++) {
                        setTimeout(() => {
                            const confetti = document.createElement('div');
                            confetti.className = 'confetti';
                            
                            // Random size between 4px and 12px
                            const size = Math.random() * 8 + 4;
                            confetti.style.width = size + 'px';
                            confetti.style.height = size + 'px';
                            
                            // Random horizontal position
                            confetti.style.left = Math.random() * 100 + '%';
                            
                            // Random color
                            confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                            
                            // Random animation duration between 2-4 seconds
                            const duration = Math.random() * 2 + 2;
                            confetti.style.animationDuration = duration + 's';
                            
                            // Random delay
                            confetti.style.animationDelay = Math.random() * 2 + 's';
                            
                            container.appendChild(confetti);
                            
                            // Remove confetti after animation
                            setTimeout(() => {
                                if (confetti.parentNode) {
                                    confetti.parentNode.removeChild(confetti);
                                }
                            }, (duration + 2) * 1000);
                        }, i * 50); // Stagger the creation
                    }
                    
                    // Clean up container after all animations
                    setTimeout(() => {
                        if (container) {
                            container.innerHTML = '';
                        }
                    }, 8000);
                }
            });
        </script>
    @endif
</div>
