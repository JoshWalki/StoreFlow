import { ref } from 'vue';
import axios from 'axios';

export function usePushNotifications() {
    const isSupported = ref('serviceWorker' in navigator && 'PushManager' in window);
    const isSubscribed = ref(false);
    const subscription = ref(null);

    /**
     * Convert VAPID public key to Uint8Array
     */
    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/\-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    /**
     * Request notification permission from user
     */
    async function requestPermission() {
        if (!isSupported.value) {
            throw new Error('Push notifications not supported');
        }

        const permission = await Notification.requestPermission();
        return permission === 'granted';
    }

    /**
     * Subscribe to push notifications
     */
    async function subscribe() {
        if (!isSupported.value) {
            throw new Error('Push notifications not supported');
        }

        try {
            // Request permission first
            const granted = await requestPermission();
            if (!granted) {
                throw new Error('Notification permission denied');
            }

            // Get service worker registration
            const registration = await navigator.serviceWorker.ready;
            console.log('✅ Service worker ready:', registration);

            // Check for existing subscription and unsubscribe first
            const existingSubscription = await registration.pushManager.getSubscription();
            if (existingSubscription) {
                console.log('⚠️ Found existing subscription, unsubscribing first...');
                await existingSubscription.unsubscribe();
                console.log('✅ Existing subscription removed');
            }

            // Get VAPID public key from backend
            const { data } = await axios.get('/api/push/vapid-key');
            const vapidPublicKey = data.public_key;

            if (!vapidPublicKey) {
                throw new Error('VAPID public key not configured on server');
            }

            console.log('✅ VAPID public key received:', vapidPublicKey.substring(0, 20) + '...');

            // Convert VAPID key
            const applicationServerKey = urlBase64ToUint8Array(vapidPublicKey);
            console.log('✅ Application server key converted, length:', applicationServerKey.length);

            // Add delay to ensure service worker is fully ready
            await new Promise(resolve => setTimeout(resolve, 500));

            // Subscribe to push notifications with retry logic
            console.log('Subscribing to push manager...');
            let pushSubscription;
            try {
                pushSubscription = await registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: applicationServerKey
                });
            } catch (subscribeError) {
                console.error('First subscription attempt failed:', subscribeError);

                // Wait and retry once
                console.log('Retrying subscription in 1 second...');
                await new Promise(resolve => setTimeout(resolve, 1000));

                pushSubscription = await registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: applicationServerKey
                });
            }

            console.log('✅ Push subscription successful:', pushSubscription.endpoint.substring(0, 50) + '...');

            // Refresh CSRF token before making request
            console.log('Refreshing CSRF token...');
            await axios.get('/sanctum/csrf-cookie');

            // Update CSRF token in axios headers
            const csrfToken = document.head.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.content;
            }

            // Send subscription to backend
            console.log('Sending subscription to backend...');
            await axios.post('/api/push/subscribe', {
                subscription: pushSubscription.toJSON()
            });
            console.log('✅ Subscription saved to backend');

            subscription.value = pushSubscription;
            isSubscribed.value = true;

            // Save subscription state to localStorage
            localStorage.setItem('pushNotificationsEnabled', 'true');

            return pushSubscription;
        } catch (error) {
            console.error('Failed to subscribe to push notifications:', error);
            throw error;
        }
    }

    /**
     * Unsubscribe from push notifications
     */
    async function unsubscribe() {
        if (!subscription.value) {
            return;
        }

        try {
            await subscription.value.unsubscribe();

            // Remove from backend
            await axios.post('/api/push/unsubscribe', {
                endpoint: subscription.value.endpoint
            });

            subscription.value = null;
            isSubscribed.value = false;

            // Remove from localStorage
            localStorage.removeItem('pushNotificationsEnabled');
        } catch (error) {
            console.error('Failed to unsubscribe from push notifications:', error);
            throw error;
        }
    }

    /**
     * Check current subscription status
     */
    async function checkSubscription() {
        if (!isSupported.value) {
            return false;
        }

        try {
            const registration = await navigator.serviceWorker.ready;
            const pushSubscription = await registration.pushManager.getSubscription();

            if (pushSubscription) {
                subscription.value = pushSubscription;
                isSubscribed.value = true;
                return true;
            }

            return false;
        } catch (error) {
            console.error('Failed to check subscription:', error);
            return false;
        }
    }

    return {
        isSupported,
        isSubscribed,
        subscription,
        requestPermission,
        subscribe,
        unsubscribe,
        checkSubscription,
    };
}
