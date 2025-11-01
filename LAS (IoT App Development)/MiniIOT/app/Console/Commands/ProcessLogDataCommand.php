<?php

namespace App\Console\Commands;

use App\Models\Device;
use App\Models\SensorReading;
use Illuminate\Console\Command;

class ProcessLogDataCommand extends Command
{
    protected $signature = 'iot:process-logs {--clear : Clear existing processed logs}';
    protected $description = 'Process IoT sensor data from server.php log files';

    public function handle()
    {
        $logPath = storage_path('logs/esp32.log');
        $processedLogPath = storage_path('logs/esp32_processed.log');

        if (!file_exists($logPath)) {
            $this->warn("Log file not found at: $logPath");
            return Command::FAILURE;
        }

        // Track processed lines
        $lastProcessedLine = 0;
        if (file_exists($processedLogPath) && !$this->option('clear')) {
            $lastProcessedLine = (int) file_get_contents($processedLogPath);
        }

        $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $totalLines = count($lines);

        if ($lastProcessedLine >= $totalLines) {
            $this->info('No new data to process.');
            return Command::SUCCESS;
        }

        $newLines = array_slice($lines, $lastProcessedLine);
        $processedCount = 0;

        $this->info("Processing " . count($newLines) . " new log entries...");

        foreach ($newLines as $index => $line) {
            $lineNumber = $lastProcessedLine + $index + 1;

            try {
                $data = json_decode($line, true);

                if ($data && $this->isValidSensorData($data)) {
                    $this->processSensorData($data);
                    $processedCount++;
                }
            } catch (\Exception $e) {
                $this->warn("Error processing line $lineNumber: " . $e->getMessage());
            }
        }

        // Update processed line count
        file_put_contents($processedLogPath, $lastProcessedLine + count($newLines));

        $this->info("Successfully processed $processedCount sensor readings.");
        return Command::SUCCESS;
    }

    private function isValidSensorData($data): bool
    {
        // Handle multiple formats: 
        // 1. {temp, hum} - ESP32 format
        // 2. {temperature, humidity, x, y, z} - full format
        // 3. {x, y, z} - accelerometer only format (Nano)
        return is_array($data) && (
            (isset($data['temp']) && isset($data['hum'])) ||
            (isset($data['temperature']) && isset($data['humidity'])) ||
            (isset($data['temperature']) && isset($data['humidity']) && isset($data['x']) && isset($data['y']) && isset($data['z'])) ||
            (isset($data['x']) && isset($data['y']) && isset($data['z'])) // Accelerometer only
        );
    }
    private function processSensorData($data)
    {
        // Find or create device based on IP or MAC address
        $deviceName = $data['device_name'] ?? 'TCP Client Device';

        // Auto-detect device type based on data format
        if (isset($data['device_type']) && $data['device_type'] === 'esp32') {
            $deviceType = 'esp32';  // ESP32 explicitly identified
            $deviceName = 'ESP32 TCP Sensor';
        } elseif (isset($data['x']) && isset($data['y']) && isset($data['z']) && !isset($data['temp']) && !isset($data['temperature'])) {
            $deviceType = 'arduino_nano';  // Accelerometer-only = Nano
            $deviceName = 'Arduino Nano Accelerometer';
        } elseif (isset($data['temperature']) || isset($data['temp']) || isset($data['humidity']) || isset($data['hum'])) {
            $deviceType = 'esp32';  // Has temp/humidity = ESP32
            $deviceName = 'ESP32 TCP Sensor';
        } else {
            $deviceType = 'unknown';  // Fallback
            $deviceName = 'Unknown TCP Device';
        }

        $macAddress = $data['mac_address'] ?? null;
        $ipAddress = $data['ip_address'] ?? $data['ip'] ?? '127.0.0.1';

        // Try to find device by MAC address first, then by IP+type combination
        $device = null;
        if ($macAddress) {
            $device = Device::where('mac_address', $macAddress)->first();
        }

        if (!$device) {
            // Look for device by IP address AND type to support multiple devices from same IP
            $device = Device::where('ip_address', $ipAddress)
                ->where('type', $deviceType)
                ->first();
        }

        if (!$device) {
            $device = Device::create([
                'name' => $deviceName,
                'type' => $deviceType,
                'mac_address' => $macAddress,
                'ip_address' => $ipAddress,
                'location' => $data['location'] ?? 'TCP Server',
                'description' => 'Device created from server.php log data'
            ]);
        } else {
            // Update device type and name if different based on current data
            if ($device->type !== $deviceType) {
                $device->update([
                    'type' => $deviceType,
                    'name' => $deviceName
                ]);
            }
        }

        // Create timestamp (use provided timestamp or current time)
        $timestamp = isset($data['timestamp'])
            ? \Carbon\Carbon::createFromTimestamp($data['timestamp'])
            : now();

        // Handle different data formats
        $sensorReadings = [];

        // Temperature (only add if present in data)
        if (isset($data['temperature'])) {
            $sensorReadings[] = ['sensor_type' => 'temperature', 'value' => $data['temperature']];
        } elseif (isset($data['temp'])) {
            $sensorReadings[] = ['sensor_type' => 'temperature', 'value' => $data['temp']];
        }

        // Humidity (only add if present in data)
        if (isset($data['humidity'])) {
            $sensorReadings[] = ['sensor_type' => 'humidity', 'value' => $data['humidity']];
        } elseif (isset($data['hum'])) {
            $sensorReadings[] = ['sensor_type' => 'humidity', 'value' => $data['hum']];
        }

        // Accelerometer data (only add if present in data)
        if (isset($data['x'])) {
            $sensorReadings[] = ['sensor_type' => 'accelerometer_x', 'value' => $data['x']];
        }
        if (isset($data['y'])) {
            $sensorReadings[] = ['sensor_type' => 'accelerometer_y', 'value' => $data['y']];
        }
        if (isset($data['z'])) {
            $sensorReadings[] = ['sensor_type' => 'accelerometer_z', 'value' => $data['z']];
        }

        foreach ($sensorReadings as $reading) {
            SensorReading::create([
                'device_id' => $device->id,
                'sensor_type' => $reading['sensor_type'],
                'value' => $reading['value'],
                'unit' => $this->getUnit($reading['sensor_type']),
                'created_at' => $timestamp,
                'updated_at' => $timestamp
            ]);
        }

        // Update device status and last seen
        $device->update([
            'status' => 'online',
            'last_seen_at' => $timestamp
        ]);
    }

    private function getUnit($sensorType): string
    {
        return match ($sensorType) {
            'temperature' => '°C',
            'humidity' => '%',
            'accelerometer_x', 'accelerometer_y', 'accelerometer_z' => 'm/s²',
            default => ''
        };
    }
}
