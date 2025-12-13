<?php

namespace App\Filament\Pages;

use BackedEnum;

class LivingRoom extends BaseRoomPage
{
    // Sidebar Config
    protected static string|\UnitEnum|null $navigationGroup = 'Rooms';
    protected static ?string $navigationLabel = 'Living Room';
    protected static ?int $navigationSort = 1;

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\RoomControls::class,
            \App\Filament\Widgets\DoorStatus::class,
        ];
    }

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
        // REPLACE '192.168.1.XX' with the actual IP from your Serial Monitor.
        // Keep the ':81/stream' part—that is the specific port for video.
        return 'http://10.122.127.5:81/stream';
    }
}
