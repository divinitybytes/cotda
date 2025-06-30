<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b px-4 py-3">
        <h1 class="text-xl font-bold text-gray-900">Cash Out Requests</h1>
        <p class="text-sm text-gray-600">Manage user cash out requests</p>
    </div>

    <div class="p-4 space-y-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg p-4 shadow">
                <div class="text-2xl font-bold text-orange-600">{{ $stats['pending'] }}</div>
                <div class="text-sm text-gray-600">Pending</div>
            </div>
            
            <div class="bg-white rounded-lg p-4 shadow">
                <div class="text-2xl font-bold text-green-600">{{ $stats['approved'] }}</div>
                <div class="text-sm text-gray-600">Approved</div>
            </div>
            
            <div class="bg-white rounded-lg p-4 shadow">
                <div class="text-2xl font-bold text-red-600">{{ $stats['rejected'] }}</div>
                <div class="text-sm text-gray-600">Rejected</div>
            </div>
            
            <div class="bg-white rounded-lg p-4 shadow">
                <div class="text-2xl font-bold text-blue-600">{{ $stats['total'] }}</div>
                <div class="text-sm text-gray-600">Total</div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="bg-white rounded-lg shadow">
            <div class="border-b border-gray-200">
                <nav class="flex space-x-8 px-4">
                    <button wire:click="setFilter('pending')" 
                            class="py-3 px-1 border-b-2 font-medium text-sm {{ $filter === 'pending' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                        Pending ({{ $stats['pending'] }})
                    </button>
                    <button wire:click="setFilter('approved')" 
                            class="py-3 px-1 border-b-2 font-medium text-sm {{ $filter === 'approved' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                        Approved ({{ $stats['approved'] }})
                    </button>
                    <button wire:click="setFilter('rejected')" 
                            class="py-3 px-1 border-b-2 font-medium text-sm {{ $filter === 'rejected' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                        Rejected ({{ $stats['rejected'] }})
                    </button>
                    <button wire:click="setFilter('all')" 
                            class="py-3 px-1 border-b-2 font-medium text-sm {{ $filter === 'all' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                        All ({{ $stats['total'] }})
                    </button>
                </nav>
            </div>

            <!-- Requests List -->
            <div class="divide-y divide-gray-200">
                @forelse($requests as $request)
                    <div class="p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3">
                                    <div class="font-medium text-gray-900">{{ $request->user->name }}</div>
                                    <div class="text-2xl font-bold 
                                        @if($request->status === 'pending') text-orange-600
                                        @elseif($request->status === 'approved') text-green-600
                                        @else text-red-600 @endif">
                                        ${{ number_format($request->amount, 2) }}
                                    </div>
                                </div>
                                
                                <div class="mt-1 text-sm text-gray-600">
                                    <span>Requested {{ $request->created_at->diffForHumans() }}</span>
                                    @if($request->processed_at)
                                        <span class="ml-2">• Processed {{ $request->processed_at->diffForHumans() }}</span>
                                        @if($request->processedBy)
                                            <span class="ml-1">by {{ $request->processedBy->name }}</span>
                                        @endif
                                    @endif
                                </div>
                                
                                @if($request->user_notes)
                                    <div class="mt-2 text-sm text-gray-700 bg-gray-50 rounded p-2">
                                        <strong>User Notes:</strong> {{ $request->user_notes }}
                                    </div>
                                @endif
                                
                                @if($request->admin_notes)
                                    <div class="mt-2 text-sm text-gray-700 bg-blue-50 rounded p-2">
                                        <strong>Admin Notes:</strong> {{ $request->admin_notes }}
                                    </div>
                                @endif
                            </div>
                            
                            <div class="flex items-center space-x-2 ml-4">
                                @if($request->status === 'pending')
                                    <button wire:click="showProcessModal({{ $request->id }}, 'approve')" 
                                            class="bg-green-600 text-white px-3 py-1 rounded text-sm font-medium">
                                        Approve
                                    </button>
                                    <button wire:click="showProcessModal({{ $request->id }}, 'reject')" 
                                            class="bg-red-600 text-white px-3 py-1 rounded text-sm font-medium">
                                        Reject
                                    </button>
                                @else
                                    <span class="px-3 py-1 rounded text-sm font-medium
                                        @if($request->status === 'approved') bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500">
                        <div class="text-4xl mb-2">💰</div>
                        <div class="font-medium">No {{ $filter === 'all' ? '' : $filter }} cash out requests found</div>
                        <div class="text-sm">Cash out requests will appear here when users submit them</div>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($requests->hasPages())
                <div class="px-4 py-3 border-t">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Process Request Modal -->
    @if($isModalVisible && $selectedRequest)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    {{ ucfirst($action) }} Cash Out Request
                </h3>
                
                <div class="mb-4">
                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                        <div class="font-medium">{{ $selectedRequest->user->name }}</div>
                        <div class="text-2xl font-bold text-green-600">${{ number_format($selectedRequest->amount, 2) }}</div>
                        <div class="text-sm text-gray-600">Requested {{ $selectedRequest->created_at->diffForHumans() }}</div>
                        
                        @if($selectedRequest->user_notes)
                            <div class="mt-2 text-sm text-gray-700">
                                <strong>User Notes:</strong> {{ $selectedRequest->user_notes }}
                            </div>
                        @endif
                    </div>
                    
                    <div class="mb-4">
                        <label for="adminNotes" class="block text-sm font-medium text-gray-700 mb-2">
                            Admin Notes (optional)
                        </label>
                        <textarea wire:model="adminNotes" 
                                  id="adminNotes"
                                  rows="3" 
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                                  placeholder="Add any notes about this decision..."></textarea>
                        @error('adminNotes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
                
                <div class="flex space-x-3">
                    <button wire:click="hideProcessModal" 
                            class="flex-1 bg-gray-200 text-gray-800 rounded-lg py-2 px-4 font-medium">
                        Cancel
                    </button>
                    <button wire:click="processRequest" 
                            class="flex-1 rounded-lg py-2 px-4 font-medium text-white
                                @if($action === 'approve') bg-green-600 @else bg-red-600 @endif">
                        {{ ucfirst($action) }}
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
