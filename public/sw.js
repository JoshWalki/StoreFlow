const CACHE_NAME = "storeflow-v3"; // Incremented version to force cache refresh
const OFFLINE_URL = "/offline.html";

const filesToCache = [OFFLINE_URL];

// Listen for SKIP_WAITING message
self.addEventListener("message", (event) => {
    if (event.data && event.data.type === "SKIP_WAITING") {
        self.skipWaiting();
    }
});

// Install event - cache offline page
self.addEventListener("install", function (event) {
    event.waitUntil(
        caches.open(CACHE_NAME).then(function (cache) {
            return cache.addAll(filesToCache);
        })
    );
    // Force the waiting service worker to become the active service worker
    self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener("activate", function (event) {
    event.waitUntil(
        caches.keys().then(function (cacheNames) {
            return Promise.all(
                cacheNames.map(function (cacheName) {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    return self.clients.claim();
});

// Fetch event - network first, then cache, only show offline for HTML pages
self.addEventListener("fetch", function (event) {
    // Skip cross-origin requests
    if (!event.request.url.startsWith(self.location.origin)) {
        return;
    }

    // For navigation requests (HTML pages)
    if (event.request.mode === "navigate") {
        event.respondWith(
            fetch(event.request).catch(function () {
                return caches.match(OFFLINE_URL);
            })
        );
        return;
    }

    // For all other requests (images, CSS, JS, etc.) - just try to fetch
    // Don't return offline page for failed asset requests
    event.respondWith(
        fetch(event.request).catch(function () {
            // Try to get from cache if network fails
            return caches.match(event.request);
        })
    );
});

// Push notification event - show notification when order is received
self.addEventListener('push', function(event) {
    if (!event.data) {
        return;
    }

    const data = event.data.json();
    const title = data.title || 'StoreFlow';
    const options = {
        body: data.body,
        icon: '/images/logo/logo.png', // Large icon shown on right (Android)
        badge: '/images/logo/logo-white.png', // Small monochrome icon on left (Android)
        tag: data.tag || 'storeflow-notification',
        data: data.data || {},
        requireInteraction: true, // Keeps notification visible until user interacts
        vibrate: [200, 100, 200], // Vibration pattern for mobile devices
        silent: false, // Allow device to play notification sound
        // Note: Custom notification sounds are not supported by Web Notifications API
        // The device will use its default notification sound
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// Notification click event - open dashboard when notification is clicked
self.addEventListener('notificationclick', function(event) {
    event.notification.close();

    const urlToOpen = event.notification.data.url || '/dashboard';

    event.waitUntil(
        clients.matchAll({
            type: 'window',
            includeUncontrolled: true
        }).then(function(clientList) {
            // If StoreFlow is already open, focus that window
            for (let i = 0; i < clientList.length; i++) {
                const client = clientList[i];
                if (client.url.includes('/dashboard') && 'focus' in client) {
                    return client.focus();
                }
            }
            // Otherwise, open a new window
            if (clients.openWindow) {
                return clients.openWindow(urlToOpen);
            }
        })
    );
});
