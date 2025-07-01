<div class="min-h-screen bg-gradient-to-br from-green-50 to-teal-100">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b px-4 py-3">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Task Verification</h1>
                <p class="text-sm text-gray-600">Review and approve task completions</p>
            </div>
            @if($stats['pending'] > 0)
                <div class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-semibold">
                    {{ $stats['pending'] }} pending
                </div>
            @endif
        </div>
    </div>

    <div class="p-4 space-y-6">
        <!-- Filter Tabs -->
        <div class="flex bg-gray-100 rounded-lg p-1">
            <button wire:click="setFilter('pending')" 
                    class="flex-1 py-2 px-3 rounded-md text-sm font-medium {{ $filter === 'pending' ? 'bg-white text-gray-900 shadow' : 'text-gray-600' }}">
                Pending ({{ $stats['pending'] }})
            </button>
            <button wire:click="setFilter('approved')" 
                    class="flex-1 py-2 px-3 rounded-md text-sm font-medium {{ $filter === 'approved' ? 'bg-white text-gray-900 shadow' : 'text-gray-600' }}">
                Approved
            </button>
            <button wire:click="setFilter('rejected')" 
                    class="flex-1 py-2 px-3 rounded-md text-sm font-medium {{ $filter === 'rejected' ? 'bg-white text-gray-900 shadow' : 'text-gray-600' }}">
                Rejected
            </button>
            <button wire:click="setFilter('all')" 
                    class="flex-1 py-2 px-3 rounded-md text-sm font-medium {{ $filter === 'all' ? 'bg-white text-gray-900 shadow' : 'text-gray-600' }}">
                All
            </button>
        </div>

        <!-- Sorting & Filtering Controls -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-4 border-b">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900">Sort & Filter</h3>
                    <div class="flex items-center space-x-2">
                        <button wire:click="clearFilters" class="text-sm text-gray-600 hover:text-gray-900">
                            Clear Filters
                        </button>
                        <button wire:click="toggleFilters" 
                                class="bg-gray-100 text-gray-700 px-3 py-1 rounded text-sm font-medium hover:bg-gray-200">
                            {{ $showFilters ? '▼ Hide Filters' : '▶ Show Filters' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Sort Options -->
            <div class="p-4 border-b bg-gray-50">
                <div class="flex flex-wrap gap-2">
                    <span class="text-sm font-medium text-gray-700 mr-2">Sort by:</span>
                    
                    <button wire:click="sortBy('completed_at')" 
                            class="px-2 py-1 rounded text-xs font-medium {{ $sortBy === 'completed_at' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Date Completed 
                        @if($sortBy === 'completed_at')
                            {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                        @endif
                    </button>
                    
                    <button wire:click="sortBy('user_name')" 
                            class="px-2 py-1 rounded text-xs font-medium {{ $sortBy === 'user_name' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        User Name 
                        @if($sortBy === 'user_name')
                            {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                        @endif
                    </button>
                    
                    <button wire:click="sortBy('task_title')" 
                            class="px-2 py-1 rounded text-xs font-medium {{ $sortBy === 'task_title' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Task Name 
                        @if($sortBy === 'task_title')
                            {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                        @endif
                    </button>
                    
                    <button wire:click="sortBy('points')" 
                            class="px-2 py-1 rounded text-xs font-medium {{ $sortBy === 'points' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Points 
                        @if($sortBy === 'points')
                            {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                        @endif
                    </button>
                    
                    <button wire:click="sortBy('verification_status')" 
                            class="px-2 py-1 rounded text-xs font-medium {{ $sortBy === 'verification_status' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Status 
                        @if($sortBy === 'verification_status')
                            {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                        @endif
                    </button>
                    
                    <button wire:click="sortBy('verified_at')" 
                            class="px-2 py-1 rounded text-xs font-medium {{ $sortBy === 'verified_at' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Verified Date 
                        @if($sortBy === 'verified_at')
                            {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                        @endif
                    </button>
                </div>
            </div>

            <!-- Quick Filter Presets -->
            @if($showFilters)
                <div class="p-4 border-b bg-blue-50">
                    <div class="flex flex-wrap gap-2">
                        <span class="text-sm font-medium text-gray-700 mr-2">Quick Filters:</span>
                        
                        <button wire:click="$set('dateFrom', '{{ now()->toDateString() }}')" 
                                class="px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200">
                            Today
                        </button>
                        
                        <button wire:click="$set('dateFrom', '{{ now()->subDays(7)->toDateString() }}')" 
                                class="px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200">
                            Last 7 Days
                        </button>
                        
                        <button wire:click="$set('filterLateOnly', true)" 
                                class="px-2 py-1 rounded text-xs font-medium bg-orange-100 text-orange-800 hover:bg-orange-200">
                            Late Submissions
                        </button>
                        
                        <button wire:click="$set('filterWithPhoto', 'without')" 
                                class="px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800 hover:bg-red-200">
                            Missing Photos
                        </button>
                        
                        <button wire:click="$set('minPoints', 20)" 
                                class="px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800 hover:bg-green-200">
                            High Value (20+ pts)
                        </button>
                    </div>
                </div>
            @endif

            <!-- Filter Options -->
            @if($showFilters)
                <div class="p-4 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- User Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">User</label>
                            <select wire:model.live="filterUser" 
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Users</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Task Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Task</label>
                            <select wire:model.live="filterTask" 
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Tasks</option>
                                @foreach($tasks as $task)
                                    <option value="{{ $task->id }}">{{ $task->title }} ({{ $task->points }} pts)</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Task Type Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Task Type</label>
                            <select wire:model.live="filterTaskType" 
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Types</option>
                                <option value="one_time">One-time</option>
                                <option value="recurring">Recurring</option>
                            </select>
                        </div>

                        <!-- Date From -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                            <input type="date" wire:model.live="dateFrom" 
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Date To -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                            <input type="date" wire:model.live="dateTo" 
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Photo Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Photo Evidence</label>
                            <select wire:model.live="filterWithPhoto" 
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Submissions</option>
                                <option value="with">With Photo</option>
                                <option value="without">Without Photo</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Points Range -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Min Points</label>
                            <input type="number" wire:model.live="minPoints" 
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="0" min="0">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Max Points</label>
                            <input type="number" wire:model.live="maxPoints" 
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="100" min="0">
                        </div>

                        <!-- Late Submissions Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Special Filters</label>
                            <label class="flex items-center">
                                <input type="checkbox" wire:model.live="filterLateOnly" 
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Late submissions only</span>
                            </label>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Quick Actions (only for pending) -->
        @if($filter === 'pending' && $completions->count() > 0)
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="font-semibold text-gray-900 mb-3">Quick Actions</h3>
                <div class="flex space-x-2">
                    <button wire:click="approveAll" 
                            onclick="return confirm('Are you sure you want to approve all pending completions?')"
                            class="bg-green-600 text-white px-4 py-2 rounded text-sm font-semibold">
                        ✅ Approve All
                    </button>
                    <button wire:click="showBulkRejectModal" 
                            class="bg-red-600 text-white px-4 py-2 rounded text-sm font-semibold">
                        ❌ Bulk Reject
                    </button>
                </div>
            </div>
        @endif

        <!-- Results Info -->
        @if($completions->count() > 0)
            <div class="bg-gray-50 rounded-lg p-3">
                <div class="flex items-center justify-between text-sm text-gray-600">
                    <div>
                        Showing {{ $completions->count() }} of {{ $completions->total() }} results
                        @if($completions->hasPages())
                            (Page {{ $completions->currentPage() }} of {{ $completions->lastPage() }})
                        @endif
                    </div>
                    
                    <!-- Active Filters Display -->
                    @if($filterUser || $filterTask || $dateFrom || $dateTo || $filterTaskType || $filterLateOnly || $filterWithPhoto || $minPoints || $maxPoints)
                        <div class="flex flex-wrap gap-1">
                            <span class="text-xs text-gray-500">Filters:</span>
                            @if($filterUser)
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                                    User: {{ $users->find($filterUser)->name ?? 'Unknown' }}
                                </span>
                            @endif
                            @if($filterTask)
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                                    Task: {{ $tasks->find($filterTask)->title ?? 'Unknown' }}
                                </span>
                            @endif
                            @if($dateFrom || $dateTo)
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                                    Date: {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('M j') : 'Start' }} - {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('M j') : 'End' }}
                                </span>
                            @endif
                            @if($filterTaskType)
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                                    Type: {{ ucfirst($filterTaskType) }}
                                </span>
                            @endif
                            @if($filterLateOnly)
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">Late Only</span>
                            @endif
                            @if($filterWithPhoto)
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                                    {{ ucfirst($filterWithPhoto) }} Photo
                                </span>
                            @endif
                            @if($minPoints || $maxPoints)
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                                    Points: {{ $minPoints ?: '0' }}-{{ $maxPoints ?: '∞' }}
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Task Completions List -->
        @if($completions->count() > 0)
            <div class="space-y-4">
                @foreach($completions as $completion)
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <!-- Header -->
                        <div class="p-4 border-b">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ $completion->assignment->task->title }}</h3>
                                    <div class="text-sm text-gray-600">
                                        Completed by <span class="font-medium">{{ $completion->assignment->user->name }}</span>
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-2">
                                    <!-- Status Badge -->
                                    @if($completion->verification_status === 'pending')
                                        <span class="bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs font-semibold">
                                            📸 Pending
                                        </span>
                                    @elseif($completion->verification_status === 'approved')
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-semibold">
                                            ✅ Approved
                                        </span>
                                    @else
                                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-semibold">
                                            ❌ Rejected
                                        </span>
                                    @endif
                                    
                                    <!-- Points -->
                                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-semibold">
                                        {{ $completion->assignment->task->points }} pts
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-4">
                            <div class="grid md:grid-cols-2 gap-4">
                                <!-- Photo -->
                                <div>
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Photo Evidence</h4>
                                    @if($completion->photo_path)
                                        <div class="relative">
                                            <img src="{{ asset('storage/' . $completion->photo_path) }}" 
                                                 alt="Task completion photo" 
                                                 class="w-full h-48 object-cover rounded-lg border"
                                                 wire:click="showPhotoModal('{{ asset('storage/' . $completion->photo_path) }}')">
                                            <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-10 rounded-lg cursor-pointer flex items-center justify-center">
                                                <span class="text-white opacity-0 hover:opacity-100 text-sm font-medium">Click to expand</span>
                                            </div>
                                        </div>
                                    @else
                                        <div class="w-full h-48 bg-gray-100 rounded-lg border flex items-center justify-center">
                                            <span class="text-gray-500">No photo uploaded</span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Details -->
                                <div class="space-y-3">
                                    <!-- Task Description -->
                                    @if($completion->assignment->task->description)
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-700 mb-1">Task Description</h4>
                                            <p class="text-sm text-gray-600">{{ $completion->assignment->task->description }}</p>
                                        </div>
                                    @endif

                                    <!-- Completion Notes -->
                                    @if($completion->completion_notes)
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-700 mb-1">User Notes</h4>
                                            <p class="text-sm text-gray-600">{{ $completion->completion_notes }}</p>
                                        </div>
                                    @endif

                                    <!-- Admin Notes (if exists) -->
                                    @if($completion->admin_notes)
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-700 mb-1">Admin Notes</h4>
                                            <p class="text-sm text-gray-600">{{ $completion->admin_notes }}</p>
                                        </div>
                                    @endif

                                    <!-- Timestamps -->
                                    <div class="text-xs text-gray-500 space-y-1">
                                        <div>Submitted: {{ $completion->completed_at->format('M j, Y g:i A') }}</div>
                                        @if($completion->verified_at)
                                            <div>Verified: {{ $completion->verified_at->format('M j, Y g:i A') }}</div>
                                            @if($completion->verified_by)
                                                <div>By: {{ $completion->verifiedBy->name }}</div>
                                            @endif
                                        @endif
                                        @if($completion->assignment->due_date && $completion->completed_at > $completion->assignment->due_date)
                                            <div class="text-red-600 font-semibold">⚠️ Submitted late</div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Actions (only for pending) -->
                            @if($completion->verification_status === 'pending')
                                <div class="mt-4 pt-4 border-t">
                                    <div class="flex flex-col space-y-3">
                                        <!-- Admin Notes Input -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Admin Notes (optional)</label>
                                            <textarea wire:model="adminNotes.{{ $completion->id }}" 
                                                      class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                                                      rows="2" 
                                                      placeholder="Add notes for the user..."></textarea>
                                        </div>

                                        <!-- Action Buttons -->
                                        <div class="flex space-x-3">
                                            <button wire:click="approve({{ $completion->id }})" 
                                                    class="flex-1 bg-green-600 text-white rounded py-2 px-4 font-semibold">
                                                ✅ Approve & Award Points
                                            </button>
                                            <button wire:click="reject({{ $completion->id }})" 
                                                    class="flex-1 bg-red-600 text-white rounded py-2 px-4 font-semibold">
                                                ❌ Reject
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $completions->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-lg shadow p-8 text-center">
                <div class="text-6xl mb-4">
                    @if($filter === 'pending')
                        📸
                    @elseif($filter === 'approved')
                        ✅
                    @elseif($filter === 'rejected')
                        ❌
                    @else
                        🔍
                    @endif
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                    @if($filter === 'pending')
                        No pending completions
                    @elseif($filter === 'approved')
                        No approved completions yet
                    @elseif($filter === 'rejected')
                        No rejected completions yet
                    @else
                        No completions found
                    @endif
                </h3>
                <p class="text-gray-600">
                    @if($filter === 'pending')
                        All submissions have been reviewed!
                    @else
                        Check back later for new submissions.
                    @endif
                </p>
            </div>
        @endif
    </div>

    <!-- Photo Modal -->
    @if($showPhotoModal)
        <div class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center p-4 z-50" 
             wire:click="hidePhotoModal">
            <div class="max-w-4xl max-h-full">
                <img src="{{ $modalPhotoUrl }}" 
                     alt="Task completion photo" 
                     class="max-w-full max-h-full object-contain rounded-lg">
            </div>
        </div>
    @endif

    <!-- Bulk Reject Modal -->
    @if($showBulkRejectModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Bulk Reject Completions</h3>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason</label>
                    <textarea wire:model="bulkRejectReason" 
                              class="w-full border border-gray-300 rounded px-3 py-2"
                              rows="3" 
                              placeholder="Enter reason for rejection..."></textarea>
                </div>
                
                <div class="flex space-x-3">
                    <button wire:click="hideBulkRejectModal" 
                            class="flex-1 bg-gray-200 text-gray-800 rounded py-2 px-4 font-medium">
                        Cancel
                    </button>
                    <button wire:click="bulkReject" 
                            class="flex-1 bg-red-600 text-white rounded py-2 px-4 font-medium">
                        Reject All
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
