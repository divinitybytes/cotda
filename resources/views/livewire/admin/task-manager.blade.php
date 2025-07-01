<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b px-4 py-3">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Task Manager</h1>
                <p class="text-sm text-gray-600">Create and manage chore tasks</p>
            </div>
            <button wire:click="openTaskForm" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                + New Task
            </button>
        </div>
    </div>

    <div class="p-4 space-y-6">
        <!-- Create/Edit Task Form -->
        @if($showCreateForm)
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">
                        {{ $editingTask ? 'Edit Task' : 'Create New Task' }}
                    </h2>
                    <button wire:click="hideCreateForm" class="text-gray-500 text-xl">×</button>
                </div>

                <form wire:submit="saveTask" class="space-y-4">
                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Task Title</label>
                        <input type="text" wire:model="title" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="e.g., Clean Your Room">
                        @error('title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea wire:model="description" rows="3"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Detailed instructions for the task..."></textarea>
                        @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Points and Type -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Points</label>
                            <input type="number" wire:model="points" min="1" max="1000"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('points') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                            <select wire:model="type" wire:change="$refresh"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="one_time">One-time</option>
                                <option value="recurring">Recurring</option>
                            </select>
                            @error('type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Recurring Frequency (if recurring) -->
                    @if($type === 'recurring')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Frequency</label>
                            <select wire:model="recurring_frequency"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select frequency...</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                            @error('recurring_frequency') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    <!-- Due Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Due Date (optional)</label>
                        <input type="date" wire:model="due_date"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('due_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Assign to Users -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Assign to Users (optional)</label>
                        <div class="grid grid-cols-2 gap-2 max-h-32 overflow-y-auto">
                            @foreach($users as $user)
                                <label class="flex items-center space-x-2 text-sm">
                                    <input type="checkbox" wire:model="selectedUsers" value="{{ $user->id }}"
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span>{{ $user->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Assignment Dates (if users selected) -->
                    @if(!empty($selectedUsers))
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Assignment Date</label>
                                <input type="date" wire:model="assignmentDate"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Assignment Due Date</label>
                                <input type="date" wire:model="assignmentDueDate"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    @endif

                    <!-- Form Actions -->
                    <div class="flex space-x-3 pt-4">
                        <button type="submit" 
                                class="flex-1 bg-blue-600 text-white rounded-lg py-2 px-4 font-semibold">
                            {{ $editingTask ? 'Update Task' : 'Create Task' }}
                        </button>
                        <button type="button" wire:click="hideCreateForm"
                                class="flex-1 bg-gray-200 text-gray-800 rounded-lg py-2 px-4 font-semibold">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <!-- Task List -->
        <div class="space-y-4">
            @forelse($tasks as $task)
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900">{{ $task->title }}</h3>
                            @if($task->description)
                                <p class="text-sm text-gray-600 mt-1">{{ $task->description }}</p>
                            @endif
                        </div>
                        
                        <div class="flex items-center space-x-2 ml-4">
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-semibold">
                                {{ $task->points }} pts
                            </span>
                            @if($task->type === 'recurring')
                                <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs font-semibold">
                                    {{ ucfirst($task->recurring_frequency) }}
                                </span>
                            @else
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-semibold">
                                    One-time
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="text-xs text-gray-500 mb-3">
                        <div>Created by {{ $task->creator->name }}</div>
                        @if($task->due_date)
                            <div>Due: {{ $task->due_date->format('M j, Y') }}</div>
                        @endif
                        <div>Created {{ $task->created_at->diffForHumans() }}</div>
                    </div>

                    <!-- Assigned Users -->
                    @if($task->assignments->count() > 0)
                        <div class="mb-3">
                            <div class="text-xs font-medium text-gray-700 mb-1">Assigned to:</div>
                            <div class="flex flex-wrap gap-1">
                                @foreach($task->assignments->groupBy('user.name') as $userName => $assignments)
                                    <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs">
                                        {{ $userName }} 
                                        @if($assignments->where('is_completed', true)->count() > 0)
                                            <span class="text-green-600">✓</span>
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Task Actions -->
                    <div class="flex space-x-2">
                        <button wire:click="editTask({{ $task->id }})"
                                class="bg-blue-100 text-blue-800 px-3 py-1 rounded text-xs font-semibold">
                            Edit
                        </button>
                        
                        <!-- Quick Assign -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open"
                                    class="bg-green-100 text-green-800 px-3 py-1 rounded text-xs font-semibold">
                                Assign
                            </button>
                            <div x-show="open" @click.away="open = false" 
                                 class="absolute top-8 left-0 bg-white border rounded-lg shadow-lg p-2 z-10 min-w-48">
                                <div class="text-xs font-medium text-gray-700 mb-1">Select users:</div>
                                @foreach($users as $user)
                                    <label class="flex items-center space-x-2 text-xs py-1">
                                        <input type="checkbox" wire:model="quickAssignUsers" value="{{ $user->id }}">
                                        <span>{{ $user->name }}</span>
                                    </label>
                                @endforeach
                                <div class="mt-2">
                                    <label class="block text-xs text-gray-700 mb-1">Assignment Date</label>
                                    <input type="date" wire:model="quickAssignDate" class="w-full border border-gray-300 rounded px-2 py-1 text-xs">
                                </div>
                                <div class="mt-2">
                                    <label class="block text-xs text-gray-700 mb-1">Due Date</label>
                                    <input type="date" wire:model="quickAssignDueDate" class="w-full border border-gray-300 rounded px-2 py-1 text-xs">
                                </div>
                                <button wire:click.prevent="$set('quickAssignTaskId', {{ $task->id }}); assignTask()"
                                        class="w-full bg-green-600 text-white px-2 py-1 rounded text-xs mt-2">
                                    Assign
                                </button>
                            </div>
                        </div>

                        <button wire:click="deleteTask({{ $task->id }})"
                                onclick="return confirm('Are you sure you want to deactivate this task?')"
                                class="bg-red-100 text-red-800 px-3 py-1 rounded text-xs font-semibold">
                            Deactivate
                        </button>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow p-8 text-center">
                    <div class="text-6xl mb-4">🎯</div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No tasks created yet</h3>
                    <p class="text-gray-600">Create your first task to get started!</p>
                    <button wire:click="openTaskForm" 
                            class="mt-4 bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold">
                        Create First Task
                    </button>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($tasks->hasPages())
            <div class="mt-6">
                {{ $tasks->links() }}
            </div>
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


