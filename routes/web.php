<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Dashboard;
use App\Livewire\Admin\TaskManager;
use App\Livewire\Admin\TaskVerification;
use App\Livewire\Admin\CashOutManager;
use App\Livewire\Admin\DailyWinnerManager;
use App\Livewire\Admin\PointAdjustments;
use App\Livewire\Admin\SpotBonusManager;
use App\Livewire\Admin\UserBalanceOverview;
use App\Livewire\User\TaskList;
use App\Livewire\User\UserBalance;
use App\Livewire\User\Rankings;

Route::get('/', function() {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::view('/profile', 'profile')->name('profile');
    
    // User routes
    Route::prefix('user')->name('user.')->group(function () {
        Route::get('/tasks', TaskList::class)->name('tasks');
        Route::get('/task/{assignment}/complete', \App\Livewire\User\TaskCompletion::class)->name('task.complete');
        Route::get('/balance', UserBalance::class)->name('balance');
        Route::get('/rankings', Rankings::class)->name('rankings');
    });
    
    // Admin routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/tasks', TaskManager::class)->name('tasks');
        Route::get('/verify', TaskVerification::class)->name('verify');
        Route::get('/cash-out', CashOutManager::class)->name('cash-out');
        Route::get('/daily-winner', DailyWinnerManager::class)->name('daily-winner');
        Route::get('/point-adjustments', PointAdjustments::class)->name('point-adjustments');
        Route::get('/spot-bonuses', SpotBonusManager::class)->name('spot-bonuses');
        Route::get('/user-balances', UserBalanceOverview::class)->name('user-balances');
    });
});

require __DIR__.'/auth.php';
