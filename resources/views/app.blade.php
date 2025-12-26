<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Favicon -->
        <link rel="icon" type="image/x-icon" href="/images/logo/favicon.ico">

        <!-- PWA Meta Tags -->
        <meta name="theme-color" content="#3b82f6">
        <link rel="manifest" href="/manifest.json">
        <link rel="apple-touch-icon" href="/images/logo/logo.png">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="StoreFlow">

        <title inertia>{{ config('app.name', 'StoreFlow') }}</title>

        <!-- Stripe.js -->
        <script src="https://js.stripe.com/v3/"></script>

        <!-- Google Fonts - Baloo 2 -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400..800&display=swap" rel="stylesheet">

        <!-- Loading Screen Styles -->
        <style>
            #app-loader {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 99999;
                transition: opacity 0.5s ease-out, visibility 0.5s ease-out;
            }

            #app-loader.loaded {
                opacity: 0;
                visibility: hidden;
            }

            .loader-content {
                text-align: center;
            }

            .loader-circle {
                position: relative;
                width: 80px;
                height: 80px;
                margin: 0 auto 20px;
            }

            .loader-circle::before,
            .loader-circle::after {
                content: '';
                position: absolute;
                border-radius: 50%;
            }

            .loader-circle::before {
                width: 80px;
                height: 80px;
                border: 4px solid rgba(255, 255, 255, 0.2);
            }

            .loader-circle::after {
                width: 80px;
                height: 80px;
                border: 4px solid transparent;
                border-top-color: #ffffff;
                border-right-color: #ffffff;
                animation: loader-spin 1s cubic-bezier(0.68, -0.55, 0.265, 1.55) infinite;
            }

            .loader-rings {
                position: absolute;
                width: 100%;
                height: 100%;
            }

            .loader-ring {
                position: absolute;
                border-radius: 50%;
                border: 2px solid rgba(255, 255, 255, 0.3);
                animation: loader-pulse 2s ease-in-out infinite;
            }

            .loader-ring:nth-child(1) {
                width: 100px;
                height: 100px;
                top: 50%;
                left: 50%;
                margin: -50px 0 0 -50px;
                animation-delay: 0s;
            }

            .loader-ring:nth-child(2) {
                width: 120px;
                height: 120px;
                top: 50%;
                left: 50%;
                margin: -60px 0 0 -60px;
                animation-delay: 0.3s;
            }

            .loader-ring:nth-child(3) {
                width: 140px;
                height: 140px;
                top: 50%;
                left: 50%;
                margin: -70px 0 0 -70px;
                animation-delay: 0.6s;
            }

            @keyframes loader-spin {
                0% {
                    transform: rotate(0deg);
                }
                100% {
                    transform: rotate(360deg);
                }
            }

            @keyframes loader-pulse {
                0%, 100% {
                    opacity: 0;
                    transform: scale(0.5);
                }
                50% {
                    opacity: 1;
                    transform: scale(1);
                }
            }

            .loader-text {
                color: white;
                font-size: 16px;
                font-weight: 500;
                letter-spacing: 2px;
                text-transform: uppercase;
                animation: loader-fade 1.5s ease-in-out infinite;
            }

            @keyframes loader-fade {
                0%, 100% {
                    opacity: 0.4;
                }
                50% {
                    opacity: 1;
                }
            }
        </style>

        <!-- Scripts -->
        @routes
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        <!-- Loading Screen -->
        <div id="app-loader">
            <div class="loader-content">
                <div class="loader-circle">
                    <div class="loader-rings">
                        <div class="loader-ring"></div>
                        <div class="loader-ring"></div>
                        <div class="loader-ring"></div>
                    </div>
                </div>
                <div class="loader-text">Loading</div>
            </div>
        </div>

        @inertia

        <!-- PWA Service Worker Registration -->
        <script>
            if ('serviceWorker' in navigator) {
                // FORCE CLEAR OLD SERVICE WORKER AND CACHES
                (async function() {
                    try {
                        // Get all registrations
                        const registrations = await navigator.serviceWorker.getRegistrations();

                        // Unregister all old service workers
                        for (let registration of registrations) {
                            console.log('Unregistering old service worker...');
                            await registration.unregister();
                        }

                        // Clear ALL caches (including the broken 'offline' cache)
                        const cacheNames = await caches.keys();
                        for (let cacheName of cacheNames) {
                            console.log('Deleting cache:', cacheName);
                            await caches.delete(cacheName);
                        }

                        console.log('All old service workers and caches cleared!');

                        // Small delay to ensure cleanup completes
                        await new Promise(resolve => setTimeout(resolve, 100));

                        // Now register the NEW fixed service worker
                        const registration = await navigator.serviceWorker.register('/sw.js?v=' + Date.now());
                        console.log(' New Service Worker registered with scope:', registration.scope);

                        // Force immediate activation
                        if (registration.waiting) {
                            registration.waiting.postMessage({ type: 'SKIP_WAITING' });
                        }

                        // Check for updates
                        registration.update();

                    } catch (error) {
                        console.error('Service Worker setup error:', error);
                    }
                })();

                // Listen for controller change
                navigator.serviceWorker.addEventListener('controllerchange', () => {
                    console.log(' Service worker activated!');
                });
            }
        </script>

        <!-- Hide Loading Screen When App Loads -->
        <script>
            (function() {
                const MIN_LOADING_TIME = 800; // Minimum time to show loader (feels better)
                const MAX_LOADING_TIME = 4000; // Maximum time before forcing hide
                const loadStartTime = Date.now();
                let hideTriggered = false;

                // Function to check if content is actually visible
                function isContentVisible() {
                    const appElement = document.querySelector('[data-page]');
                    if (!appElement) return false;

                    // Check if there's actual content rendered
                    const hasContent = appElement.offsetHeight > 0 && appElement.offsetWidth > 0;
                    return hasContent;
                }

                // Function to wait for images to load
                function waitForImages(callback) {
                    const images = document.querySelectorAll('img');
                    if (images.length === 0) {
                        callback();
                        return;
                    }

                    let loadedCount = 0;
                    const totalImages = images.length;

                    images.forEach(img => {
                        if (img.complete) {
                            loadedCount++;
                        } else {
                            img.addEventListener('load', () => {
                                loadedCount++;
                                if (loadedCount === totalImages) {
                                    callback();
                                }
                            });
                            img.addEventListener('error', () => {
                                loadedCount++;
                                if (loadedCount === totalImages) {
                                    callback();
                                }
                            });
                        }
                    });

                    if (loadedCount === totalImages) {
                        callback();
                    }
                }

                // Function to hide the loader with proper timing
                window.hideAppLoader = function(forceImmediate = false) {
                    if (hideTriggered) return;

                    const loader = document.getElementById('app-loader');
                    if (!loader) return;

                    const elapsed = Date.now() - loadStartTime;

                    function performHide() {
                        if (!isContentVisible() && !forceImmediate) {
                            // Content not ready, wait a bit more
                            setTimeout(() => window.hideAppLoader(false), 200);
                            return;
                        }

                        hideTriggered = true;
                        loader.classList.add('loaded');

                        // Remove from DOM after transition
                        setTimeout(() => {
                            if (loader.parentNode) {
                                loader.remove();
                            }
                        }, 500);
                    }

                    if (forceImmediate) {
                        performHide();
                    } else if (elapsed < MIN_LOADING_TIME) {
                        // Haven't shown loader long enough, wait
                        setTimeout(performHide, MIN_LOADING_TIME - elapsed);
                    } else {
                        performHide();
                    }
                };

                // Listen for Vue app fully rendered
                window.addEventListener('app:rendered', function() {
                    // Wait for next animation frame to ensure paint
                    requestAnimationFrame(() => {
                        requestAnimationFrame(() => {
                            // Give extra time for any async operations
                            setTimeout(() => {
                                window.hideAppLoader();
                            }, 300);
                        });
                    });
                });

                // Maximum timeout - force hide after MAX_LOADING_TIME
                setTimeout(() => {
                    window.hideAppLoader(true);
                }, MAX_LOADING_TIME);

            })();
        </script>
    </body>
</html>
