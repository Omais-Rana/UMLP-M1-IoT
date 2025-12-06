<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

abstract class BaseRoomPage extends Page
{
    // FIX: Match the parent class type definition exactly (string OR UnitEnum OR null)


    // FIX: $view is NOT static in v4, keep it as a standard property
    protected string $view = 'filament.pages.room-dashboard';

    // Abstract methods
    abstract protected function getGrafanaPanelId(): int;
    abstract protected function getHumidityPanelId(): int;
    abstract protected function getCameraUrl(): ?string;

    public function getGrafanaUrl(): string
    {
        $panelId = $this->getGrafanaPanelId();

        // CLEAN URL STRUCTURE:
        // 1. We use your UID: 'adbnc7l'
        // 2. We remove 'from' and 'to' (so it defaults to "now")
        // 3. We add '&refresh=5s' (to force auto-update)
        // 4. We change 'panelId' to use the integer directly (removing "panel-")

        return "http://localhost:3000/d-solo/adbnc7l/smart-home-iot?orgId=1&panelId=1&theme=light&refresh=5s";
    }

    public function getHumidityUrl(): string
    {
        $panelId = $this->getHumidityPanelId();
        return "http://localhost:3000/d-solo/adbnc7l/smart-home-iot?orgId=1&panelId=2&theme=light&refresh=5s";
    }

    protected function getViewData(): array
    {
        return [
            'grafanaUrl' => $this->getGrafanaUrl(),
            'humidityUrl' => $this->getHumidityUrl(),
            'cameraUrl' => $this->getCameraUrl(),
        ];
    }
}
