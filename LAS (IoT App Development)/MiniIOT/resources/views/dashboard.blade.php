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

        <!-- Charts Section -->
        <div
            class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6">
            <div class="flex flex-wrap justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">Real-time Sensor Data</h3>
                <div class="flex space-x-2 flex-wrap">
                    <select id="timeRange"
                        class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="1">Last 1 hour</option>
                        <option value="6">Last 6 hours</option>
                        <option value="24" selected>Last 24 hours</option>
                    </select>
                    <button id="autoRefresh"
                        class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-md transition-colors">
                        <span id="refreshStatus">Auto Refresh: ON</span>
                    </button>
                </div>
            </div>

            <!-- Chart Tabs -->
            <div class="border-b border-gray-200 dark:border-gray-700 mb-4">
                <nav class="-mb-px flex space-x-8">
                    <button
                        class="chart-tab active py-2 px-1 border-b-2 border-blue-500 font-medium text-sm text-blue-600 dark:text-blue-400"
                        data-chart="temperature">
                        Temperature
                    </button>
                    <button
                        class="chart-tab py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                        data-chart="humidity">
                        Humidity
                    </button>
                    <button
                        class="chart-tab py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                        data-chart="accelerometer">
                        Accelerometer
                    </button>
                </nav>
            </div>

            <!-- Chart Container -->
            <div class="h-[500px]">
                <canvas id="sensorChart" height="400"></canvas>
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
                                        @php
                                            $isOnline =
                                                $device->sensorReadings->count() > 0 &&
                                                $device->sensorReadings->first()->created_at > now()->subMinutes(5);
                                        @endphp
                                        <div
                                            class="w-10 h-10 rounded-full flex items-center justify-center {{ $isOnline ? 'bg-green-100 dark:bg-green-900' : 'bg-red-100 dark:bg-red-900' }}">
                                            @if ($device->type === 'arduino_nano')
                                                <svg class="w-5 h-5 {{ $isOnline ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                                                    </path>
                                                </svg>
                                            @else
                                                <svg class="w-5 h-5 {{ $isOnline ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}"
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
                                            class="text-sm {{ $isOnline ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            {{ $isOnline ? 'Online' : 'Offline' }}
                                        </p>
                                        @if ($device->sensorReadings->count() > 0)
                                            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                                {{ $device->sensorReadings->first()->created_at->diffForHumans() }}
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

    <!-- Chart.js and Real-time Updates Script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        let sensorChart;
        let autoRefreshInterval;
        let isAutoRefreshEnabled = true;
        let currentChartType = 'temperature';

        const colors = {
            temperature: '#dc2626',
            humidity: '#2563eb',
            accelerometer_x: '#16a34a',
            accelerometer_y: '#d97706',
            accelerometer_z: '#7c3aed'
        };

        // Initialize Chart
        function initChart() {
            const ctx = document.getElementById('sensorChart').getContext('2d');

            // Create gradient backgrounds
            const temperatureGradient = ctx.createLinearGradient(0, 0, 0, 400);
            temperatureGradient.addColorStop(0, 'rgba(239, 68, 68, 0.2)');
            temperatureGradient.addColorStop(1, 'rgba(239, 68, 68, 0.0)');

            const humidityGradient = ctx.createLinearGradient(0, 0, 0, 400);
            humidityGradient.addColorStop(0, 'rgba(59, 130, 246, 0.2)');
            humidityGradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)');

            sensorChart = new Chart(ctx, {
                type: 'line',
                data: {
                    datasets: []
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                color: document.documentElement.classList.contains('dark') ? '#f5f5f5' : '#374151',
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        title: {
                            display: true,
                            text: 'Temperature Data',
                            color: document.documentElement.classList.contains('dark') ? '#f5f5f5' : '#374151',
                            font: {
                                size: 16,
                                weight: 'bold'
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: '#374151',
                            borderWidth: 1,
                            cornerRadius: 8,
                            displayColors: true
                        }
                    },
                    scales: {
                        x: {
                            type: 'category',
                            display: true,
                            title: {
                                display: true,
                                text: 'Time',
                                color: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280'
                            },
                            ticks: {
                                color: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280'
                            },
                            grid: {
                                color: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb'
                            }
                        },
                        y: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Value',
                                color: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280'
                            },
                            ticks: {
                                color: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280'
                            },
                            grid: {
                                color: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb'
                            }
                        }
                    },
                    elements: {
                        point: {
                            radius: 3,
                            hoverRadius: 6,
                            borderWidth: 2
                        },
                        line: {
                            tension: 0.4,
                            borderWidth: 3
                        }
                    },
                    animation: {
                        duration: 1000,
                        easing: 'easeInOutQuart'
                    }
                }
            });
        }

        // Update live values with animation
        function updateLiveValues() {
            fetch('/api/live-data')
                .then(response => response.json())
                .then(data => {
                    // Animate temperature
                    animateValue('current-temp', parseFloat(data.temperature), 1, '°C');
                    // Animate humidity
                    animateValue('current-humidity', parseFloat(data.humidity), 1, '%');
                    // Animate accelerometer values
                    animateValue('current-accel-x', parseFloat(data.x), 2, '');
                    animateValue('current-accel-y', parseFloat(data.y), 2, '');
                    animateValue('current-accel-z', parseFloat(data.z), 2, '');
                })
                .catch(error => {
                    console.error('Error fetching live data:', error);
                });
        }

        // Animate value changes
        function animateValue(elementId, newValue, decimals, suffix) {
            const element = document.getElementById(elementId);
            if (!element) return;

            const currentValue = parseFloat(element.textContent) || 0;
            const difference = newValue - currentValue;
            const steps = 20;
            const stepValue = difference / steps;
            let current = currentValue;
            let step = 0;

            const timer = setInterval(() => {
                step++;
                current += stepValue;

                if (step >= steps) {
                    current = newValue;
                    clearInterval(timer);
                }

                element.textContent = current.toFixed(decimals) + suffix;
            }, 50);
        }

        // Update chart data
        function updateChartData() {
            const hours = document.getElementById('timeRange').value;

            fetch(`/api/historical-data?hours=${hours}`)
                .then(response => response.json())
                .then(data => {
                    let datasets = [];
                    let title = '';

                    if (currentChartType === 'temperature') {
                        datasets.push({
                            label: 'Temperature (°C)',
                            data: data.temperature,
                            borderColor: colors.temperature,
                            backgroundColor: colors.temperature + '20',
                            fill: true,
                            pointBackgroundColor: colors.temperature,
                            pointBorderColor: '#fff',
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: colors.temperature
                        });
                        title = 'Temperature Data';
                        sensorChart.options.scales.y.title.text = 'Temperature (°C)';

                    } else if (currentChartType === 'humidity') {
                        datasets.push({
                            label: 'Humidity (%)',
                            data: data.humidity,
                            borderColor: colors.humidity,
                            backgroundColor: colors.humidity + '20',
                            fill: true,
                            pointBackgroundColor: colors.humidity,
                            pointBorderColor: '#fff',
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: colors.humidity
                        });
                        title = 'Humidity Data';
                        sensorChart.options.scales.y.title.text = 'Humidity (%)';

                    } else if (currentChartType === 'accelerometer') {
                        datasets.push({
                            label: 'X-axis',
                            data: data.accelerometer.x,
                            borderColor: colors.accelerometer_x,
                            backgroundColor: colors.accelerometer_x + '20',
                            fill: false,
                            pointBackgroundColor: colors.accelerometer_x,
                            pointBorderColor: '#fff'
                        }, {
                            label: 'Y-axis',
                            data: data.accelerometer.y,
                            borderColor: colors.accelerometer_y,
                            backgroundColor: colors.accelerometer_y + '20',
                            fill: false,
                            pointBackgroundColor: colors.accelerometer_y,
                            pointBorderColor: '#fff'
                        }, {
                            label: 'Z-axis',
                            data: data.accelerometer.z,
                            borderColor: colors.accelerometer_z,
                            backgroundColor: colors.accelerometer_z + '20',
                            fill: false,
                            pointBackgroundColor: colors.accelerometer_z,
                            pointBorderColor: '#fff'
                        });
                        title = 'Accelerometer Data';
                        sensorChart.options.scales.y.title.text = 'Acceleration';
                    }

                    sensorChart.data.datasets = datasets;
                    sensorChart.options.plugins.title.text = title;
                    sensorChart.update('none');
                })
                .catch(error => {
                    console.error('Error fetching chart data:', error);
                });
        }

        // Initialize everything when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize chart
            initChart();

            // Initial data load
            updateLiveValues();
            updateChartData();

            // Chart tab event listeners
            document.querySelectorAll('.chart-tab').forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs
                    document.querySelectorAll('.chart-tab').forEach(t => {
                        t.classList.remove('active', 'border-blue-500', 'text-blue-600',
                            'dark:text-blue-400');
                        t.classList.add('border-transparent', 'text-gray-500',
                            'hover:text-gray-700', 'dark:text-gray-400',
                            'dark:hover:text-gray-300');
                    });

                    // Add active class to clicked tab
                    this.classList.add('active', 'border-blue-500', 'text-blue-600',
                        'dark:text-blue-400');
                    this.classList.remove('border-transparent', 'text-gray-500',
                        'hover:text-gray-700', 'dark:text-gray-400', 'dark:hover:text-gray-300');

                    // Update current chart type
                    currentChartType = this.dataset.chart;
                    updateChartData();
                });
            });

            // Time range change event
            document.getElementById('timeRange').addEventListener('change', function() {
                updateChartData();
            });

            // Auto refresh toggle
            document.getElementById('autoRefresh').addEventListener('click', function() {
                isAutoRefreshEnabled = !isAutoRefreshEnabled;
                const statusText = document.getElementById('refreshStatus');

                if (isAutoRefreshEnabled) {
                    statusText.textContent = 'Auto Refresh: ON';
                    this.classList.remove('bg-gray-500');
                    this.classList.add('bg-blue-500', 'hover:bg-blue-600');
                    startAutoRefresh();
                } else {
                    statusText.textContent = 'Auto Refresh: OFF';
                    this.classList.remove('bg-blue-500', 'hover:bg-blue-600');
                    this.classList.add('bg-gray-500');
                    stopAutoRefresh();
                }
            });

            // Start auto refresh
            startAutoRefresh();
        });

        function startAutoRefresh() {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
            }

            if (isAutoRefreshEnabled) {
                autoRefreshInterval = setInterval(function() {
                    updateLiveValues();
                    updateChartData();
                }, 1000); // Update every 1 second (matches sensor frequency)
            }
        }

        function stopAutoRefresh() {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
            }
        }

        // Clean up on page unload
        window.addEventListener('beforeunload', function() {
            stopAutoRefresh();
        });

        // Handle dark mode changes
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'class') {
                    // Update chart colors for dark mode
                    if (sensorChart) {
                        const isDark = document.documentElement.classList.contains('dark');
                        sensorChart.options.plugins.legend.labels.color = isDark ? '#f5f5f5' : '#374151';
                        sensorChart.options.plugins.title.color = isDark ? '#f5f5f5' : '#374151';
                        sensorChart.options.scales.x.title.color = isDark ? '#9ca3af' : '#6b7280';
                        sensorChart.options.scales.y.title.color = isDark ? '#9ca3af' : '#6b7280';
                        sensorChart.options.scales.x.ticks.color = isDark ? '#9ca3af' : '#6b7280';
                        sensorChart.options.scales.y.ticks.color = isDark ? '#9ca3af' : '#6b7280';
                        sensorChart.options.scales.x.grid.color = isDark ? '#374151' : '#e5e7eb';
                        sensorChart.options.scales.y.grid.color = isDark ? '#374151' : '#e5e7eb';
                        sensorChart.update('none');
                    }
                }
            });
        });

        observer.observe(document.documentElement, {
            attributes: true
        });
    </script>
</x-layouts.app>
