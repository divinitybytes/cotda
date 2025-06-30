<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Chore Tracker') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
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
    </body>
</html> 