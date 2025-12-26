/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Enable sending cookies with requests (required for session-based auth)
window.axios.defaults.withCredentials = true;

// Set CSRF token for all requests
let token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found in meta tags');
}

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// Real-time updates via Pusher
import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
    wsHost: import.meta.env.VITE_PUSHER_HOST ? import.meta.env.VITE_PUSHER_HOST : `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
    wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
    wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]')?.content,
        },
    },
});

// Debug connection status (can be removed after fixing production)
if (import.meta.env.DEV || window.location.hostname === 'storeflow.com.au') {
    window.Echo.connector.pusher.connection.bind('error', function(err) {
        console.error('Pusher connection error:', err);
    });

    window.Echo.connector.pusher.connection.bind('connected', function() {
        console.log('✅ Pusher connected successfully');
    });

    window.Echo.connector.pusher.connection.bind('disconnected', function() {
        console.warn('⚠️ Pusher disconnected');
    });

    window.Echo.connector.pusher.connection.bind('failed', function() {
        console.error('❌ Pusher connection failed');
    });
}
