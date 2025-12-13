<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Temperature & Humidity Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div
                style="background-color: white; border-radius: 12px; box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1); padding: 8px; border: 1px solid #e5e7eb;">
                <div
                    style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border-bottom: 1px solid #e5e7eb;">
                    <h3
                        style="font-size: 18px; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 24px;">🌡️</span>
                        <span>Temperature</span>
                    </h3>
                    <span
                        style="font-size: 11px; font-family: monospace; color: #6b7280; background-color: #f3f4f6; padding: 4px 8px; border-radius: 6px; font-weight: 600;">LIVE</span>
                </div>
                <div style="width: 100%; height: 300px;">
                    <iframe src="{{ $grafanaUrl }}" width="100%" height="100%" frameborder="0">
                    </iframe>
                </div>
            </div>

            <div
                style="background-color: white; border-radius: 12px; box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1); padding: 8px; border: 1px solid #e5e7eb;">
                <div
                    style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border-bottom: 1px solid #e5e7eb;">
                    <h3
                        style="font-size: 18px; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 24px;">💧</span>
                        <span>Humidity</span>
                    </h3>
                    <span
                        style="font-size: 11px; font-family: monospace; color: #6b7280; background-color: #f3f4f6; padding: 4px 8px; border-radius: 6px; font-weight: 600;">LIVE</span>
                </div>
                <div style="width: 100%; height: 300px;">
                    <iframe src="{{ $humidityUrl }}" width="100%" height="100%" frameborder="0">
                    </iframe>
                </div>
            </div>
        </div>

        <!-- Camera Feed -->
        @if ($cameraUrl)
            <div
                style="background-color: white; border-radius: 12px; box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1); padding: 16px; border: 1px solid #e5e7eb;">
                <h3
                    style="font-size: 18px; font-weight: 700; color: #111827; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 24px;">📹</span>
                    <span>Camera Feed</span>
                </h3>
                <div
                    style="aspect-ratio: 16/9; background-color: black; border-radius: 8px; overflow: hidden; display: flex; align-items: center; justify-content: center; position: relative;">
                    <div
                        style="position: absolute; top: 8px; right: 8px; display: flex; align-items: center; gap: 4px; z-index: 10;">
                        <div
                            style="width: 8px; height: 8px; background-color: #dc2626; border-radius: 50%; animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;">
                        </div>
                        <span style="font-size: 12px; color: white; font-weight: 700;">LIVE</span>
                    </div>
                    <img src="{{ $cameraUrl }}" style="height: 100%; width: 100%; object-fit: cover;">
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
