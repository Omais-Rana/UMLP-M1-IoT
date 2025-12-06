<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Smart Home IoT - Control Your Home</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>

<body
    class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 text-slate-900 dark:text-slate-100 min-h-screen font-sans antialiased">

    <!-- Navigation -->
    <nav
        class="fixed top-0 left-0 right-0 z-50 bg-white/80 dark:bg-slate-900/80 backdrop-blur-lg border-b border-slate-200 dark:border-slate-700">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span
                        class="text-xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-400 bg-clip-text text-transparent">Smart
                        Home IoT</span>
                </div>
                <div class="flex items-center gap-4">
                    @auth
                        <a href="{{ url('/admin') }}"
                            class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-lg font-medium transition-all shadow-lg hover:shadow-xl">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ url('/admin/login') }}"
                            class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-lg font-medium transition-all shadow-lg hover:shadow-xl">
                            Login
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="pt-32 pb-20 px-6">
        <div class="container mx-auto">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-16">
                    <h1
                        class="text-5xl md:text-6xl lg:text-7xl font-bold mb-6 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 dark:from-blue-400 dark:via-indigo-400 dark:to-purple-400 bg-clip-text text-transparent">
                        Control Your Home<br>From Anywhere
                    </h1>
                    <p class="text-xl md:text-2xl text-slate-600 dark:text-slate-300 mb-8 max-w-3xl mx-auto">
                        Monitor temperature, humidity, control lights, and view live camera feeds from all your smart
                        home devices in one unified dashboard.
                    </p>
                    @auth
                        <a href="{{ url('/admin') }}"
                            class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-lg rounded-xl font-semibold transition-all shadow-2xl hover:shadow-blue-500/50 hover:scale-105">
                            <span>Go to Dashboard</span>
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    @else
                        <a href="{{ url('/admin/login') }}"
                            class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-lg rounded-xl font-semibold transition-all shadow-2xl hover:shadow-blue-500/50 hover:scale-105">
                            <span>Get Started</span>
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    @endauth
                </div>

                <!-- Feature Cards -->
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8 mb-20">
                    <!-- Temperature Monitoring -->
                    <div
                        class="bg-white dark:bg-slate-800 rounded-2xl p-8 shadow-xl hover:shadow-2xl transition-all hover:-translate-y-2 border border-slate-200 dark:border-slate-700">
                        <div
                            class="w-16 h-16 bg-gradient-to-br from-orange-400 to-red-500 rounded-xl flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold mb-3 text-slate-900 dark:text-white">Temperature Control</h3>
                        <p class="text-slate-600 dark:text-slate-300">Real-time temperature monitoring with Grafana
                            dashboards showing live data from every room.</p>
                    </div>

                    <!-- Humidity Tracking -->
                    <div
                        class="bg-white dark:bg-slate-800 rounded-2xl p-8 shadow-xl hover:shadow-2xl transition-all hover:-translate-y-2 border border-slate-200 dark:border-slate-700">
                        <div
                            class="w-16 h-16 bg-gradient-to-br from-blue-400 to-cyan-500 rounded-xl flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold mb-3 text-slate-900 dark:text-white">Humidity Sensors</h3>
                        <p class="text-slate-600 dark:text-slate-300">Track humidity levels across your home to maintain
                            optimal comfort and air quality.</p>
                    </div>

                    <!-- Live Camera Feeds -->
                    <div
                        class="bg-white dark:bg-slate-800 rounded-2xl p-8 shadow-xl hover:shadow-2xl transition-all hover:-translate-y-2 border border-slate-200 dark:border-slate-700">
                        <div
                            class="w-16 h-16 bg-gradient-to-br from-purple-400 to-indigo-500 rounded-xl flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold mb-3 text-slate-900 dark:text-white">Live Camera Feeds</h3>
                        <p class="text-slate-600 dark:text-slate-300">Stream live video from cameras in each room for
                            complete home security and monitoring.</p>
                    </div>

                    <!-- Light Control -->
                    <div
                        class="bg-white dark:bg-slate-800 rounded-2xl p-8 shadow-xl hover:shadow-2xl transition-all hover:-translate-y-2 border border-slate-200 dark:border-slate-700">
                        <div
                            class="w-16 h-16 bg-gradient-to-br from-yellow-400 to-amber-500 rounded-xl flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold mb-3 text-slate-900 dark:text-white">Smart Lighting</h3>
                        <p class="text-slate-600 dark:text-slate-300">Control and automate lights throughout your home
                            with intelligent scheduling and scenes.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 px-6 bg-white dark:bg-slate-800">
        <div class="container mx-auto">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-4xl font-bold mb-6 text-slate-900 dark:text-white">Powered by Modern Technology</h2>
                <p class="text-xl text-slate-600 dark:text-slate-300 mb-12">Built with Laravel, Filament, and Grafana
                    for a seamless IoT experience</p>

                <div class="grid md:grid-cols-3 gap-8">
                    <div>
                        <div class="text-4xl mb-4">⚡</div>
                        <h3 class="text-lg font-semibold mb-2 text-slate-900 dark:text-white">Real-time Updates</h3>
                        <p class="text-slate-600 dark:text-slate-300 text-sm">Instant data refresh every 5 seconds</p>
                    </div>
                    <div>
                        <div class="text-4xl mb-4">📊</div>
                        <h3 class="text-lg font-semibold mb-2 text-slate-900 dark:text-white">Advanced Analytics</h3>
                        <p class="text-slate-600 dark:text-slate-300 text-sm">Grafana-powered visualizations</p>
                    </div>
                    <div>
                        <div class="text-4xl mb-4">🔒</div>
                        <h3 class="text-lg font-semibold mb-2 text-slate-900 dark:text-white">Secure Access</h3>
                        <p class="text-slate-600 dark:text-slate-300 text-sm">Authentication and authorization</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-12 px-6 bg-slate-900 text-white">
        <div class="container mx-auto">
            <div class="max-w-6xl mx-auto text-center">
                <div class="flex items-center justify-center space-x-2 mb-4">
                    <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="text-xl font-bold">Smart Home IoT</span>
                </div>
                <p class="text-slate-400 mb-4">Building the future of connected homes</p>
                <p class="text-sm text-slate-500">&copy; {{ date('Y') }} Smart Home IoT Dashboard. All rights
                    reserved.</p>
            </div>
        </div>
    </footer>

</body>

</html>
