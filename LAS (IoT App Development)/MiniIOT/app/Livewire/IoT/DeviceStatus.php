<?php

namespace App\Livewire\IoT;

use App\Models\Device;
use App\Models\SensorReading;
use Livewire\Component;
use Livewire\Attributes\On;

class DeviceStatus extends Component
{
    public $devices = [];
    public $latestReadings = [];
    public $deviceStats = [];
    public $lastUpdate;

    public function mount()
    {
        $this->loadData();
        $this->lastUpdate = now();
    }

    #[On('refresh-data')]
    public function loadData()
    {
        // Get device statistics
        $this->deviceStats = [
            'total_devices' => Device::count(),
            'online_devices' => Device::where('status', 'online')
                ->where('last_seen_at', '>=', now()->subMinutes(5))
                ->count(),
            'arduino_devices' => Device::where('type', 'arduino_nano')->count(),
            'esp32_devices' => Device::where('type', 'esp32')->count(),
        ];

        // Get all devices with their latest readings
        $this->devices = Device::with(['sensorReadings' => function ($query) {
            $query->latest('created_at')->limit(5);
        }])->get();

        // Get latest readings by sensor type
        $this->latestReadings = SensorReading::whereIn('sensor_type', ['temperature', 'humidity', 'pressure', 'light'])
            ->latest('created_at')
            ->get()
            ->groupBy('sensor_type')
            ->map(function ($readings) {
                return $readings->first();
            });

        $this->lastUpdate = now();
    }

    public function refreshData()
    {
        $this->loadData();
        $this->dispatch('data-refreshed');
    }

    public function render()
    {
        return view('livewire.io-t.device-status');
    }
}
