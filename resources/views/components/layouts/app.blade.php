<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Chore Tracker') }}</title>
        <meta name="description" content="Mobile-first chore tracking application for kids to earn points and rewards">
        
        <!-- PWA Meta Tags -->
        <meta name="theme-color" content="#667eea">
        <meta name="background-color" content="#667eea">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="Chore Tracker">
        <meta name="msapplication-TileColor" content="#667eea">
        <meta name="msapplication-tap-highlight" content="no">
        
        <!-- Mobile optimizations -->
        <meta name="format-detection" content="telephone=no">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="application-name" content="Chore Tracker">
        
        <!-- PWA Manifest -->
        <link rel="manifest" href="/manifest.json">
        
        <!-- Icons -->
        <link rel="icon" type="image/png" sizes="32x32" href="/icons/icon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/icons/icon-16x16.png">
        <link rel="apple-touch-icon" href="/icons/icon-192x192.png">
        <link rel="apple-touch-icon" sizes="152x152" href="/icons/icon-152x152.png">
        <link rel="apple-touch-icon" sizes="180x180" href="/icons/icon-192x192.png">
        <link rel="apple-touch-icon" sizes="167x167" href="/icons/icon-192x192.png">
        
        <!-- Splash screens for iOS -->
        <link rel="apple-touch-startup-image" media="screen and (device-width: 430px) and (device-height: 932px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)" href="/splash/iPhone_14_Pro_Max_portrait.png">
        <link rel="apple-touch-startup-image" media="screen and (device-width: 393px) and (device-height: 852px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)" href="/splash/iPhone_14_Pro_portrait.png">
        <link rel="apple-touch-startup-image" media="screen and (device-width: 390px) and (device-height: 844px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)" href="/splash/iPhone_14_portrait.png">
        <link rel="apple-touch-startup-image" media="screen and (device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)" href="/splash/iPhone_13_mini_portrait.png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        
        <!-- PWA Install Detection -->
        <style>
            .pwa-install-prompt {
                position: fixed;
                bottom: 80px;
                left: 16px;
                right: 16px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 16px;
                border-radius: 12px;
                box-shadow: 0 8px 32px rgba(0,0,0,0.3);
                z-index: 1000;
                transform: translateY(100%);
                transition: transform 0.3s ease;
            }
            
            .pwa-install-prompt.show {
                transform: translateY(0);
            }
            
            .pwa-install-prompt button {
                background: rgba(255,255,255,0.2);
                border: 1px solid rgba(255,255,255,0.3);
                color: white;
                padding: 8px 16px;
                border-radius: 6px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.2s ease;
            }
            
            .pwa-install-prompt button:hover {
                background: rgba(255,255,255,0.3);
            }
            
            .pwa-install-prompt .close-btn {
                background: none;
                border: none;
                color: rgba(255,255,255,0.8);
                font-size: 18px;
                padding: 4px;
                cursor: pointer;
                float: right;
                margin-top: -4px;
            }
        </style>
    </head>
    <body class="font-sans antialiased bg-gray-50">
        <div class="min-h-screen">
            <!-- Mobile Navigation -->
            <nav class="bg-white shadow-sm border-b sticky top-0 z-50">
                <div class="px-4 py-2">
                    <div class="flex items-center justify-between">
                        <!-- Logo/Brand -->
                        <div class="flex items-center">
                            <a href="{{ route('dashboard') }}" class="text-lg font-bold text-indigo-600">
                                🏆 {{ config('app.name', 'Chore Tracker') }}
                            </a>
                        </div>

                        <!-- User Info & Menu -->
                        <div class="flex items-center space-x-2">
                            @auth
                                <!-- User Avatar/Points -->
                                <div class="text-right mr-2">
                                    <div class="text-sm font-semibold text-gray-900">{{ Auth::user()->name }}</div>
                                    @if(Auth::user()->isUser())
                                        <div class="text-xs text-indigo-600">{{ Auth::user()->points }} pts</div>
                                    @else
                                        <div class="text-xs text-gray-500">Admin</div>
                                    @endif
                                </div>

                                <!-- Logout -->
                                <form method="POST" action="{{ route('logout') }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-sm text-gray-600 hover:text-gray-900">
                                        Logout
                                    </button>
                                </form>
                            @endauth
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>

            <!-- Mobile Bottom Navigation -->
            @auth
                @if(Auth::user()->isUser())
                    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t shadow-lg z-50">
                        <div class="grid grid-cols-4 py-2">
                            <a href="{{ route('dashboard') }}" class="flex flex-col items-center py-2 text-xs {{ request()->routeIs('dashboard') ? 'text-indigo-600' : 'text-gray-600' }}">
                                <div class="text-lg mb-1">🏠</div>
                                Home
                            </a>
                            
                            <a href="{{ route('user.tasks') }}" class="flex flex-col items-center py-2 text-xs {{ request()->routeIs('user.tasks') ? 'text-indigo-600' : 'text-gray-600' }}">
                                <div class="text-lg mb-1">📝</div>
                                Tasks
                            </a>
                            
                            <a href="{{ route('user.balance') }}" class="flex flex-col items-center py-2 text-xs {{ request()->routeIs('user.balance') ? 'text-indigo-600' : 'text-gray-600' }}">
                                <div class="text-lg mb-1">💰</div>
                                Balance
                            </a>
                            
                            <a href="{{ route('user.rankings') }}" class="flex flex-col items-center py-2 text-xs {{ request()->routeIs('user.rankings') ? 'text-indigo-600' : 'text-gray-600' }}">
                                <div class="text-lg mb-1">🏆</div>
                                Rankings
                            </a>
                        </div>
                    </nav>

                    <!-- Bottom padding to account for fixed nav -->
                    <div class="h-16"></div>
                @else
                    <!-- Admin Navigation -->
                    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t shadow-lg z-50">
                        <div class="grid grid-cols-3 py-2">
                            <a href="{{ route('dashboard') }}" class="flex flex-col items-center py-2 text-xs {{ request()->routeIs('dashboard') ? 'text-indigo-600' : 'text-gray-600' }}">
                                <div class="text-lg mb-1">🏠</div>
                                Dashboard
                            </a>
                            
                            <a href="{{ route('admin.tasks') }}" class="flex flex-col items-center py-2 text-xs {{ request()->routeIs('admin.tasks') ? 'text-indigo-600' : 'text-gray-600' }}">
                                <div class="text-lg mb-1">🎯</div>
                                Tasks
                            </a>
                            
                            <a href="{{ route('admin.verify') }}" class="flex flex-col items-center py-2 text-xs {{ request()->routeIs('admin.verify') ? 'text-indigo-600' : 'text-gray-600' }} relative">
                                <div class="text-lg mb-1">✅</div>
                                Verify
                                @if(App\Models\TaskCompletion::pending()->count() > 0)
                                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">{{ App\Models\TaskCompletion::pending()->count() }}</span>
                                @endif
                            </a>
                        </div>
                    </nav>

                    <!-- Bottom padding to account for fixed nav -->
                    <div class="h-16"></div>
                @endif
            @endauth
        </div>

        @livewireScripts
        
        <!-- PWA Install Prompt -->
        <div id="pwa-install-prompt" class="pwa-install-prompt">
            <button class="close-btn" onclick="hidePWAPrompt()">&times;</button>
            <div style="display: flex; align-items: center; margin-bottom: 12px;">
                <div style="font-size: 24px; margin-right: 12px;">📱</div>
                <div>
                    <div style="font-weight: 600; margin-bottom: 4px;">Install Chore Tracker</div>
                    <div style="font-size: 14px; opacity: 0.9;">Add to your home screen for a better experience!</div>
                </div>
            </div>
            <div style="display: flex; gap: 12px;">
                <button onclick="installPWA()" style="flex: 1;">Install App</button>
                <button onclick="hidePWAPrompt()" style="flex: 0 0 auto;">Not Now</button>
            </div>
        </div>
        
        <!-- PWA Scripts -->
        <script>
            let deferredPrompt;
            let pwaInstallPromptShown = localStorage.getItem('pwa-prompt-shown') === 'true';
            
            // Register Service Worker
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js')
                        .then((registration) => {
                            console.log('SW registered: ', registration);
                            
                            // Check for updates
                            registration.addEventListener('updatefound', () => {
                                const newWorker = registration.installing;
                                newWorker.addEventListener('statechange', () => {
                                    if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                        // New content is available, prompt user to refresh
                                        if (confirm('A new version is available! Refresh to update?')) {
                                            window.location.reload();
                                        }
                                    }
                                });
                            });
                        })
                        .catch((registrationError) => {
                            console.log('SW registration failed: ', registrationError);
                        });
                });
            }
            
            // PWA Install Prompt
            window.addEventListener('beforeinstallprompt', (e) => {
                console.log('beforeinstallprompt fired');
                e.preventDefault();
                deferredPrompt = e;
                
                // Show install prompt if not already shown
                if (!pwaInstallPromptShown && !window.matchMedia('(display-mode: standalone)').matches) {
                    setTimeout(() => {
                        showPWAPrompt();
                    }, 3000); // Show after 3 seconds
                }
            });
            
            function showPWAPrompt() {
                const prompt = document.getElementById('pwa-install-prompt');
                if (prompt) {
                    prompt.classList.add('show');
                }
            }
            
            function hidePWAPrompt() {
                const prompt = document.getElementById('pwa-install-prompt');
                if (prompt) {
                    prompt.classList.remove('show');
                    localStorage.setItem('pwa-prompt-shown', 'true');
                    pwaInstallPromptShown = true;
                }
            }
            
            async function installPWA() {
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    const result = await deferredPrompt.userChoice;
                    console.log('User choice:', result);
                    
                    if (result.outcome === 'accepted') {
                        console.log('User accepted the install prompt');
                    } else {
                        console.log('User dismissed the install prompt');
                    }
                    
                    deferredPrompt = null;
                    hidePWAPrompt();
                } else {
                    // Fallback for browsers that don't support install prompt
                    alert('To install this app:\n\n1. Tap the share button\n2. Select "Add to Home Screen"');
                    hidePWAPrompt();
                }
            }
            
            // Track install event
            window.addEventListener('appinstalled', () => {
                console.log('PWA was installed');
                hidePWAPrompt();
                
                // Hide prompt permanently after successful install
                localStorage.setItem('pwa-installed', 'true');
            });
            
            // Don't show prompt if already installed
            if (localStorage.getItem('pwa-installed') === 'true' || 
                window.matchMedia('(display-mode: standalone)').matches) {
                pwaInstallPromptShown = true;
            }
            
            // Handle iOS install instructions
            function isIOS() {
                return /iPad|iPhone|iPod/.test(navigator.userAgent);
            }
            
            function isInStandaloneMode() {
                return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
            }
            
            // Show iOS-specific install instructions
            if (isIOS() && !isInStandaloneMode() && !pwaInstallPromptShown) {
                setTimeout(() => {
                    const prompt = document.getElementById('pwa-install-prompt');
                    if (prompt) {
                        prompt.innerHTML = `
                            <button class="close-btn" onclick="hidePWAPrompt()">&times;</button>
                            <div style="display: flex; align-items: center; margin-bottom: 12px;">
                                <div style="font-size: 24px; margin-right: 12px;">📱</div>
                                <div>
                                    <div style="font-weight: 600; margin-bottom: 4px;">Install Chore Tracker</div>
                                    <div style="font-size: 14px; opacity: 0.9;">Add to your home screen for the best experience!</div>
                                </div>
                            </div>
                            <div style="font-size: 14px; line-height: 1.4;">
                                1. Tap the share button <span style="font-size: 16px;">⬆️</span><br>
                                2. Select "Add to Home Screen" <span style="font-size: 16px;">➕</span><br>
                                3. Tap "Add" to install
                            </div>
                            <div style="margin-top: 12px;">
                                <button onclick="hidePWAPrompt()" style="width: 100%;">Got it!</button>
                            </div>
                        `;
                        prompt.classList.add('show');
                    }
                }, 5000);
            }
        </script>
        
        <!-- Auto-hide flash messages -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const flashMessages = document.querySelectorAll('[class*="fixed bottom-4"]');
                flashMessages.forEach(function(message) {
                    setTimeout(function() {
                        message.style.opacity = '0';
                        setTimeout(function() {
                            message.remove();
                        }, 300);
                    }, 3000);
                });
            });
        </script>
        
        <!-- Prevent zoom on input focus (iOS) -->
        <script>
            if (/iPad|iPhone|iPod/.test(navigator.userAgent)) {
                const viewport = document.querySelector('meta[name="viewport"]');
                viewport.content = 'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover';
            }
        </script>
    </body>
</html> 