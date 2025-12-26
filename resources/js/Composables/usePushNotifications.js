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

            // Get VAPID public key from backend
            const { data } = await axios.get('/api/push/vapid-key');
            const vapidPublicKey = data.public_key;

            // Subscribe to push notifications
            const pushSubscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(vapidPublicKey)
            });

            // Send subscription to backend
            await axios.post('/api/push/subscribe', {
                subscription: pushSubscription.toJSON()
            });

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
