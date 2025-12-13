<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\HomeDevice; // Import your new Model

class DoorStatus extends Widget
{
    protected string $view = 'filament.widgets.door-status';

    // Refresh every 2 seconds to see changes
    protected int | string | array $pollingInterval = '2s';

    public bool $isOpen = false;

    public function mount()
    {
        $this->refreshStatus();
    }

    // 1. Read from MySQL
    public function refreshStatus()
    {
        // Find the device by name
        $door = HomeDevice::where('name', 'Front Door')->first();

        if ($door) {
            $this->isOpen = (bool) $door->state;
        }
    }

    // 2. Simulate an Event (Clicking the widget toggles the door)
    public function toggleDoor()
    {
        $door = HomeDevice::where('name', 'Front Door')->first();

        if ($door) {
            $door->state = !$door->state; // Flip the state
            $door->save();

            $this->isOpen = $door->state;
        }
    }
}
