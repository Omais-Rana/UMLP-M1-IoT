<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\SensorReading;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class IoTController extends Controller
{
    /**
     * Register or update a device
     */
    public function registerDevice(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:arduino_nano,esp32',
            'mac_address' => 'nullable|string|max:17',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $device = Device::updateOrCreate(
            ['mac_address' => $request->mac_address],
            [
                'name' => $request->name,
                'type' => $request->type,
                'ip_address' => $request->ip(),
                'status' => 'online',
                'last_seen_at' => now(),
                'location' => $request->location,
                'description' => $request->description,
            ]
        );

        Log::info('Device registered/updated', ['device_id' => $device->id, 'type' => $device->type]);

        return response()->json(['device_id' => $device->id, 'status' => 'success'], 201);
    }

    /**
     * Receive Arduino Nano data
     */
    public function receiveArduinoData(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|exists:devices,id',
            'temperature' => 'nullable|numeric',
            'humidity' => 'nullable|numeric',
            'x' => 'nullable|numeric',
            'y' => 'nullable|numeric',
            'z' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $device = Device::find($request->device_id);
        $device->update([
            'status' => 'online',
            'last_seen_at' => now(),
            'ip_address' => $request->ip()
        ]);

        $readings = [];

        // Store temperature reading
        if ($request->has('temperature')) {
            $readings[] = SensorReading::create([
                'device_id' => $request->device_id,
                'sensor_type' => 'temperature',
                'value' => $request->temperature,
                'unit' => '°C'
            ]);
        }

        // Store humidity reading
        if ($request->has('humidity')) {
            $readings[] = SensorReading::create([
                'device_id' => $request->device_id,
                'sensor_type' => 'humidity',
                'value' => $request->humidity,
                'unit' => '%'
            ]);
        }

        // Store accelerometer data
        if ($request->has('x') && $request->has('y') && $request->has('z')) {
            $readings[] = SensorReading::create([
                'device_id' => $request->device_id,
                'sensor_type' => 'accelerometer',
                'x_axis' => $request->x,
                'y_axis' => $request->y,
                'z_axis' => $request->z,
                'unit' => 'g'
            ]);
        }

        Log::info('Arduino data received', [
            'device_id' => $request->device_id,
            'readings_count' => count($readings)
        ]);

        return response()->json(['status' => 'success', 'readings_stored' => count($readings)]);
    }

    /**
     * Receive ESP32 data
     */
    public function receiveEsp32Data(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|exists:devices,id',
            'temperature' => 'nullable|numeric',
            'humidity' => 'nullable|numeric',
            'pressure' => 'nullable|numeric',
            'light' => 'nullable|numeric',
            'motion' => 'nullable|boolean',
            'wifi_signal' => 'nullable|integer',
            'battery_level' => 'nullable|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $device = Device::find($request->device_id);
        $device->update([
            'status' => 'online',
            'last_seen_at' => now(),
            'ip_address' => $request->ip()
        ]);

        $readings = [];

        // Store various sensor readings
        $sensors = [
            'temperature' => ['value' => $request->temperature, 'unit' => '°C'],
            'humidity' => ['value' => $request->humidity, 'unit' => '%'],
            'pressure' => ['value' => $request->pressure, 'unit' => 'hPa'],
            'light' => ['value' => $request->light, 'unit' => 'lux'],
            'motion' => ['value' => $request->motion ? 1 : 0, 'unit' => 'bool'],
            'wifi_signal' => ['value' => $request->wifi_signal, 'unit' => 'dBm'],
            'battery_level' => ['value' => $request->battery_level, 'unit' => '%']
        ];

        foreach ($sensors as $sensorType => $data) {
            if ($request->has($sensorType) && $data['value'] !== null) {
                $readings[] = SensorReading::create([
                    'device_id' => $request->device_id,
                    'sensor_type' => $sensorType,
                    'value' => $data['value'],
                    'unit' => $data['unit']
                ]);
            }
        }

        Log::info('ESP32 data received', [
            'device_id' => $request->device_id,
            'readings_count' => count($readings)
        ]);

        return response()->json(['status' => 'success', 'readings_stored' => count($readings)]);
    }

    /**
     * Get device status and latest readings
     */
    public function getDeviceData(Device $device): JsonResponse
    {
        $latestReadings = $device->sensorReadings()
            ->select(['sensor_type', 'value', 'unit', 'x_axis', 'y_axis', 'z_axis', 'created_at'])
            ->latest('created_at')
            ->limit(50)
            ->get()
            ->groupBy('sensor_type')
            ->map(function ($readings) {
                return $readings->first();
            });

        return response()->json([
            'device' => $device,
            'latest_readings' => $latestReadings,
            'is_online' => $device->isOnline()
        ]);
    }

    /**
     * Get all devices with their latest readings
     */
    public function getAllDevicesData(): JsonResponse
    {
        $devices = Device::with(['sensorReadings' => function ($query) {
            $query->select(['device_id', 'sensor_type', 'value', 'unit', 'x_axis', 'y_axis', 'z_axis', 'created_at'])
                ->latest('created_at')
                ->limit(10);
        }])->get();

        return response()->json($devices);
    }
}
