<?php

use App\Http\Controllers\DashboardController;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');

Route::get('api/chart-data', [DashboardController::class, 'getChartData'])
    ->name('chart.data');

// Live data endpoints for real-time updates
Route::get('api/live-data', [DashboardController::class, 'getLiveData'])
    ->name('live.data');

Route::get('api/historical-data', [DashboardController::class, 'getHistoricalData'])
    ->name('historical.data');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});

require __DIR__ . '/auth.php';
