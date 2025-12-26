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
