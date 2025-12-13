<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RoomStats extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Rooms', '4')
                ->description('Living Room, Dining Room, Bed Room, Kitchen')
                ->descriptionIcon('heroicon-m-home')
                ->color('success'),

            Stat::make('Temperature Sensors', '4')
                ->description('One sensor per room')
                ->descriptionIcon('heroicon-m-fire')
                ->color('danger'),

            Stat::make('Humidity Sensors', '4')
                ->description('Monitoring all rooms')
                ->descriptionIcon('heroicon-m-cloud')
                ->color('info'),

            Stat::make('Camera Feeds', '4')
                ->description('Active monitoring')
                ->descriptionIcon('heroicon-m-video-camera')
                ->color('warning'),
        ];
    }
}
