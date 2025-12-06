<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-2 border border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center px-4 py-2 border-b dark:border-gray-700">
                <h3 class="text-lg font-semibold dark:text-white">🌡️ Temperature</h3>
                <span class="text-xs font-mono text-gray-500">Live</span>
            </div>
            <div class="w-full h-[300px]">
                <iframe src="{{ $grafanaUrl }}" width="100%" height="100%" frameborder="0">
                </iframe>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-2 border border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center px-4 py-2 border-b dark:border-gray-700">
                <h3 class="text-lg font-semibold dark:text-white">💧 Humidity</h3>
                <span class="text-xs font-mono text-gray-500">Live</span>
            </div>
            <div class="w-full h-[300px]">
                <iframe src="{{ $humidityUrl }}" width="100%" height="100%" frameborder="0">
                </iframe>
            </div>
        </div>

        @if ($cameraUrl)
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-4 border border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold mb-2 dark:text-white">📹 Live Feed</h3>
                <div class="aspect-video bg-black rounded overflow-hidden flex items-center justify-center relative">
                    <div class="absolute top-2 right-2 flex items-center gap-1">
                        <div class="w-2 h-2 bg-red-600 rounded-full animate-pulse"></div>
                        <span class="text-xs text-white font-bold">LIVE</span>
                    </div>
                    <img src="{{ $cameraUrl }}" class="h-full w-full object-cover opacity-90">
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
