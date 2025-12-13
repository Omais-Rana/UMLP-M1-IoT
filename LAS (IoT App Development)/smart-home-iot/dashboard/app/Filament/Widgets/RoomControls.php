<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class RoomControls extends Widget
{
    protected string $view = 'filament.widgets.room-controls';

    // State of the light
    public bool $isOn = false;

    public function toggleLight()
    {
        $this->isOn = !$this->isOn;
        $payload = $this->isOn ? 'on' : 'off';

        // 1. Connect to MQTT Broker
        // NOTE: Ensure these match your Docker IP and Port
        $server   = 'localhost';
        $port     = 1883;
        $clientId = 'laravel-sender';

        try {
            $mqtt = new \PhpMqtt\Client\MqttClient($server, $port, $clientId);
            $mqtt->connect();

            // 2. Publish the Command
            // Topic: home/livingroom/light
            $mqtt->publish('home/livingroom/light', $payload, 0);

            $mqtt->disconnect();

            // Optional: Show notification
            // Notification::make()->title('Light turned ' . $payload)->success()->send();

        } catch (\Exception $e) {
            // Log error if needed
        }
    }
}
