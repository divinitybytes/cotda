<?php

namespace App\Livewire\User;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\TaskAssignment;
use App\Models\TaskCompletion as TaskCompletionModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Title;

#[Title('Complete Task')]
class TaskCompletion extends Component
{
    use WithFileUploads;

    public $assignment;
    
    #[Validate('required|image|max:10240')] // 10MB max
    public $photo;
    
    #[Validate('nullable|string|max:500')]
    public $completionNotes = '';

    public function mount(TaskAssignment $assignment)
    {
        $this->assignment = $assignment->load(['task', 'completion']);

        // Check if user owns this assignment
        if ($this->assignment->user_id !== Auth::id()) {
            abort(403);
        }

        // Check if already completed or has pending completion
        if ($this->assignment->isCompleted() || $this->assignment->hasPendingCompletion()) {
            return redirect()->route('user.tasks')->with('error', 'This task has already been completed or is pending verification.');
        }
    }

    public function submitCompletion()
    {
        $this->validate();

        try {
            // Store the photo
            $photoPath = $this->photo->store('task-completions', 'public');

            // Create task completion record
            TaskCompletionModel::create([
                'task_assignment_id' => $this->assignment->id,
                'user_id' => Auth::id(),
                'photo_path' => $photoPath,
                'completion_notes' => $this->completionNotes,
                'completed_at' => now(),
            ]);

            session()->flash('message', 'Task completed! Your submission is pending admin verification.');
            
            return redirect()->route('user.tasks');

        } catch (\Exception $e) {
            session()->flash('error', 'Error uploading photo. Please try again.');
        }
    }

    public function resetForm()
    {
        $this->photo = null;
        $this->completionNotes = '';
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.user.task-completion');
    }
}
