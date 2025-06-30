<div class="min-h-screen bg-gradient-to-br from-purple-50 to-pink-100">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b px-4 py-3">
        <h1 class="text-xl font-bold text-gray-900">Rankings</h1>
        <p class="text-sm text-gray-600">See how you rank against others</p>
    </div>

    <div class="p-4 space-y-6">
        <!-- Your Achievements -->
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="font-semibold text-gray-900 mb-3">Your Achievements</h2>
            
            <div class="grid grid-cols-2 gap-3">
                <div class="text-center p-3 bg-blue-50 rounded-lg">
                    <div class="text-2xl mb-1">🏆</div>
                    <div class="text-lg font-bold text-blue-600">{{ $achievements['daily_awards'] }}</div>
                    <div class="text-xs text-gray-600">Daily Awards</div>
                </div>
                
                <div class="text-center p-3 bg-green-50 rounded-lg">
                    <div class="text-2xl mb-1">✅</div>
                    <div class="text-lg font-bold text-green-600">{{ $achievements['total_completions'] }}</div>
                    <div class="text-xs text-gray-600">Tasks Completed</div>
                </div>
                
                <div class="text-center p-3 bg-orange-50 rounded-lg">
                    <div class="text-2xl mb-1">🔥</div>
                    <div class="text-lg font-bold text-orange-600">{{ $achievements['streak_days'] }}</div>
                    <div class="text-xs text-gray-600">Day Streak</div>
                </div>
                
                <div class="text-center p-3 bg-purple-50 rounded-lg">
                    <div class="text-2xl mb-1">⭐</div>
                    <div class="text-lg font-bold text-purple-600">{{ $achievements['best_day_points'] }}</div>
                    <div class="text-xs text-gray-600">Best Day</div>
                </div>
            </div>
        </div>

        <!-- Time Filter -->
        <div class="flex bg-gray-100 rounded-lg p-1">
            <button wire:click="setTimeframe('today')" 
                    class="flex-1 py-2 px-3 rounded-md text-sm font-medium {{ $timeframe === 'today' ? 'bg-white text-gray-900 shadow' : 'text-gray-600' }}">
                Today
            </button>
            <button wire:click="setTimeframe('week')" 
                    class="flex-1 py-2 px-3 rounded-md text-sm font-medium {{ $timeframe === 'week' ? 'bg-white text-gray-900 shadow' : 'text-gray-600' }}">
                Week
            </button>
            <button wire:click="setTimeframe('month')" 
                    class="flex-1 py-2 px-3 rounded-md text-sm font-medium {{ $timeframe === 'month' ? 'bg-white text-gray-900 shadow' : 'text-gray-600' }}">
                Month
            </button>
            <button wire:click="setTimeframe('all_time')" 
                    class="flex-1 py-2 px-3 rounded-md text-sm font-medium {{ $timeframe === 'all_time' ? 'bg-white text-gray-900 shadow' : 'text-gray-600' }}">
                All Time
            </button>
        </div>

        <!-- Your Current Rank -->
        @if($currentUserRank)
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg p-4 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm opacity-90">Your Rank</div>
                        <div class="text-2xl font-bold">#{{ $currentUserRank->rank }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm opacity-90">Points</div>
                        <div class="text-2xl font-bold">{{ number_format($currentUserRank->total_points) }}</div>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-gray-100 rounded-lg p-4 text-center text-gray-600">
                <div class="text-4xl mb-2">🎯</div>
                <div class="font-medium">No points earned {{ $timeframe === 'today' ? 'today' : 'in this period' }} yet</div>
                <div class="text-sm">Complete some tasks to appear on the leaderboard!</div>
            </div>
        @endif

        <!-- Leaderboard -->
        @if($rankings->count() > 0)
            <div class="bg-white rounded-lg shadow">
                <div class="p-4 border-b">
                    <h3 class="font-semibold text-gray-900">
                        Leaderboard - {{ ucfirst(str_replace('_', ' ', $timeframe)) }}
                    </h3>
                </div>
                
                <div class="space-y-0">
                    @foreach($rankings->take(10) as $ranking)
                        <div class="p-4 border-b last:border-b-0 flex items-center justify-between {{ $ranking->id === auth()->id() ? 'bg-indigo-50' : '' }}">
                            <div class="flex items-center space-x-3">
                                <!-- Rank Badge -->
                                <div class="flex-shrink-0">
                                    @if($ranking->rank === 1)
                                        <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                            <span class="text-lg">🥇</span>
                                        </div>
                                    @elseif($ranking->rank === 2)
                                        <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                            <span class="text-lg">🥈</span>
                                        </div>
                                    @elseif($ranking->rank === 3)
                                        <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                                            <span class="text-lg">🥉</span>
                                        </div>
                                    @else
                                        <div class="w-8 h-8 bg-gray-50 rounded-full flex items-center justify-center">
                                            <span class="text-sm font-bold text-gray-600">#{{ $ranking->rank }}</span>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- User Info -->
                                <div>
                                    <div class="font-medium text-gray-900 {{ $ranking->id === auth()->id() ? 'text-indigo-600' : '' }}">
                                        {{ $ranking->name }}
                                        @if($ranking->id === auth()->id())
                                            <span class="text-xs text-indigo-500">(You)</span>
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-500">{{ number_format($ranking->total_points) }} points</div>
                                </div>
                            </div>
                            
                            <!-- Achievement Badge -->
                            @if($ranking->rank === 1)
                                <div class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs font-bold">
                                    👑 Leader
                                </div>
                            @elseif($ranking->rank <= 3)
                                <div class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-bold">
                                    Top 3
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="bg-white rounded-lg shadow p-8 text-center">
                <div class="text-6xl mb-4">🏆</div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No rankings yet</h3>
                <p class="text-gray-600">Complete some tasks to see the leaderboard!</p>
            </div>
        @endif

        <!-- Recent Child of the Day Awards -->
        @if($recentAwards->count() > 0)
            <div class="bg-white rounded-lg shadow">
                <div class="p-4 border-b">
                    <h3 class="font-semibold text-gray-900">Recent Winners</h3>
                    <p class="text-sm text-gray-600">Child of the Day awards</p>
                </div>
                
                <div class="space-y-0">
                    @foreach($recentAwards as $award)
                        <div class="p-4 border-b last:border-b-0 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="text-2xl">🏆</div>
                                <div>
                                    <div class="font-medium text-gray-900 {{ $award->user_id === auth()->id() ? 'text-yellow-600' : '' }}">
                                        {{ $award->user->name }}
                                        @if($award->user_id === auth()->id())
                                            <span class="text-xs text-yellow-500">(You!)</span>
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-500">{{ $award->award_date->format('M j, Y') }}</div>
                                </div>
                            </div>
                            
                            <div class="text-right">
                                <div class="font-bold text-green-600">+$5.00</div>
                                <div class="text-xs text-gray-500">{{ $award->points_earned }} pts</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Motivational Message -->
        <div class="bg-gradient-to-r from-pink-100 to-purple-100 rounded-lg p-4 text-center">
            <div class="text-2xl mb-2">🌟</div>
            @if($currentUserRank && $currentUserRank->rank === 1)
                <h4 class="font-semibold text-gray-900 mb-1">Congratulations, Champion! 🎉</h4>
                <p class="text-sm text-gray-700">You're currently in the lead! Keep up the great work!</p>
            @elseif($currentUserRank && $currentUserRank->rank <= 3)
                <h4 class="font-semibold text-gray-900 mb-1">You're in the Top 3! 🔥</h4>
                <p class="text-sm text-gray-700">Great job! A few more tasks and you could be #1!</p>
            @elseif($currentUserRank)
                <h4 class="font-semibold text-gray-900 mb-1">Keep Climbing! 💪</h4>
                <p class="text-sm text-gray-700">You're ranked #{{ $currentUserRank->rank }}. Complete more tasks to move up!</p>
            @else
                <h4 class="font-semibold text-gray-900 mb-1">Ready to Start? 🚀</h4>
                <p class="text-sm text-gray-700">Complete your first task to join the leaderboard!</p>
            @endif
        </div>
    </div>
</div>
