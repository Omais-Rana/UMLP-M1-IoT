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
     * Contains P, Y, JX, JY, B
     */
    public $data;

    public function __construct(array $data)
    {
        // $data example: ['P' => 10.5, 'Y' => -5.2, 'JX' => 1800, 'JY' => 1400, 'B' => 0]
        $this->data = $data;
    }

    /**
     * Broadcast on a public channel.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('game-controls'),
        ];
    }

    /**
     * The event name to listen for in the frontend via Laravel Echo.
     */
    public function broadcastAs(): string
    {
        return 'data.received';
    }
}
