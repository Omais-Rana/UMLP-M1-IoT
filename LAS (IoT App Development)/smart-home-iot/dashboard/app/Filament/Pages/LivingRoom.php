<?php

namespace App\Filament\Pages;

use BackedEnum;

class LivingRoom extends BaseRoomPage
{
    // Sidebar Config

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Living Room';
    protected static ?int $navigationSort = 1;

    // Room Specifics
    protected function getGrafanaPanelId(): int
    {
        return 1; // The ID of your Gauge panel
    }

    protected function getCameraUrl(): ?string
    {
        return 'https://media.giphy.com/media/L0HfI57n4ydAI/giphy.gif';
    }
}
