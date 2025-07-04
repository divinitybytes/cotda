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
    
    #[Validate('required|image|max:20480')] // 20MB max to match PHP limits
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

        // Check if task has approved completion or pending completion
        // Allow retry if rejected
        if ($this->assignment->completion) {
            if ($this->assignment->completion->verification_status === 'approved') {
                return redirect()->route('user.tasks')->with('error', 'This task has already been completed and approved.');
            }
            
            if ($this->assignment->completion->verification_status === 'pending') {
                return redirect()->route('user.tasks')->with('error', 'This task completion is pending verification.');
            }
            
            // If rejected, allow retry by continuing (don't redirect)
        }
    }

    public function updatedPhoto()
    {
        // Validate photo immediately when uploaded for better user feedback
        $this->validateOnly('photo');
    }

    public function submitCompletion()
    {
        $this->validate();

        try {
            // Additional checks for file upload issues
            if (!$this->photo) {
                throw new \Exception('No photo was uploaded. Please select a photo and try again.');
            }

            if (!$this->photo->isValid()) {
                throw new \Exception('The uploaded file is corrupted. Please try taking/selecting the photo again.');
            }

            // Check file size explicitly (in case client-side validation missed it)
            $fileSizeKB = round($this->photo->getSize() / 1024);
            if ($fileSizeKB > 20480) {
                throw new \Exception("Photo is too large ({$fileSizeKB}KB). Please use a photo smaller than 20MB or compress it first.");
            }

            // Store the photo
            $photoPath = $this->photo->store('task-completions', 'public');

            if (!$photoPath) {
                throw new \Exception('Failed to save photo to server. Please try again.');
            }

            // Check if there's an existing completion (rejected task retry)
            if ($this->assignment->completion && $this->assignment->completion->verification_status === 'rejected') {
                // Delete old photo if it exists
                if ($this->assignment->completion->photo_path) {
                    Storage::disk('public')->delete($this->assignment->completion->photo_path);
                }
                
                // Update existing completion record
                $this->assignment->completion->update([
                    'photo_path' => $photoPath,
                    'completion_notes' => $this->completionNotes,
                    'verification_status' => 'pending',
                    'admin_notes' => null,
                    'verified_by' => null,
                    'verified_at' => null,
                    'completed_at' => now(),
                ]);
            } else {
                // Create new task completion record
                TaskCompletionModel::create([
                    'task_assignment_id' => $this->assignment->id,
                    'user_id' => Auth::id(),
                    'photo_path' => $photoPath,
                    'completion_notes' => $this->completionNotes,
                    'completed_at' => now(),
                ]);
            }

            session()->flash('message', 'Task completed! Your submission is pending admin verification.');
            
            return redirect()->route('user.tasks');

        } catch (\Livewire\Exceptions\ValidationException $e) {
            // Re-throw validation exceptions to show field-specific errors
            throw $e;
        } catch (\Exception $e) {
            // Log the actual error for debugging
            \Log::error('Photo upload error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'assignment_id' => $this->assignment->id,
                'photo_size' => $this->photo ? $this->photo->getSize() : 'null',
                'photo_mime' => $this->photo ? $this->photo->getMimeType() : 'null',
            ]);

            session()->flash('error', $e->getMessage());
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
