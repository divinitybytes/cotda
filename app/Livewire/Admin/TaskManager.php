<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;

class TaskManager extends Component
{
    public $showCreateForm = false;
    public $editingTask = null;
    
    #[Validate('required|string|max:255')]
    public $title = '';
    
    #[Validate('nullable|string')]
    public $description = '';
    
    #[Validate('required|integer|min:1|max:1000')]
    public $points = 10;
    
    #[Validate('required|in:one_time,recurring')]
    public $type = 'one_time';
    
    #[Validate('nullable|in:daily,weekly,monthly')]
    public $recurring_frequency = null;
    
    #[Validate('nullable|date')]
    public $due_date = null;
    
    public $selectedUsers = [];
    public $assignmentDate = null;
    public $assignmentDueDate = null;

    public function mount()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }
        $this->assignmentDate = now()->toDateString();
    }

    public function openTaskForm()
    {
        $this->showCreateForm = true;
        $this->resetForm();
    }

    public function hideCreateForm()
    {
        $this->showCreateForm = false;
        $this->resetForm();
    }

    public function editTask($taskId)
    {
        $task = Task::findOrFail($taskId);
        $this->editingTask = $task;
        $this->title = $task->title;
        $this->description = $task->description;
        $this->points = $task->points;
        $this->type = $task->type;
        $this->recurring_frequency = $task->recurring_frequency;
        $this->due_date = $task->due_date?->format('Y-m-d');
        $this->showCreateForm = true;
    }

    public function saveTask()
    {
        $this->validate();

        $taskData = [
            'title' => $this->title,
            'description' => $this->description,
            'points' => $this->points,
            'type' => $this->type,
            'recurring_frequency' => $this->type === 'recurring' ? $this->recurring_frequency : null,
            'due_date' => $this->due_date,
            'created_by' => Auth::id(),
        ];

        if ($this->editingTask) {
            $this->editingTask->update($taskData);
            $task = $this->editingTask;
        } else {
            $task = Task::create($taskData);
        }

        // Assign to selected users if any
        if (!empty($this->selectedUsers)) {
            $task->assignToUsers($this->selectedUsers, $this->assignmentDate, $this->assignmentDueDate);
        }

        $this->dispatch('task-saved');
        $this->hideCreateForm();
        
        session()->flash('message', $this->editingTask ? 'Task updated successfully!' : 'Task created successfully!');
    }

    public function deleteTask($taskId)
    {
        $task = Task::findOrFail($taskId);
        $task->update(['is_active' => false]);
        
        session()->flash('message', 'Task deactivated successfully!');
    }

    public function assignTask($taskId)
    {
        if (empty($this->selectedUsers)) {
            session()->flash('error', 'Please select at least one user.');
            return;
        }

        $task = Task::findOrFail($taskId);
        $task->assignToUsers($this->selectedUsers, $this->assignmentDate, $this->assignmentDueDate);
        
        $this->selectedUsers = [];
        session()->flash('message', 'Task assigned successfully!');
    }

    public function resetForm()
    {
        $this->editingTask = null;
        $this->title = '';
        $this->description = '';
        $this->points = 10;
        $this->type = 'one_time';
        $this->recurring_frequency = null;
        $this->due_date = null;
        $this->selectedUsers = [];
        $this->assignmentDate = now()->toDateString();
        $this->assignmentDueDate = null;
        $this->resetValidation();
    }

    public function render()
    {
        $tasks = Task::with(['creator', 'assignments.user'])
            ->active()
            ->latest()
            ->paginate(10);

        $users = User::where('role', 'user')
            ->orderBy('name')
            ->get();

        return view('livewire.admin.task-manager', [
            'tasks' => $tasks,
            'users' => $users,
        ]);
    }
}
