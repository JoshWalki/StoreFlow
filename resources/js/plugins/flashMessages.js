import { router } from '@inertiajs/vue3';
import { useToast } from '../composables/useToast';

export default {
    install: (app) => {
        const toast = useToast();

        // Listen to Inertia navigation events for flash messages
        router.on('finish', (event) => {
            const page = event.detail?.page;
            if (!page) return; // Guard against undefined page
            const flash = page.props?.flash;

            if (flash?.success) {
                toast.success(flash.success);
            }
            if (flash?.error) {
                toast.error(flash.error);
            }
            if (flash?.warning) {
                toast.warning(flash.warning);
            }
            if (flash?.info) {
                toast.info(flash.info);
            }
        });

        // Provide toast globally
        app.config.globalProperties.$toast = toast;
        app.provide('toast', toast);
    }
};
