<div class="min-h-screen bg-gradient-to-br from-green-50 to-emerald-100">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b px-4 py-3">
        <h1 class="text-xl font-bold text-gray-900">My Balance</h1>
        <p class="text-sm text-gray-600">Track your earnings and vesting</p>
    </div>

    <div class="p-4 space-y-6">
        <!-- Main Balance Card -->
        <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-lg p-6 text-white shadow-lg">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">Cash Balance</h2>
                @if($balance->getDaysUntilFullyVested() > 0)
                    <div class="text-sm opacity-90">
                        {{ $balance->getDaysUntilFullyVested() }} days to vest
                    </div>
                @else
                    <div class="text-sm opacity-90 bg-white/20 px-2 py-1 rounded">
                        Fully Vested!
                    </div>
                @endif
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <div class="text-3xl font-bold">${{ number_format($balance->current_balance, 2) }}</div>
                    <div class="text-sm opacity-90">Total Balance</div>
                </div>
                <div>
                    <div class="text-3xl font-bold">${{ number_format($balance->getEarlyCashOutValue(), 2) }}</div>
                    <div class="text-sm opacity-90">Available Now</div>
                </div>
            </div>
        </div>

        <!-- Vesting Progress -->
        <div class="bg-white rounded-lg p-4 shadow">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-gray-900">Vesting Progress</h3>
                <span class="text-sm font-medium text-green-600">{{ number_format($balance->getVestingPercentage(), 1) }}%</span>
            </div>
            
            <div class="w-full bg-gray-200 rounded-full h-3 mb-3">
                <div class="bg-green-500 h-3 rounded-full transition-all duration-300" 
                     style="width: {{ $balance->getVestingPercentage() }}%"></div>
            </div>
            
            <div class="grid grid-cols-2 gap-4 text-sm text-gray-600">
                <div>
                    <div class="font-medium">${{ number_format($balance->vested_amount, 2) }}</div>
                    <div>Vested Amount</div>
                </div>
                <div>
                    <div class="font-medium">${{ number_format($balance->current_balance - $balance->vested_amount, 2) }}</div>
                    <div>Still Vesting</div>
                </div>
            </div>
        </div>

        <!-- Earnings Summary -->
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-white rounded-lg p-4 shadow text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $balance->total_awards }}</div>
                <div class="text-sm text-gray-600">Total Awards</div>
            </div>
            
            <div class="bg-white rounded-lg p-4 shadow text-center">
                <div class="text-2xl font-bold text-purple-600">${{ number_format($balance->total_earned, 2) }}</div>
                <div class="text-sm text-gray-600">Total Earned</div>
            </div>
        </div>

        <!-- Monthly/Yearly Stats -->
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-white rounded-lg p-4 shadow text-center">
                <div class="text-lg font-bold text-orange-600">${{ number_format($monthlyEarnings, 2) }}</div>
                <div class="text-sm text-gray-600">This Month</div>
            </div>
            
            <div class="bg-white rounded-lg p-4 shadow text-center">
                <div class="text-lg font-bold text-red-600">${{ number_format($yearlyEarnings, 2) }}</div>
                <div class="text-sm text-gray-600">This Year</div>
            </div>
        </div>

        <!-- Cash Out Section -->
        
        @if($pendingCashOutRequest)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-semibold text-yellow-900">⏳ Cash Out Request Pending</h3>
                    <span class="text-sm font-medium text-yellow-700">${{ number_format($pendingCashOutRequest->amount, 2) }}</span>
                </div>
                <div class="text-sm text-yellow-800">
                    <p>Submitted {{ $pendingCashOutRequest->created_at->diffForHumans() }}</p>
                    @if($pendingCashOutRequest->user_notes)
                        <p class="mt-1"><strong>Notes:</strong> {{ $pendingCashOutRequest->user_notes }}</p>
                    @endif
                </div>
            </div>
        @elseif($balance->getEarlyCashOutValue() > 0)
            <button wire:click="openCashOutModal" 
                    class="w-full bg-green-600 text-white rounded-lg p-4 font-semibold shadow-lg">
                💰 Cash Out ${{ number_format($balance->getEarlyCashOutValue(), 2) }}
            </button>
        @else
            <div class="bg-gray-100 rounded-lg p-4 text-center text-gray-600">
                No vested amount available for cash out yet
            </div>
        @endif

        <!-- Recent Awards -->
        @if($recentAwards->count() > 0)
            <div class="bg-white rounded-lg shadow">
                <div class="p-4 border-b">
                    <h3 class="font-semibold text-gray-900">Recent Awards</h3>
                </div>
                <div class="space-y-0">
                    @foreach($recentAwards as $award)
                        <div class="p-4 border-b last:border-b-0 flex items-center justify-between">
                            <div>
                                @if($award instanceof \App\Models\DailyAward)
                                    <div class="font-medium">🏆 Child of the Day</div>
                                    <div class="text-sm text-gray-600">{{ $award->award_date->format('M j, Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $award->points_earned }} points earned</div>
                                @else
                                    <div class="font-medium">💰 Spot Bonus</div>
                                    <div class="text-sm text-gray-600">{{ $award->bonus_date->format('M j, Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $award->reason }}</div>
                                @endif
                            </div>
                            <div class="text-lg font-bold text-green-600">
                                +${{ number_format($award->cash_amount, 2) }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Recent Cash Out Requests -->
        @if($recentCashOutRequests->count() > 0)
            <div class="bg-white rounded-lg shadow">
                <div class="p-4 border-b">
                    <h3 class="font-semibold text-gray-900">Cash Out History</h3>
                </div>
                <div class="space-y-0">
                    @foreach($recentCashOutRequests as $request)
                        <div class="p-4 border-b last:border-b-0">
                            <div class="flex items-center justify-between mb-2">
                                <div class="font-medium">${{ number_format($request->amount, 2) }}</div>
                                <span class="px-2 py-1 rounded text-xs font-medium
                                    @if($request->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($request->status === 'approved') bg-green-100 text-green-800
                                    @else bg-red-100 text-red-800 @endif">
                                    {{ ucfirst($request->status) }}
                                </span>
                            </div>
                            
                            <div class="text-sm text-gray-600">
                                <div>Requested {{ $request->created_at->diffForHumans() }}</div>
                                @if($request->processed_at)
                                    <div>Processed {{ $request->processed_at->diffForHumans() }}
                                        @if($request->processedBy)
                                            by {{ $request->processedBy->name }}
                                        @endif
                                    </div>
                                @endif
                            </div>
                            
                            @if($request->user_notes)
                                <div class="mt-2 text-xs text-gray-600 bg-gray-50 rounded p-2">
                                    <strong>Your Notes:</strong> {{ $request->user_notes }}
                                </div>
                            @endif
                            
                            @if($request->admin_notes)
                                <div class="mt-2 text-xs text-gray-600 bg-blue-50 rounded p-2">
                                    <strong>Admin Notes:</strong> {{ $request->admin_notes }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Vesting Explanation -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h4 class="font-semibold text-blue-900 mb-2">💡 How Vesting Works</h4>
            <div class="text-sm text-blue-800 space-y-1">
                <p>• Each award and bonus vests individually over 180 days (6 months)</p>
                <p>• Daily awards (🏆) are $10 for being Child of the Day</p>
                <p>• Spot bonuses (💰) are one-time awards from admins</p>
                <p>• You can cash out your total vested amount anytime</p>
                <p>• ⚠️ Cashing out forfeits ALL remaining unvested amounts</p>
                <p>• Awards continue vesting daily until fully vested</p>
                @if($balance->first_award_date)
                    <p>• Your first award was on {{ $balance->first_award_date->format('M j, Y') }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Cash Out Modal -->
    @if($showCashOutModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-[9998]">
            <div class="bg-white rounded-lg p-6 w-full max-w-md max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">⚠️ Cash Out Warning</h3>
                
                <div class="mb-4">
                    <div class="text-center mb-4">
                        <div class="text-3xl font-bold text-green-600 mb-2">
                            ${{ number_format($balance->getEarlyCashOutValue(), 2) }}
                        </div>
                        <div class="text-sm text-gray-600">
                            You will receive this amount ({{ number_format($balance->getVestingPercentage(), 1) }}% of total balance)
                        </div>
                    </div>
                    
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                        <div class="text-red-800 text-sm space-y-1">
                            <p class="font-semibold">⚠️ You will FORFEIT:</p>
                            <p class="text-lg font-bold text-red-600">
                                ${{ number_format($balance->current_balance - $balance->getEarlyCashOutValue(), 2) }}
                            </p>
                            <p>This unvested amount will be permanently lost!</p>
                        </div>
                    </div>

                    @php
                        $vestingDetails = $balance->getAwardVestingDetails();
                        $totalUnvested = collect($vestingDetails)->sum('unvested_amount');
                    @endphp

                    @if(count($vestingDetails) > 0)
                        <div class="bg-gray-50 rounded-lg p-3 mb-4 max-h-40 overflow-y-auto">
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">Award Breakdown:</h4>
                            <div class="space-y-2 text-xs">
                                @foreach($vestingDetails as $detail)
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <div class="font-medium">
                                                {{ $detail['award_date']->format('M j, Y') }}
                                                @if($detail['type'] === 'spot_bonus')
                                                    <span class="inline-block ml-1 text-orange-600">💰</span>
                                                @else
                                                    <span class="inline-block ml-1 text-yellow-600">🏆</span>
                                                @endif
                                            </div>
                                            <div class="text-gray-600">
                                                {{ number_format($detail['vesting_percentage'], 1) }}% vested
                                                @if($detail['type'] === 'spot_bonus')
                                                    • {{ Str::limit($detail['description'], 20) }}
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-green-600">${{ number_format($detail['vested_amount'], 2) }}</div>
                                            <div class="text-red-600">-${{ number_format($detail['unvested_amount'], 2) }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    <div class="mb-4">
                        <label for="userNotes" class="block text-sm font-medium text-gray-700 mb-2">
                            Notes (optional)
                        </label>
                        <textarea wire:model="userNotes" 
                                  id="userNotes"
                                  rows="3" 
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                                  placeholder="Any special instructions or notes for the admin..."></textarea>
                    </div>
                </div>
                
                <div class="flex space-x-3">
                    <button wire:click="hideCashOutModal" 
                            class="flex-1 bg-gray-200 text-gray-800 rounded-lg py-2 px-4 font-medium">
                        Cancel
                    </button>
                    <button wire:click="cashOut" 
                            class="flex-1 bg-red-600 text-white rounded-lg py-2 px-4 font-medium">
                        Cash Out & Forfeit
                    </button>
                </div>
            </div>
        </div>
    @endif

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
