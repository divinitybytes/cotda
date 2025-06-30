<div class="min-h-screen bg-gradient-to-br from-green-50 to-emerald-100">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b px-4 py-3">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Complete Task</h1>
                <p class="text-sm text-gray-600">Upload photo evidence to complete your task</p>
            </div>
            <a href="{{ route('user.tasks') }}" class="text-gray-600 text-sm">
                ← Back to Tasks
            </a>
        </div>
    </div>

    <div class="p-4">
        <!-- Task Details -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-semibold text-gray-900">{{ $assignment->task->title }}</h2>
                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-semibold">
                    {{ $assignment->task->points }} points
                </span>
            </div>

            @if($assignment->task->description)
                <p class="text-gray-600 mb-4">{{ $assignment->task->description }}</p>
            @endif

            <div class="grid grid-cols-2 gap-4 text-sm text-gray-500">
                <div>
                    <div class="font-medium">Assigned:</div>
                    <div>{{ $assignment->assigned_date->format('M j, Y') }}</div>
                </div>
                @if($assignment->due_date)
                    <div>
                        <div class="font-medium {{ $assignment->isOverdue() ? 'text-red-600' : '' }}">Due:</div>
                        <div class="{{ $assignment->isOverdue() ? 'text-red-600 font-semibold' : '' }}">
                            {{ $assignment->due_date->format('M j, Y') }}
                            @if($assignment->isOverdue()) <span class="text-xs">(Overdue)</span> @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Completion Form -->
        <form wire:submit="submitCompletion" class="space-y-6">
            <!-- Photo Upload -->
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="font-semibold text-gray-900 mb-4">📸 Photo Evidence (Required)</h3>
                
                <div class="space-y-4">
                    <!-- Photo Input -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Take or upload a photo showing your completed task
                        </label>
                        
                        <!-- File Input with Camera -->
                        <input type="file" 
                               wire:model="photo" 
                               accept="image/*" 
                               capture="environment"
                               id="photoInput"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        
                        <div class="text-xs text-gray-500 mt-1">
                            Maximum file size: 20MB. Supported formats: JPG, PNG, GIF
                            <span class="block text-blue-600 font-medium">📱 iPhone users: Photos will be automatically compressed if needed</span>
                        </div>
                        
                        @error('photo') 
                            <span class="text-red-500 text-xs">{{ $message }}</span> 
                        @enderror
                    </div>

                    <!-- Photo Preview -->
                    @if($photo)
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4">
                            <div class="text-center">
                                <div class="text-green-600 mb-2">✅ Photo selected</div>
                                <div class="text-sm text-gray-600">{{ $photo->getClientOriginalName() }}</div>
                                <div class="text-xs text-gray-500">{{ number_format($photo->getSize() / 1024, 0) }}KB</div>
                            </div>
                        </div>
                    @else
                        <!-- Upload Instructions -->
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                            <div class="text-4xl mb-2">📱</div>
                            <div class="text-sm text-gray-600 mb-2">Tap to take a photo or select from gallery</div>
                            <div class="text-xs text-gray-500">
                                Show the completed task clearly in your photo
                            </div>
                        </div>
                    @endif

                    <!-- Upload Progress -->
                    <div wire:loading wire:target="photo" class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full transition-all duration-300 animate-pulse" style="width: 45%"></div>
                    </div>
                </div>
            </div>

            <!-- Completion Notes -->
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="font-semibold text-gray-900 mb-4">📝 Additional Notes (Optional)</h3>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Add any comments about completing this task
                    </label>
                    <textarea wire:model="completionNotes" 
                              rows="3"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="e.g., This was easier than expected, or I had trouble with..."></textarea>
                    
                    @error('completionNotes') 
                        <span class="text-red-500 text-xs">{{ $message }}</span> 
                    @enderror
                </div>
            </div>

            <!-- Tips for Good Photos -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="font-semibold text-blue-900 mb-2">💡 Tips for Good Photos</h4>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li>• Make sure the completed task is clearly visible</li>
                    <li>• Use good lighting - natural light works best</li>
                    <li>• Take the photo from an angle that shows the work clearly</li>
                    <li>• Include the whole area/item that was cleaned or organized</li>
                    <li>• Avoid blurry or dark photos</li>
                    <li>📱 <strong>iPhone users:</strong> Use "Photo" mode instead of "Portrait" mode for better compatibility</li>
                </ul>
            </div>

            <!-- Submit Button -->
            <div class="space-y-3">
                <button type="submit" 
                        class="w-full bg-green-600 text-white rounded-lg py-4 px-6 font-semibold text-lg shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="!$wire.photo">
                    <span wire:loading.remove wire:target="submitCompletion">
                        🏆 Submit Completion & Earn {{ $assignment->task->points }} Points
                    </span>
                    <span wire:loading wire:target="submitCompletion" class="flex items-center justify-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Uploading...
                    </span>
                </button>
            </div>

            <!-- Warning if overdue -->
            @if($assignment->isOverdue())
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="text-red-600 mr-2">⚠️</div>
                        <div class="text-sm text-red-800">
                            <strong>This task is overdue.</strong> You can still complete it, but it was due on {{ $assignment->due_date->format('M j, Y') }}.
                        </div>
                    </div>
                </div>
            @endif
        </form>

        <!-- What Happens Next -->
        <div class="mt-6 bg-gray-50 rounded-lg p-4">
            <h4 class="font-semibold text-gray-900 mb-2">What happens next?</h4>
            <div class="text-sm text-gray-600 space-y-1">
                <p>1. Your photo will be reviewed by an admin</p>
                <p>2. If approved, you'll earn {{ $assignment->task->points }} points</p>
                <p>3. Points help you compete for the daily "Child of the Day" $5 award</p>
                <p>4. You'll get notified of the decision</p>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if (session()->has('message'))
        <div class="fixed bottom-4 left-4 right-4 bg-green-500 text-white p-4 rounded-lg shadow-lg z-50">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="fixed bottom-4 left-4 right-4 bg-red-500 text-white p-4 rounded-lg shadow-lg z-50">
            {{ session('error') }}
        </div>
    @endif
</div>

<script>
// Client-side image compression for iPhone compatibility
document.addEventListener('DOMContentLoaded', function() {
    const photoInput = document.getElementById('photoInput');
    
    if (photoInput) {
        photoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            // Check if compression is needed (file > 1.5MB)
            if (file.size > 1.5 * 1024 * 1024) {
                compressImage(file, (compressedFile) => {
                    // Replace the file input with compressed version
                    const dt = new DataTransfer();
                    dt.items.add(compressedFile);
                    photoInput.files = dt.files;
                    
                    // Trigger Livewire update
                    photoInput.dispatchEvent(new Event('change', { bubbles: true }));
                });
            }
        });
    }
    
    function compressImage(file, callback) {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const img = new Image();
        
        img.onload = function() {
            // Calculate new dimensions to stay under 2MB
            let { width, height } = img;
            const maxDimension = 1200; // Max width or height
            
            if (width > height && width > maxDimension) {
                height = (height * maxDimension) / width;
                width = maxDimension;
            } else if (height > maxDimension) {
                width = (width * maxDimension) / height;
                height = maxDimension;
            }
            
            canvas.width = width;
            canvas.height = height;
            
            // Draw and compress
            ctx.drawImage(img, 0, 0, width, height);
            
            canvas.toBlob(function(blob) {
                // Convert Blob to File object
                const compressedFile = new File([blob], file.name, {
                    type: 'image/jpeg',
                    lastModified: Date.now()
                });
                callback(compressedFile);
            }, 'image/jpeg', 0.8); // 80% quality
        };
        
        img.src = URL.createObjectURL(file);
    }
});
</script>
