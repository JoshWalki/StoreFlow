import { useToast as useToastification } from 'vue-toastification';

export function useToast() {
    const toast = useToastification();

    return {
        success: (message, options = {}) => {
            toast.success(message, {
                timeout: 4000,
                ...options,
            });
        },
        error: (message, options = {}) => {
            toast.error(message, {
                timeout: 6000,
                ...options,
            });
        },
        warning: (message, options = {}) => {
            toast.warning(message, {
                timeout: 5000,
                ...options,
            });
        },
        info: (message, options = {}) => {
            toast.info(message, {
                timeout: 4000,
                ...options,
            });
        },
        // Helper to show Laravel validation errors
        validationErrors: (errors) => {
            const errorMessages = Object.values(errors).flat();
            errorMessages.forEach(error => {
                toast.error(error, {
                    timeout: 7000,
                });
            });
        },
    };
}
