<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return array_filter(
            parent::getWidgets(),
            fn($widget) => !is_string($widget) || !in_array($widget, [
                \App\Filament\Widgets\RoomControls::class,
                \App\Filament\Widgets\DoorStatus::class,
            ])
        );
    }
}
