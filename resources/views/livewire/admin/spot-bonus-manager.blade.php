<div class="p-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">💰 Spot Bonus Manager</h1>
        <p class="text-gray-600 mt-1">Award one-time monetary bonuses that follow the same 6-month vesting schedule.</p>
    </div>

    <!-- Success/Error Messages -->
    @if (session()->has('message'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Award Button -->
    <div class="mb-6">
        <button wire:click="openAwardModal" 
                class="bg-yellow-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-yellow-700 transition-colors">
            💰 Award Spot Bonus
        </button>
    </div>

    <!-- Recent Bonuses -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b bg-gray-50">
            <h3 class="font-semibold text-gray-900">Recent Spot Bonuses</h3>
        </div>
        
        @if($recentBonuses->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Awarded By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recentBonuses as $bonus)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900">{{ $bonus->user->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-lg font-bold text-green-600">${{ number_format($bonus->cash_amount, 2) }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">{{ $bonus->reason }}</div>
                                    @if($bonus->notes)
                                        <div class="text-sm text-gray-500 mt-1">{{ $bonus->notes }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $bonus->bonus_date->format('M j, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $bonus->admin->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button wire:click="deleteBonus({{ $bonus->id }})" 
                                            wire:confirm="Are you sure you want to delete this spot bonus? This will remove it from the user's balance."
                                            class="text-red-600 hover:text-red-900">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="px-6 py-3 border-t">
                {{ $recentBonuses->links() }}
            </div>
        @else
            <div class="p-8 text-center text-gray-500">
                <div class="text-4xl mb-4">💰</div>
                <p class="text-lg font-medium">No spot bonuses awarded yet</p>
                <p class="text-sm">Click "Award Spot Bonus" to get started!</p>
            </div>
        @endif
    </div>

    <!-- Award Modal -->
    @if($showAwardModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-[9999]">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">💰 Award Spot Bonus</h3>
                
                <form wire:submit="awardBonus" class="space-y-4">
                    <!-- User Selection -->
                    <div>
                        <label for="selectedUserId" class="block text-sm font-medium text-gray-700 mb-1">
                            Select User
                        </label>
                        <select wire:model="selectedUserId" 
                                id="selectedUserId"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                            <option value="">Choose a user...</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        @error('selectedUserId') 
                            <span class="text-red-500 text-xs">{{ $message }}</span> 
                        @enderror
                    </div>

                    <!-- Bonus Amount -->
                    <div>
                        <label for="bonusAmount" class="block text-sm font-medium text-gray-700 mb-1">
                            Bonus Amount ($)
                        </label>
                        <input type="number" 
                               wire:model="bonusAmount" 
                               id="bonusAmount"
                               step="0.01" 
                               min="0.01" 
                               max="500"
                               placeholder="10.00"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                        @error('bonusAmount') 
                            <span class="text-red-500 text-xs">{{ $message }}</span> 
                        @enderror
                    </div>

                    <!-- Reason -->
                    <div>
                        <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">
                            Reason for Bonus
                        </label>
                        <input type="text" 
                               wire:model="reason" 
                               id="reason"
                               placeholder="e.g., Exceptional help with dishes"
                               maxlength="255"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                        @error('reason') 
                            <span class="text-red-500 text-xs">{{ $message }}</span> 
                        @enderror
                    </div>

                    <!-- Additional Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                            Additional Notes (Optional)
                        </label>
                        <textarea wire:model="notes" 
                                  id="notes"
                                  rows="3"
                                  placeholder="Any additional context..."
                                  maxlength="500"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"></textarea>
                        @error('notes') 
                            <span class="text-red-500 text-xs">{{ $message }}</span> 
                        @enderror
                    </div>

                    <!-- Vesting Info -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                        <p class="text-sm text-blue-800">
                            💡 This bonus will vest over 6 months (180 days) starting from today, 
                            following the same vesting schedule as daily awards.
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex space-x-3 pt-2">
                        <button type="submit" 
                                wire:loading.attr="disabled"
                                wire:target="awardBonus"
                                class="flex-1 bg-yellow-600 text-white py-2 rounded-lg font-semibold hover:bg-yellow-700 disabled:bg-yellow-400 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="awardBonus">Award Bonus</span>
                            <span wire:loading wire:target="awardBonus">Awarding...</span>
                        </button>
                        <button type="button" 
                                wire:click="closeAwardModal"
                                class="flex-1 bg-gray-500 text-white py-2 rounded-lg font-semibold hover:bg-gray-600">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div> 