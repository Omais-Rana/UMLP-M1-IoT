<div wire:poll.30s="refreshData" class="space-y-6">
    <!-- Header with last update time -->
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">IoT Dashboard</h2>
        <div class="flex items-center space-x-4">
            <span class="text-sm text-neutral-500 dark:text-neutral-400">
                Last updated: {{ $lastUpdate->format('H:i:s') }}
            </span>
            <button wire:click="refreshData"
                class="px-3 py-2 text-sm bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded-md hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors">
                <svg wire:loading.remove wire:target="refreshData" class="w-4 h-4 inline mr-1" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                    </path>
                </svg>
                <svg wire:loading wire:target="refreshData" class="w-4 h-4 inline mr-1 animate-spin" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                    </path>
                </svg>
                Refresh
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid auto-rows-min gap-4 md:grid-cols-4">
        <!-- Total Devices -->
        <div
            class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">Total Devices</p>
                    <p class="text-3xl font-bold text-neutral-900 dark:text-neutral-100">
                        {{ $deviceStats['total_devices'] }}</p>
                </div>
                <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z">
                        </path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Online Devices -->
        <div
            class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">Online Devices</p>
                    <p class="text-3xl font-bold text-green-600 dark:text-green-400">
                        {{ $deviceStats['online_devices'] }}</p>
                </div>
                <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                    <div class="relative">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        @if ($deviceStats['online_devices'] > 0)
                            <div class="absolute -top-1 -right-1 w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Arduino Devices -->
        <div
            class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">Arduino Nano</p>
                    <p class="text-3xl font-bold text-purple-600 dark:text-purple-400">
                        {{ $deviceStats['arduino_devices'] }}</p>
                </div>
                <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                        </path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- ESP32 Devices -->
        <div
            class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">ESP32</p>
                    <p class="text-3xl font-bold text-orange-600 dark:text-orange-400">
                        {{ $deviceStats['esp32_devices'] }}</p>
                </div>
                <div class="p-3 rounded-full bg-orange-100 dark:bg-orange-900">
                    <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0">
                        </path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Device List -->
    <div
        class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-4">Connected Devices</h3>

            @if ($devices->count() > 0)
                <div class="space-y-4">
                    @foreach ($devices as $device)
                        <div wire:key="device-{{ $device->id }}"
                            class="flex items-center justify-between p-4 rounded-lg border border-neutral-200 dark:border-neutral-700 transition-all duration-300 hover:shadow-md">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 {{ $device->isOnline() ? 'bg-green-100 dark:bg-green-900' : 'bg-red-100 dark:bg-red-900' }}">
                                        @if ($device->type === 'arduino_nano')
                                            <svg class="w-5 h-5 {{ $device->isOnline() ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                                                </path>
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5 {{ $device->isOnline() ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0">
                                                </path>
                                            </svg>
                                        @endif
                                        @if ($device->isOnline())
                                            <div class="absolute w-3 h-3 bg-green-400 rounded-full animate-ping"></div>
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
                                        class="text-sm transition-colors duration-300 {{ $device->isOnline() ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
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
                    <p class="text-sm text-neutral-400 dark:text-neutral-500 mt-2">Connect your Arduino Nano or ESP32
                        devices to see them here</p>
                </div>
            @endif
        </div>
    </div>
</div>
