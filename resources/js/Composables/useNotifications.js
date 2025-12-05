import { ref } from 'vue';

// Shared notification state
const notifications = ref([]);
let notificationId = 0;

export function useNotifications() {
    const addNotification = (notification) => {
        const id = ++notificationId;
        const newNotification = {
            id,
            type: notification.type || 'info', // success, error, warning, info
            title: notification.title,
            message: notification.message,
            duration: notification.duration || 5000, // Auto-dismiss after 5 seconds
        };

        notifications.value.push(newNotification);

        // Auto-dismiss
        if (newNotification.duration > 0) {
            setTimeout(() => {
                removeNotification(id);
            }, newNotification.duration);
        }

        return id;
    };

    const removeNotification = (id) => {
        const index = notifications.value.findIndex(n => n.id === id);
        if (index !== -1) {
            notifications.value.splice(index, 1);
        }
    };

    const success = (title, message, duration) => {
        return addNotification({ type: 'success', title, message, duration });
    };

    const error = (title, message, duration) => {
        return addNotification({ type: 'error', title, message, duration });
    };

    const warning = (title, message, duration) => {
        return addNotification({ type: 'warning', title, message, duration });
    };

    const info = (title, message, duration) => {
        return addNotification({ type: 'info', title, message, duration });
    };

    return {
        notifications,
        addNotification,
        removeNotification,
        success,
        error,
        warning,
        info,
    };
}
