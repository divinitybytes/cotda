<div>
    <!-- Prize Wheel Button -->
    <div class="mt-4">
        @if($hasSpunToday)
            <!-- Already spun today -->
            <div class="bg-gray-500 text-white rounded-lg p-4 text-center">
                <div class="font-semibold">🎡 Prize Wheel</div>
                <div class="text-sm mt-1">You've already spun today!</div>
                @if($lastSpin)
                    <div class="text-xs mt-2 bg-black bg-opacity-20 rounded p-2">
                        Today's prize: 🎁 {{ $lastSpin->prize_name }}
                    </div>
                @endif
            </div>
        @elseif($canSpin)
            <!-- Can spin -->
            <button wire:click="openWheel" 
                    class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-lg p-4 text-center font-semibold transition-all duration-200 transform hover:scale-105 prize-wheel-button">
                <div class="text-xl">🎡 SPIN THE PRIZE WHEEL! 🎡</div>
                <div class="text-sm mt-1">Win points, cash, or Whataburger treats!</div>
            </button>
        @else
            <!-- Need more tasks -->
            <div class="bg-gradient-to-r from-gray-400 to-gray-600 text-white rounded-lg p-4 text-center">
                <div class="font-semibold">🎡 Prize Wheel</div>
                <div class="text-sm mt-1">Complete {{ 2 - $completedTasks }} more task{{ (2 - $completedTasks) === 1 ? '' : 's' }} to unlock!</div>
                <div class="text-xs mt-2 bg-black bg-opacity-20 rounded p-2">
                    {{ $completedTasks }}/2 tasks completed today
                </div>
            </div>
        @endif
    </div>

    <!-- Prize Wheel Modal -->
    @if($showWheel)
        <div class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center p-4 z-[9999]">
            <div class="bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900 rounded-xl p-6 w-full max-w-lg relative overflow-hidden">
                <!-- Background sparkles -->
                <div class="absolute inset-0 overflow-hidden">
                    <div class="sparkle sparkle-1"></div>
                    <div class="sparkle sparkle-2"></div>
                    <div class="sparkle sparkle-3"></div>
                    <div class="sparkle sparkle-4"></div>
                    <div class="sparkle sparkle-5"></div>
                </div>
                
                <!-- Header -->
                <div class="text-center mb-6 relative z-10">
                    <h2 class="text-3xl font-bold text-white mb-2">🎡 PRIZE WHEEL 🎡</h2>
                    <p class="text-purple-200">Spin to win amazing prizes!</p>
                </div>

                <!-- Wheel Container -->
                <div class="relative flex justify-center mb-6">
                    <!-- Wheel -->
                    <div class="relative">
                        <!-- Pointer -->
                        <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-2 z-20">
                            <div class="w-6 h-8 bg-yellow-400 clip-triangle shadow-lg border-2 border-yellow-600"></div>
                        </div>
                        
                        <!-- Wheel -->
                        <div id="prize-wheel" 
                             class="w-80 h-80 rounded-full border-8 border-yellow-400 shadow-2xl relative"
                             style="transform: rotate(0deg); transition: none;">
                            
                            <!-- SVG Wheel Slices -->
                            <svg viewBox="0 0 200 200" class="w-full h-full absolute inset-0 rounded-full">
                                @foreach($prizes as $index => $prize)
                                    @php
                                        $startAngle = $index * 45;
                                        $endAngle = ($index + 1) * 45;
                                        
                                        // Convert to radians
                                        $startRad = ($startAngle - 90) * pi() / 180;
                                        $endRad = ($endAngle - 90) * pi() / 180;
                                        
                                        // Calculate path coordinates
                                        $radius = 95;
                                        $centerX = 100;
                                        $centerY = 100;
                                        
                                        $x1 = $centerX + $radius * cos($startRad);
                                        $y1 = $centerY + $radius * sin($startRad);
                                        $x2 = $centerX + $radius * cos($endRad);
                                        $y2 = $centerY + $radius * sin($endRad);
                                        
                                        $largeArcFlag = ($endAngle - $startAngle) > 180 ? 1 : 0;
                                        
                                        $pathData = "M {$centerX} {$centerY} L {$x1} {$y1} A {$radius} {$radius} 0 {$largeArcFlag} 1 {$x2} {$y2} Z";
                                    @endphp
                                    <path d="{{ $pathData }}" 
                                          fill="{{ $prize['color'] }}" 
                                          stroke="rgba(255,255,255,0.3)" 
                                          stroke-width="1"/>
                                @endforeach
                            </svg>
                            
                            <!-- Prize labels -->
                            @foreach($prizes as $index => $prize)
                                @php
                                    $angle = ($index * 45) + 22.5; // Center of the slice
                                    $radians = ($angle - 90) * pi() / 180;
                                    $labelRadius = 60;
                                    $x = 50 + ($labelRadius / 100) * 50 * cos($radians);
                                    $y = 50 + ($labelRadius / 100) * 50 * sin($radians);
                                @endphp
                                <div class="absolute text-center text-white font-bold text-shadow pointer-events-none"
                                     style="left: {{ $x }}%; top: {{ $y }}%; 
                                            transform: translate(-50%, -50%);
                                            width: 60px;">
                                    <div class="text-lg mb-1">{{ $prize['icon'] }}</div>
                                    <div class="text-xs leading-tight" style="font-size: 8px;">
                                        {{ $prize['name'] }}
                                    </div>
                                </div>
                            @endforeach
                            
                            <!-- Center circle -->
                            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-16 h-16 bg-gradient-to-br from-yellow-300 to-yellow-600 rounded-full border-4 border-white shadow-lg flex items-center justify-center z-10">
                                <div class="text-2xl">🎯</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Spin Button -->
                <div class="text-center relative z-10">
                    @if(!$isSpinning)
                        <button wire:click="spinWheel" 
                                class="bg-gradient-to-r from-yellow-400 to-orange-500 hover:from-yellow-500 hover:to-orange-600 text-white font-bold py-4 px-8 rounded-full text-xl shadow-lg transform hover:scale-105 transition-all duration-200 pulse-button">
                            🎰 SPIN THE WHEEL! 🎰
                        </button>
                    @else
                        <div class="text-white text-xl font-bold animate-pulse">
                            🎪 Spinning... 🎪
                        </div>
                    @endif
                </div>

                <!-- Close button -->
                <button wire:click="closeWheel" 
                        class="absolute top-4 right-4 text-white hover:text-gray-300 text-2xl z-20">
                    ✕
                </button>
            </div>
        </div>
    @endif

    <!-- Prize Won Modal -->
    @if($showPrizeModal && $wonPrize)
        <div class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center p-4 z-[10000]">
            <div class="bg-gradient-to-br from-yellow-400 via-orange-400 to-red-500 rounded-xl p-6 w-full max-w-md text-center relative overflow-hidden prize-modal">
                <!-- Confetti animation -->
                <div class="absolute inset-0 overflow-hidden">
                    <div class="confetti confetti-1"></div>
                    <div class="confetti confetti-2"></div>
                    <div class="confetti confetti-3"></div>
                    <div class="confetti confetti-4"></div>
                    <div class="confetti confetti-5"></div>
                </div>
                
                <div class="relative z-10">
                    <h2 class="text-4xl font-bold text-white mb-4">🎉 WINNER! 🎉</h2>
                    
                    <div class="bg-white bg-opacity-20 rounded-lg p-6 mb-4">
                        <div class="text-6xl mb-4">{{ $wonPrize['icon'] }}</div>
                        <h3 class="text-2xl font-bold text-white mb-2">{{ $wonPrize['name'] }}</h3>
                        
                        @if($wonPrize['type'] === 'points')
                            <p class="text-white text-lg">You earned {{ $wonPrize['points'] }} points!</p>
                        @elseif($wonPrize['type'] === 'spot_bonus')
                            <p class="text-white text-lg">You earned ${{ number_format($wonPrize['cash'], 2) }}!</p>
                        @else
                            <p class="text-white text-lg">Better luck next time!</p>
                        @endif
                    </div>
                    
                    <button wire:click="closeWheel" 
                            class="bg-white text-orange-600 font-bold py-3 px-6 rounded-full hover:bg-gray-100 transition-colors">
                        Awesome! 🚀
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- JavaScript for wheel animation -->
<script>
    let isWheelSpinning = false;
    
    document.addEventListener('livewire:initialized', () => {
        console.log('Prize wheel JavaScript initialized');
    });
    
    // Listen for the wheel animation event
    document.addEventListener('livewire:init', () => {
        Livewire.on('startWheelAnimation', (rotation) => {
            console.log('Starting wheel animation with rotation:', rotation);
            const wheel = document.getElementById('prize-wheel');
            
            if (wheel && !isWheelSpinning) {
                isWheelSpinning = true;
                
                // Reset wheel position
                wheel.style.transition = 'none';
                wheel.style.transform = 'rotate(0deg)';
                
                // Force reflow
                wheel.offsetHeight;
                
                // Add animation
                setTimeout(() => {
                    wheel.style.transition = 'transform 4s cubic-bezier(0.15, 0, 0.25, 1)';
                    wheel.style.transform = `rotate(${rotation}deg)`;
                    console.log('Wheel animation started');
                }, 50);
                
                // Show prize modal early while wheel is still spinning (creates suspense and hides the mismatch)
                setTimeout(() => {
                    console.log('Showing prize while wheel is still spinning');
                    @this.call('showPrize');
                }, 2800);
                
                // Reset spinning state after full animation completes
                setTimeout(() => {
                    console.log('Animation complete');
                    isWheelSpinning = false;
                }, 4100);
            }
        });
    });
    
    // Reset when component updates
    window.addEventListener('livewire:updated', () => {
        if (!@this.showWheel && !@this.showPrizeModal) {
            isWheelSpinning = false;
            const wheel = document.getElementById('prize-wheel');
            if (wheel) {
                wheel.style.transition = 'none';
                wheel.style.transform = 'rotate(0deg)';
            }
        }
    });
</script> 