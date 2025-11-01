<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\SensorReading;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function index()
    {
        // Get all devices
        $devices = Device::with(['sensorReadings' => function ($query) {
            $query->latest('created_at')->limit(5);
        }])->get();

        // Get latest sensor readings for the cards
        // Get temperature and humidity from ESP32 devices only (exclude 0 values from Nano)
        $latestReadings = [
            'temperature' => SensorReading::join('devices', 'sensor_readings.device_id', '=', 'devices.id')
                ->where('sensor_type', 'temperature')
                ->where('devices.type', 'esp32')
                ->where('value', '>', 0)
                ->latest('sensor_readings.created_at')
                ->value('sensor_readings.value') ?? 0,
            'humidity' => SensorReading::join('devices', 'sensor_readings.device_id', '=', 'devices.id')
                ->where('sensor_type', 'humidity')
                ->where('devices.type', 'esp32')
                ->where('value', '>', 0)
                ->latest('sensor_readings.created_at')
                ->value('sensor_readings.value') ?? 0,
            // Get accelerometer from Arduino Nano devices
            'x' => SensorReading::join('devices', 'sensor_readings.device_id', '=', 'devices.id')
                ->where('sensor_type', 'accelerometer_x')
                ->where('devices.type', 'arduino_nano')
                ->latest('sensor_readings.created_at')
                ->value('sensor_readings.value') ?? 0,
            'y' => SensorReading::join('devices', 'sensor_readings.device_id', '=', 'devices.id')
                ->where('sensor_type', 'accelerometer_y')
                ->where('devices.type', 'arduino_nano')
                ->latest('sensor_readings.created_at')
                ->value('sensor_readings.value') ?? 0,
            'z' => SensorReading::join('devices', 'sensor_readings.device_id', '=', 'devices.id')
                ->where('sensor_type', 'accelerometer_z')
                ->where('devices.type', 'arduino_nano')
                ->latest('sensor_readings.created_at')
                ->value('sensor_readings.value') ?? 0,
        ];

        // Also try to get data from server.php log file if no database data
        if ($latestReadings['temperature'] == 0) {
            $latestReadings = $this->getLatestFromLogFile();
        }

        return view('dashboard', compact('devices', 'latestReadings'));
    }

    private function getLatestFromLogFile()
    {
        $readings = ['temperature' => 0, 'humidity' => 0, 'x' => 0, 'y' => 0, 'z' => 0];

        $logPath = storage_path('logs/esp32.log');
        if (file_exists($logPath)) {
            $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (!empty($lines)) {
                $lastLine = end($lines);
                $data = json_decode($lastLine, true);
                if ($data) {
                    $readings['temperature'] = $data['temperature'] ?? 0;
                    $readings['humidity'] = $data['humidity'] ?? 0;
                    $readings['x'] = $data['x'] ?? 0;
                    $readings['y'] = $data['y'] ?? 0;
                    $readings['z'] = $data['z'] ?? 0;
                }
            }
        }

        return $readings;
    }

    public function getChartData(Request $request)
    {
        $sensorType = $request->get('sensor_type', 'temperature');
        $hours = $request->get('hours', 24);

        $readings = SensorReading::with('device')
            ->where('sensor_type', $sensorType)
            ->where('created_at', '>=', now()->subHours($hours))
            ->orderBy('created_at', 'asc')
            ->get();

        $chartData = $readings->map(function ($reading) {
            return [
                'x' => $reading->created_at->format('H:i:s'),
                'y' => (float) $reading->value,
                'device' => $reading->device->name ?? 'Unknown'
            ];
        });

        return response()->json($chartData);
    }

    public function getLiveData()
    {
        // Get latest readings from database - separate by device type
        $dbReadings = [
            'temperature' => SensorReading::join('devices', 'sensor_readings.device_id', '=', 'devices.id')
                ->where('sensor_type', 'temperature')
                ->where('devices.type', 'esp32')
                ->where('sensor_readings.value', '>', 0)
                ->latest('sensor_readings.created_at')
                ->first(['sensor_readings.value']),
            'humidity' => SensorReading::join('devices', 'sensor_readings.device_id', '=', 'devices.id')
                ->where('sensor_type', 'humidity')
                ->where('devices.type', 'esp32')
                ->where('sensor_readings.value', '>', 0)
                ->latest('sensor_readings.created_at')
                ->first(['sensor_readings.value']),
            'x' => SensorReading::join('devices', 'sensor_readings.device_id', '=', 'devices.id')
                ->where('sensor_readings.sensor_type', 'accelerometer_x')
                ->where('devices.type', 'arduino_nano')
                ->latest('sensor_readings.created_at')
                ->first(['sensor_readings.value']),
            'y' => SensorReading::join('devices', 'sensor_readings.device_id', '=', 'devices.id')
                ->where('sensor_readings.sensor_type', 'accelerometer_y')
                ->where('devices.type', 'arduino_nano')
                ->latest('sensor_readings.created_at')
                ->first(['sensor_readings.value']),
            'z' => SensorReading::join('devices', 'sensor_readings.device_id', '=', 'devices.id')
                ->where('sensor_readings.sensor_type', 'accelerometer_z')
                ->where('devices.type', 'arduino_nano')
                ->latest('sensor_readings.created_at')
                ->first(['sensor_readings.value']),
        ];

        // If no database data, try log file
        $logData = $this->getLatestFromLogFile();

        // Prepare response
        $response = [
            'temperature' => $dbReadings['temperature']->value ?? $logData['temperature'],
            'humidity' => $dbReadings['humidity']->value ?? $logData['humidity'],
            'x' => $dbReadings['x']->value ?? $logData['x'],
            'y' => $dbReadings['y']->value ?? $logData['y'],
            'z' => $dbReadings['z']->value ?? $logData['z'],
            'y' => $dbReadings['y']->value ?? $logData['y'],
            'z' => $dbReadings['z']->value ?? $logData['z'],
            'timestamp' => now()->format('H:i:s'),
        ];

        return response()->json($response);
    }

    public function getHistoricalData(Request $request)
    {
        $hours = $request->get('hours', 1);

        // Get data from database
        $data = SensorReading::select('sensor_type', 'value', 'created_at')
            ->where('created_at', '>=', now()->subHours($hours))
            ->orderBy('created_at', 'asc')
            ->get()
            ->groupBy('sensor_type');

        $response = [
            'temperature' => [],
            'humidity' => [],
            'accelerometer' => []
        ];

        // Process temperature data
        if ($data->has('temperature')) {
            $response['temperature'] = $data['temperature']->map(function ($reading) {
                return [
                    'x' => $reading->created_at->format('H:i:s'),
                    'y' => (float) $reading->value
                ];
            })->values();
        }

        // Process humidity data
        if ($data->has('humidity')) {
            $response['humidity'] = $data['humidity']->map(function ($reading) {
                return [
                    'x' => $reading->created_at->format('H:i:s'),
                    'y' => (float) $reading->value
                ];
            })->values();
        }

        // Process accelerometer data
        $accelData = ['x' => [], 'y' => [], 'z' => []];
        foreach (['accelerometer_x', 'accelerometer_y', 'accelerometer_z'] as $axis) {
            $axisLabel = substr($axis, -1); // Get last character (x, y, or z)
            if ($data->has($axis)) {
                $accelData[$axisLabel] = $data[$axis]->map(function ($reading) {
                    return [
                        'x' => $reading->created_at->format('H:i:s'),
                        'y' => (float) $reading->value
                    ];
                })->values();
            }
        }
        $response['accelerometer'] = $accelData;

        return response()->json($response);
    }
}
