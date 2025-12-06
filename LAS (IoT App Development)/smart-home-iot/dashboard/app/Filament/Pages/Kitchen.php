<?php

namespace App\Filament\Pages;

use BackedEnum;

class Kitchen extends BaseRoomPage
{
    // Sidebar Config
    protected static string|\UnitEnum|null $navigationGroup = 'Rooms';
    protected static ?string $navigationLabel = 'Kitchen';
    protected static ?int $navigationSort = 4;

    // Room Specifics
    protected function getGrafanaPanelId(): int
    {
        return 1; // The ID of your Gauge panel
    }

    protected function getHumidityPanelId(): int
    {
        return 2; // REPLACE with your new Humidity Panel ID
    }

    protected function getCameraUrl(): ?string
    {
        // 1. Use 'localhost' because the camera is on the same machine
        // 2. Use '/live.mjpg' to match the specific endpoint we defined in FFmpeg
        return 'http://localhost:8080/live.mjpg';
    }
}
