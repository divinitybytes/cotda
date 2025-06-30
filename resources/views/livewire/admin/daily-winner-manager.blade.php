<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b px-4 py-3">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Daily Winner Manager</h1>
                <p class="text-sm text-gray-600">Award "Child of the Day" and manage daily winners</p>
            </div>
            <a href="{{ route('dashboard') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                ← Back to Dashboard
            </a>
        </div>
    </div>

    <div class="p-4 space-y-6">
        <!-- Date Selection -->
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Select Date</h2>
                <div class="flex items-center space-x-2">
                    <button wire:click="$set('selectedDate', '{{ now()->subDay()->toDateString() }}')" 
                            class="bg-gray-100 text-gray-700 px-3 py-1 rounded text-sm">
                        Yesterday
                    </button>
                    <button wire:click="$set('selectedDate', '{{ now()->toDateString() }}')" 
                            class="bg-blue-100 text-blue-700 px-3 py-1 rounded text-sm">
                        Today
                    </button>
                </div>
            </div>
            
            <div class="flex items-center space-x-4">
                <input type="date" wire:model.live="selectedDate" 
                       class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <div class="text-sm text-gray-600">
                    Selected: {{ \Carbon\Carbon::parse($selectedDate)->format('l, F j, Y') }}
                </div>
            </div>
        </div>

        <!-- Existing Award Notice -->
        @if($existingAward)
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-start justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="text-2xl">🏆</div>
                        <div>
                            <h3 class="font-semibold text-green-800">Award Already Given</h3>
                            <p class="text-sm text-green-700">
                                <strong>{{ $existingAward->user->name }}</strong> was awarded ${{ number_format($existingAward->cash_amount, 2) }} 
                                for {{ $existingAward->points_earned }} points
                            </p>
                            @if($existingAward->notes)
                                <p class="text-xs text-green-600 mt-1">{{ $existingAward->notes }}</p>
                            @endif
                            <p class="text-xs text-green-500 mt-1">Awarded on {{ $existingAward->created_at->format('M j, Y g:i A') }}</p>
                        </div>
                    </div>
                    <button wire:click="deleteAward" 
                            wire:confirm="Are you sure you want to delete this award? This will also remove it from the user's balance."
                            class="bg-red-100 text-red-700 px-3 py-1 rounded text-sm font-semibold hover:bg-red-200">
                        Delete Award
                    </button>
                </div>
            </div>
        @endif

        <!-- Top Performers -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-4 border-b">
                <h2 class="text-lg font-semibold text-gray-900">Top Performers for {{ \Carbon\Carbon::parse($selectedDate)->format('F j, Y') }}</h2>
                <p class="text-sm text-gray-600">Based on approved task completions</p>
            </div>
            
            @if(empty($topPerformers))
                <div class="p-8 text-center text-gray-500">
                    <div class="text-4xl mb-2">📊</div>
                    <p>No completed tasks found for this date.</p>
                </div>
            @else
                <div class="space-y-0">
                    @foreach($topPerformers as $index => $performer)
                        <div class="p-4 border-b last:border-b-0 flex items-center justify-between
                                    {{ $index === 0 ? 'bg-yellow-50' : '' }}">
                            <div class="flex items-center space-x-3">
                                <div class="text-lg">
                                    @if($index === 0)
                                        🥇
                                    @elseif($index === 1)
                                        🥈
                                    @elseif($index === 2)
                                        🥉
                                    @else
                                        {{ $index + 1 }}.
                                    @endif
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900 {{ $index === 0 ? 'text-yellow-700' : '' }}">
                                        {{ $performer['name'] }}
                                        @if($index === 0)
                                            <span class="text-xs text-yellow-600 font-semibold">(Top Performer)</span>
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-600">{{ $performer['daily_points'] }} points earned</div>
                                </div>
                            </div>
                            
                            @if(!$existingAward && $index === 0)
                                <button wire:click="awardAutomaticWinner" 
                                        wire:loading.attr="disabled" 
                                        wire:target="awardAutomaticWinner"
                                        class="bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm font-semibold
                                               {{ $isAwarding ? 'bg-yellow-400 cursor-not-allowed' : 'hover:bg-yellow-700' }}">
                                    <span wire:loading.remove wire:target="awardAutomaticWinner">
                                        🏆 Award Winner
                                    </span>
                                    <span wire:loading wire:target="awardAutomaticWinner">
                                        Awarding...
                                    </span>
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Award Actions -->
        @if(!empty($topPerformers))
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="font-semibold text-gray-900 mb-4">Award Actions</h3>
                
                <div class="flex space-x-3">
                    @if(!$existingAward)
                        <button wire:click="awardAutomaticWinner" 
                                wire:loading.attr="disabled" 
                                wire:target="awardAutomaticWinner"
                                class="bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm font-semibold
                                       {{ $isAwarding ? 'bg-yellow-400 cursor-not-allowed' : 'hover:bg-yellow-700' }}">
                            <span wire:loading.remove wire:target="awardAutomaticWinner">
                                🏆 Award Top Performer
                            </span>
                            <span wire:loading wire:target="awardAutomaticWinner">
                                Awarding...
                            </span>
                        </button>
                    @endif
                    
                    <button wire:click="openOverrideModal" 
                            class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700">
                        ⚡ Override & Select Winner
                    </button>
                </div>
                
                @if(!$existingAward)
                    <p class="text-xs text-gray-500 mt-2">
                        The top performer will receive $5.00 and be designated as "Child of the Day"
                    </p>
                @endif
            </div>
        @endif
    </div>

    <!-- Override Modal -->
    @if($showOverrideModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
                <div class="p-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">Override Winner Selection</h3>
                    <p class="text-sm text-gray-600">Select a different user to receive the daily award</p>
                </div>
                
                <form wire:submit="awardOverrideWinner" class="p-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Winner</label>
                        <select wire:model="overrideWinner" 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Choose a user...</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->name }}
                                    @if($topPerformers && collect($topPerformers)->where('id', $user->id)->first())
                                        ({{ collect($topPerformers)->where('id', $user->id)->first()['daily_points'] }} pts today)
                                    @else
                                        (0 pts today)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('overrideWinner') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Award Notes (optional)</label>
                        <textarea wire:model="awardNotes" rows="3"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Reason for override or special recognition..."></textarea>
                        @error('awardNotes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                        <p class="text-sm text-yellow-800">
                            <strong>Note:</strong> This will award $5.00 to the selected user and designate them as "Child of the Day" for {{ \Carbon\Carbon::parse($selectedDate)->format('F j, Y') }}.
                            @if($existingAward)
                                This will replace the existing award.
                            @endif
                        </p>
                    </div>
                    
                    <div class="flex space-x-3 pt-4">
                        <button type="submit" 
                                wire:loading.attr="disabled"
                                wire:target="awardOverrideWinner"
                                class="flex-1 bg-blue-600 text-white rounded-lg py-2 px-4 font-semibold
                                       {{ $isAwarding ? 'bg-blue-400 cursor-not-allowed' : 'hover:bg-blue-700' }}">
                            <span wire:loading.remove wire:target="awardOverrideWinner">
                                Award Winner
                            </span>
                            <span wire:loading wire:target="awardOverrideWinner">
                                Awarding...
                            </span>
                        </button>
                        <button type="button" wire:click="closeOverrideModal"
                                class="flex-1 bg-gray-200 text-gray-800 rounded-lg py-2 px-4 font-semibold hover:bg-gray-300">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div> 