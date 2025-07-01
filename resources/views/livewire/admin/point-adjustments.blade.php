<div class="min-h-screen bg-gradient-to-br from-purple-50 to-pink-100">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b px-4 py-3">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Point Adjustments</h1>
                <p class="text-sm text-gray-600">Add or deduct points for users</p>
            </div>
            <a href="{{ route('dashboard') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                ← Back to Dashboard
            </a>
        </div>
    </div>

    <div class="p-4 space-y-6">
        <!-- Quick Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $stats['total_adjustments'] }}</div>
                <div class="text-sm text-gray-600">Total Adjustments</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <div class="text-2xl font-bold text-purple-600">{{ $stats['today_adjustments'] }}</div>
                <div class="text-sm text-gray-600">Today</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <div class="text-2xl font-bold text-green-600">+{{ $stats['positive_today'] }}</div>
                <div class="text-sm text-gray-600">Points Added Today</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <div class="text-2xl font-bold text-red-600">{{ $stats['negative_today'] }}</div>
                <div class="text-sm text-gray-600">Points Deducted Today</div>
            </div>
        </div>

        <!-- Add/Deduct Points Form -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-4 border-b">
                <h2 class="text-lg font-semibold text-gray-900">Make Point Adjustment</h2>
                <p class="text-sm text-gray-600">Add bonus points or deduct for penalties</p>
            </div>

            <form wire:submit="addAdjustment" class="p-4 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- User Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Select User</label>
                        <select wire:model="selectedUser" 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            <option value="">Choose a user...</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->points }} pts)</option>
                            @endforeach
                        </select>
                        @error('selectedUser') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                        <input type="date" wire:model="adjustmentDate" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        @error('adjustmentDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Points -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Points</label>
                        <input type="number" wire:model="points" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                               placeholder="Enter positive or negative number" min="-100" max="100">
                        <p class="text-xs text-gray-500 mt-1">Use positive numbers to add points, negative to deduct</p>
                        @error('points') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select wire:model="type" 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            <option value="bonus">🎉 Bonus</option>
                            <option value="penalty">⚠️ Penalty</option>
                            <option value="correction">🔧 Correction</option>
                            <option value="other">📝 Other</option>
                        </select>
                        @error('type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Reason -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
                    <input type="text" wire:model="reason" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                           placeholder="e.g., Exceptional work on weekend, Late completion penalty, etc.">
                    @error('reason') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Additional Notes (optional)</label>
                    <textarea wire:model="notes" rows="3"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                              placeholder="Any additional details..."></textarea>
                    @error('notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Submit Button -->
                <div class="flex space-x-3 pt-4">
                    <button type="submit" 
                            class="flex-1 bg-purple-600 text-white rounded-lg py-2 px-4 font-semibold hover:bg-purple-700">
                        Add Point Adjustment
                    </button>
                    <button type="button" wire:click="resetForm"
                            class="flex-1 bg-gray-200 text-gray-800 rounded-lg py-2 px-4 font-semibold hover:bg-gray-300">
                        Clear Form
                    </button>
                </div>
            </form>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-4 border-b">
                <h3 class="font-semibold text-gray-900">Quick Actions</h3>
                <p class="text-sm text-gray-600">Common point adjustments</p>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    <button wire:click="setQuickAdjustment('', 5, 'bonus', 'Extra effort bonus')" 
                            class="bg-green-100 text-green-800 px-3 py-2 rounded text-sm font-medium hover:bg-green-200">
                        +5 Bonus
                    </button>
                    <button wire:click="setQuickAdjustment('', 10, 'bonus', 'Outstanding work')" 
                            class="bg-green-100 text-green-800 px-3 py-2 rounded text-sm font-medium hover:bg-green-200">
                        +10 Excellent
                    </button>
                    <button wire:click="setQuickAdjustment('', -5, 'penalty', 'Minor penalty')" 
                            class="bg-red-100 text-red-800 px-3 py-2 rounded text-sm font-medium hover:bg-red-200">
                        -5 Penalty
                    </button>
                    <button wire:click="setQuickAdjustment('', -10, 'penalty', 'Major penalty')" 
                            class="bg-red-100 text-red-800 px-3 py-2 rounded text-sm font-medium hover:bg-red-200">
                        -10 Penalty
                    </button>
                </div>
            </div>
        </div>

        <!-- Filter & History -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-4 border-b">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900">Adjustment History</h3>
                    <button wire:click="toggleFilters" 
                            class="bg-gray-100 text-gray-700 px-3 py-1 rounded text-sm font-medium hover:bg-gray-200">
                        {{ $showFilters ? '▼ Hide Filters' : '▶ Show Filters' }}
                    </button>
                </div>
            </div>

            <!-- Filters -->
            @if($showFilters)
                <div class="p-4 border-b bg-gray-50">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Filter by User</label>
                            <select wire:model.live="filterUser" 
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                <option value="">All Users</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Type</label>
                            <select wire:model.live="filterType" 
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                <option value="">All Types</option>
                                <option value="bonus">🎉 Bonus</option>
                                <option value="penalty">⚠️ Penalty</option>
                                <option value="correction">🔧 Correction</option>
                                <option value="other">📝 Other</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Date</label>
                            <input type="date" wire:model.live="filterDate" 
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button wire:click="clearFilters" class="text-sm text-gray-600 hover:text-gray-900">
                            Clear All Filters
                        </button>
                    </div>
                </div>
            @endif

            <!-- Adjustments List -->
            @if($adjustments->count() > 0)
                <div class="space-y-0">
                    @foreach($adjustments as $adjustment)
                        <div class="p-4 border-b last:border-b-0 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2 mb-1">
                                        <span class="text-lg">{{ $adjustment->type_emoji }}</span>
                                        <span class="font-medium text-gray-900">{{ $adjustment->user->name }}</span>
                                        <span class="px-2 py-1 rounded text-xs font-medium {{ $adjustment->type_color }}">
                                            {{ ucfirst($adjustment->type) }}
                                        </span>
                                    </div>
                                    
                                    <div class="text-sm text-gray-600 mb-1">
                                        <strong>Reason:</strong> {{ $adjustment->reason }}
                                    </div>
                                    
                                    @if($adjustment->notes)
                                        <div class="text-xs text-gray-500 mb-1">
                                            <strong>Notes:</strong> {{ $adjustment->notes }}
                                        </div>
                                    @endif
                                    
                                    <div class="text-xs text-gray-500">
                                        {{ $adjustment->adjustment_date->format('M j, Y') }} • 
                                        By {{ $adjustment->admin->name }} • 
                                        {{ $adjustment->created_at->diffForHumans() }}
                                    </div>
                                </div>
                                
                                <div class="text-right ml-4">
                                    <div class="text-lg font-bold {{ $adjustment->isPositive() ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $adjustment->formatted_points }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="p-4 border-t bg-gray-50">
                    {{ $adjustments->links() }}
                </div>
            @else
                <!-- Empty State -->
                <div class="p-8 text-center text-gray-500">
                    <div class="text-4xl mb-2">📊</div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No adjustments found</h3>
                    <p class="text-gray-600">
                        @if($filterUser || $filterType || $filterDate)
                            Try adjusting your filters to see more results.
                        @else
                            Point adjustments will appear here once you start making them.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if (session()->has('message'))
        <div class="fixed bottom-4 left-4 right-4 bg-green-500 text-white p-4 rounded-lg shadow-lg z-50">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="fixed bottom-4 left-4 right-4 bg-red-500 text-white p-4 rounded-lg shadow-lg z-50">
            {{ session('error') }}
        </div>
    @endif
</div>
