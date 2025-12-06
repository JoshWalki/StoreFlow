<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- PWA Meta Tags -->
        <meta name="theme-color" content="#3b82f6">
        <link rel="manifest" href="/manifest.json">
        <link rel="apple-touch-icon" href="/logo.png">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="StoreFlow">

        <title inertia>{{ config('app.name', 'StoreFlow') }}</title>

        <!-- Scripts -->
        @routes
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia

        <!-- PWA Service Worker Registration -->
        <script>
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/sw.js')
                    .then((registration) => {
                        console.log('Service Worker registered with scope:', registration.scope);

                        // Check for updates every time the page loads
                        registration.update();

                        // When a new service worker is waiting, activate it immediately
                        registration.addEventListener('updatefound', () => {
                            const newWorker = registration.installing;
                            newWorker.addEventListener('statechange', () => {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    // New service worker available, reload to activate
                                    console.log('New service worker available, reloading...');
                                    window.location.reload();
                                }
                            });
                        });
                    })
                    .catch((error) => {
                        console.error('Service Worker registration failed:', error);
                    });

                // Listen for controller change and reload
                navigator.serviceWorker.addEventListener('controllerchange', () => {
                    console.log('Service worker controller changed, reloading...');
                    window.location.reload();
                });
            }
        </script>
    </body>
</html>
