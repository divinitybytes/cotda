<div class="p-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">💳 User Balance Overview</h1>
        <p class="text-gray-600 mt-1">Monitor all user balances, vesting status, and earnings.</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg p-4 shadow">
            <div class="text-2xl font-bold text-green-600">${{ number_format($totalCurrentBalance, 2) }}</div>
            <div class="text-sm text-gray-600">Total Balance</div>
        </div>
        
        <div class="bg-white rounded-lg p-4 shadow">
            <div class="text-2xl font-bold text-blue-600">${{ number_format($totalVestedBalance, 2) }}</div>
            <div class="text-sm text-gray-600">Total Vested</div>
        </div>
        
        <div class="bg-white rounded-lg p-4 shadow">
            <div class="text-2xl font-bold text-orange-600">${{ number_format($totalUnvestedBalance, 2) }}</div>
            <div class="text-sm text-gray-600">Total Unvested</div>
        </div>
        
        <div class="bg-white rounded-lg p-4 shadow">
            <div class="text-2xl font-bold text-purple-600">{{ number_format($totalAwards) }}</div>
            <div class="text-sm text-gray-600">Total Awards</div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
            <div class="flex-1">
                <input type="text" 
                       wire:model.live="search" 
                       placeholder="Search users by name or email..."
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div class="text-sm text-gray-600">
                {{ $users->count() }} user{{ $users->count() === 1 ? '' : 's' }}
            </div>
        </div>
    </div>

    <!-- User Balances Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortByField('name')" class="flex items-center space-x-1 hover:text-gray-700">
                                <span>User</span>
                                @if($sortBy === 'name')
                                    <span class="text-blue-500">
                                        {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                    </span>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortByField('current_balance')" class="flex items-center space-x-1 hover:text-gray-700">
                                <span>Total Balance</span>
                                @if($sortBy === 'current_balance')
                                    <span class="text-blue-500">
                                        {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                    </span>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortByField('vested_amount')" class="flex items-center space-x-1 hover:text-gray-700">
                                <span>Vested Amount</span>
                                @if($sortBy === 'vested_amount')
                                    <span class="text-blue-500">
                                        {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                    </span>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortByField('vesting_percentage')" class="flex items-center space-x-1 hover:text-gray-700">
                                <span>Vesting %</span>
                                @if($sortBy === 'vesting_percentage')
                                    <span class="text-blue-500">
                                        {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                    </span>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortByField('total_awards')" class="flex items-center space-x-1 hover:text-gray-700">
                                <span>Awards</span>
                                @if($sortBy === 'total_awards')
                                    <span class="text-blue-500">
                                        {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                    </span>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortByField('total_earned')" class="flex items-center space-x-1 hover:text-gray-700">
                                <span>Total Earned</span>
                                @if($sortBy === 'total_earned')
                                    <span class="text-blue-500">
                                        {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                    </span>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Award Breakdown
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                        @php
                            $balance = $user->balance;
                            $vestingPercentage = $balance ? $balance->getVestingPercentage() : 0;
                            $unvestedAmount = $balance ? ($balance->current_balance - $balance->vested_amount) : 0;
                            $dailyAwardsCount = $user->dailyAwards()->count();
                            $spotBonusesCount = $user->spotBonuses()->count();
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-lg font-bold text-gray-900">
                                    ${{ number_format($balance->current_balance ?? 0, 2) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-lg font-bold text-green-600">
                                    ${{ number_format($balance->vested_amount ?? 0, 2) }}
                                </div>
                                @if($unvestedAmount > 0)
                                    <div class="text-xs text-gray-500">
                                        ${{ number_format($unvestedAmount, 2) }} unvested
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-1">
                                        <div class="text-sm font-bold text-gray-900">
                                            {{ number_format($vestingPercentage, 1) }}%
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                            <div class="bg-green-500 h-2 rounded-full transition-all duration-300" 
                                                 style="width: {{ $vestingPercentage }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="text-lg font-bold text-purple-600">
                                    {{ $balance->total_awards ?? 0 }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-lg font-bold text-blue-600">
                                    ${{ number_format($balance->total_earned ?? 0, 2) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="space-y-1 text-sm">
                                    @if($dailyAwardsCount > 0)
                                        <div class="flex items-center text-yellow-600">
                                            <span class="mr-1">🏆</span>
                                            <span>{{ $dailyAwardsCount }} daily</span>
                                        </div>
                                    @endif
                                    @if($spotBonusesCount > 0)
                                        <div class="flex items-center text-orange-600">
                                            <span class="mr-1">💰</span>
                                            <span>{{ $spotBonusesCount }} spot</span>
                                        </div>
                                    @endif
                                    @if($dailyAwardsCount === 0 && $spotBonusesCount === 0)
                                        <div class="text-gray-400">No awards</div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                @if($search)
                                    <div class="text-lg font-medium">No users found matching "{{ $search }}"</div>
                                    <p class="text-sm">Try adjusting your search terms.</p>
                                @else
                                    <div class="text-lg font-medium">No users found</div>
                                    <p class="text-sm">Users will appear here once they're created.</p>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Additional Information -->
    @if($users->count() > 0)
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h4 class="font-semibold text-blue-900 mb-2">💡 Balance Overview Information</h4>
            <div class="text-sm text-blue-800 space-y-1">
                <p>• <strong>Total Balance:</strong> Current cash balance including both vested and unvested amounts</p>
                <p>• <strong>Vested Amount:</strong> Amount user can cash out immediately (forfeiting unvested balance)</p>
                <p>• <strong>Vesting %:</strong> Percentage of total balance that has vested (updates daily)</p>
                <p>• <strong>Awards:</strong> Total number of daily awards and spot bonuses received</p>
                <p>• <strong>🏆 Daily:</strong> Child of the Day awards ($10 each)</p>
                <p>• <strong>💰 Spot:</strong> Custom bonus awards from admins</p>
            </div>
        </div>
    @endif
</div> 