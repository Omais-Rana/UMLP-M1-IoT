<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <div
            class="col-span-1 lg:col-span-2 bg-white dark:bg-gray-900 rounded-xl shadow p-2 border border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center px-4 py-2 border-b dark:border-gray-700">
                <h3 class="text-lg font-semibold dark:text-white">📊 Environment Stats</h3>
                <span class="text-xs font-mono text-gray-500">Live Data</span>
            </div>

            <div class="w-full h-[400px]">
                <iframe src="{{ $grafanaUrl }}" width="100%" height="100%" frameborder="0">
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
                    <img src="{{ $cameraUrl }}" class="w-full opacity-90 object-cover">
                </div>
            </div>
        @endif

        <div
            class="bg-white dark:bg-gray-900 rounded-xl shadow p-4 border border-gray-200 dark:border-gray-700 flex flex-col justify-center gap-4">
            <h3 class="text-lg font-semibold dark:text-white">⚡ Controls</h3>
            <button
                class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold transition flex items-center justify-center gap-2">
                <span>💡 Toggle Lights</span>
            </button>
        </div>

    </div>
</x-filament-panels::page>
