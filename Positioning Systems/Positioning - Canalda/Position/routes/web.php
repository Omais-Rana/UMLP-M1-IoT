<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PositioningController;

Route::get('/', [PositioningController::class, 'dashboard'])->name('dashboard');
Route::get('/lateration', [PositioningController::class, 'lateration'])->name('lateration');
Route::get('/fingerprint', [PositioningController::class, 'fingerprint'])->name('fingerprint');
Route::get('/markov', [PositioningController::class, 'markov'])->name('markov');
Route::post('/markov/reset', [PositioningController::class, 'resetMarkov'])->name('markov.reset');
