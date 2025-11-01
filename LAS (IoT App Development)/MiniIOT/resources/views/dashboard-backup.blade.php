<x-layouts.app :title="__('IoT Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">

        <!-- Sensor Data Cards - Vertical Stack -->
        <div class="space-y-4">
            <!-- Temperature Card -->
            <div
                class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">Temperature</p>
                        <p class="text-3xl font-bold text-red-600 dark:text-red-400" id="current-temp">
                            {{ number_format($latestReadings['temperature'] ?? 0, 1) }}°C</p>
                    </div>
                    <div class="p-3 rounded-full bg-red-100 dark:bg-red-900">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Humidity Card -->
            <div
                class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">Humidity</p>
                        <p class="text-3xl font-bold text-blue-600 dark:text-blue-400" id="current-humidity">
                            {{ number_format($latestReadings['humidity'] ?? 0, 1) }}%</p>
                    </div>
                    <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Accelerometer Card -->
            <div
                class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">Accelerometer</p>
                        <div class="flex space-x-4 mt-2">
                            <div>
                                <p class="text-sm text-neutral-500">X</p>
                                <p class="text-xl font-bold text-green-600 dark:text-green-400" id="current-accel-x">
                                    {{ number_format($latestReadings['x'] ?? 0, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-neutral-500">Y</p>
                                <p class="text-xl font-bold text-yellow-600 dark:text-yellow-400" id="current-accel-y">
                                    {{ number_format($latestReadings['y'] ?? 0, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-neutral-500">Z</p>
                                <p class="text-xl font-bold text-purple-600 dark:text-purple-400" id="current-accel-z">
                                    {{ number_format($latestReadings['z'] ?? 0, 2) }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <!-- Connected Devices -->
        <div
            class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-4">Connected Devices</h3>

                @if ($devices->count() > 0)
                    <div class="space-y-4">
                        @foreach ($devices as $device)
                            <div
                                class="flex items-center justify-between p-4 rounded-lg border border-neutral-200 dark:border-neutral-700">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-10 h-10 rounded-full flex items-center justify-center {{ $device->isOnline() ? 'bg-green-100 dark:bg-green-900' : 'bg-red-100 dark:bg-red-900' }}">
                                            @if ($device->type === 'arduino_nano')
                                                <svg class="w-5 h-5 {{ $device->isOnline() ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                                                    </path>
                                                </svg>
                                            @else
                                                <svg class="w-5 h-5 {{ $device->isOnline() ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0">
                                                    </path>
                                                </svg>
                                            @endif
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                            {{ $device->name }}</p>
                                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                            {{ ucwords(str_replace('_', ' ', $device->type)) }}
                                            @if ($device->location)
                                                • {{ $device->location }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <div class="text-right">
                                        <p
                                            class="text-sm {{ $device->isOnline() ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            {{ $device->isOnline() ? 'Online' : 'Offline' }}
                                        </p>
                                        @if ($device->last_seen_at)
                                            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                                {{ $device->last_seen_at->diffForHumans() }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 text-neutral-400 dark:text-neutral-600 mx-auto mb-4" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z">
                            </path>
                        </svg>
                        <p class="text-neutral-500 dark:text-neutral-400">No devices connected yet</p>
                        <p class="text-sm text-neutral-400 dark:text-neutral-500 mt-2">Connect your Arduino Nano or
                            ESP32 devices to see them here</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
