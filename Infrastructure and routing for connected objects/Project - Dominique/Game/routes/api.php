<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Events\ControllerDataEvent;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/**
 * IoT Controller Input Route
 * The Python Bridge script sends POST requests here.
 */
Route::post('/controller-data', function (Request $request) {
    // 1. Grab only the specific keys we need from the JSON packet
    $data = $request->only(['P', 'Y', 'JX', 'JY', 'F', 'C']);

    // 2. Broadcast the event immediately
    // "new ControllerDataEvent($data)" triggers the Reverb broadcast
    broadcast(new ControllerDataEvent($data));

    // 3. Return a quick success response so the Python script doesn't hang
    return response()->json(['status' => 'received']);
});
