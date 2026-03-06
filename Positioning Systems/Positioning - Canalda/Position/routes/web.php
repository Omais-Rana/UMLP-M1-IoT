<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PositioningController;

// Route to display the N-lateration map and results
Route::get('/', [PositioningController::class, 'index'])->name('lateration.index');

// Route::get('/', function () {
//     return view('welcome');
// });
