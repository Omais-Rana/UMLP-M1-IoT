<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\IoTController;

// Legacy endpoint (keeping for backward compatibility)
Route::post('/nano-data', function (Request $request) {
    Log::info('Nano data received (legacy)', $request->all());

    // Example: save into DB
    DB::table('nano_readings')->insert([
        'x' => $request->input('x'),
        'y' => $request->input('y'),
        'z' => $request->input('z'),
        'created_at' => now(),
    ]);

    return response()->json(['status' => 'ok']);
});

// New IoT API endpoints
Route::prefix('iot')->group(function () {
    // Device registration
    Route::post('/register', [IoTController::class, 'registerDevice']);

    // Data collection endpoints
    Route::post('/arduino/data', [IoTController::class, 'receiveArduinoData']);
    Route::post('/esp32/data', [IoTController::class, 'receiveEsp32Data']);

    // Data retrieval endpoints
    Route::get('/devices', [IoTController::class, 'getAllDevicesData']);
    Route::get('/device/{device}', [IoTController::class, 'getDeviceData']);
});
