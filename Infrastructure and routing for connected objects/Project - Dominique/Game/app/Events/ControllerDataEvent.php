<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ControllerDataEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The controller data packet.
     * Contains GX, GY, GZ (gyro rates), JX, JY (joystick), F (fire), R (recenter)
     */
    public $data;

    public function __construct(array $data)
    {
        // $data example: ['GX' => 10.5, 'GY' => -5.2, 'GZ' => 0.1, 'JX' => 1800, 'JY' => 1400, 'F' => 0, 'R' => 0]
        $this->data = $data;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('game-controls'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'data.received';
    }
}
