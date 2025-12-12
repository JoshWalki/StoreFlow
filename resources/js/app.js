import "./bootstrap";
import "./echo"; // Import Laravel Echo for WebSocket support
import { createApp, h, nextTick } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";
import Echo from "laravel-echo";
import Pusher from "pusher-js";
import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";
import flashMessages from "./Plugins/flashMessages";

const appName =
    window.document.getElementsByTagName("title")[0]?.innerText || "StoreFlow";

// Toast notification options
const toastOptions = {
    position: "top-right",
    timeout: 5000,
    closeOnClick: true,
    pauseOnFocusLoss: true,
    pauseOnHover: true,
    draggable: true,
    draggablePercent: 0.6,
    showCloseButtonOnHover: false,
    hideProgressBar: false,
    closeButton: "button",
    icon: true,
    rtl: false,
    transition: "Vue-Toastification__bounce",
    maxToasts: 5,
    newestOnTop: true,
};

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: async (name) => {
        const page = await resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue")
        );

        // Set persistent layout for dashboard pages (except DisplayView which needs full-screen)
        if ((name.startsWith('Dashboard/') ||
            name.startsWith('Orders/') ||
            name.startsWith('Products/') ||
            name.startsWith('Categories/') ||
            name.startsWith('Customers/') ||
            name.startsWith('Store/') ||
            name.startsWith('Staff/') ||
            name.startsWith('Shipping/') ||
            name.startsWith('Loyalty/') ||
            name.startsWith('AuditLogs/')) &&
            name !== 'Dashboard/DisplayView') {

            // Import layout dynamically
            const DashboardLayout = (await import('./Layouts/DashboardLayout.vue')).default;
            page.default.layout = page.default.layout || DashboardLayout;
        }

        return page;
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(Toast, toastOptions)
            .use(flashMessages);

        // Mount the app
        const mountedApp = app.mount(el);

        // Wait for Vue to fully render all components
        nextTick(() => {
            // Use multiple animation frames to ensure browser has painted
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    // Additional delay to ensure all async components and images start loading
                    setTimeout(() => {
                        window.dispatchEvent(new Event('app:rendered'));
                    }, 200);
                });
            });
        });

        return mountedApp;
    },
    progress: {
        delay: 100, // Show loading indicator quickly
        color: '#3b82f6',
        includeCSS: true,
        showSpinner: false, // Disable default small spinner - we'll use custom one
    },
});

// Custom centered loading spinner with minimum display time
let loadingOverlay = null;
let loadingStartTime = null;
const MIN_LOADING_TIME = 500; // Minimum time to show spinner (in milliseconds)

const showLoadingSpinner = () => {
    loadingStartTime = Date.now();

    if (!loadingOverlay) {
        loadingOverlay = document.createElement('div');
        loadingOverlay.className = 'fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50 transition-opacity duration-200';
        loadingOverlay.innerHTML = `
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-2xl">
                <div class="flex flex-col items-center space-y-3">
                    <svg class="animate-spin h-16 w-16 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-gray-700 dark:text-gray-300 font-medium">Loading...</p>
                </div>
            </div>
        `;
        document.body.appendChild(loadingOverlay);
    }
    loadingOverlay.style.opacity = '1';
    loadingOverlay.style.display = 'flex';
};

const hideLoadingSpinner = () => {
    if (loadingOverlay && loadingStartTime) {
        const elapsedTime = Date.now() - loadingStartTime;
        const remainingTime = Math.max(0, MIN_LOADING_TIME - elapsedTime);

        // Wait for minimum display time before hiding
        setTimeout(() => {
            if (loadingOverlay) {
                loadingOverlay.style.opacity = '0';
                setTimeout(() => {
                    if (loadingOverlay) {
                        loadingOverlay.style.display = 'none';
                    }
                }, 200);
            }
            loadingStartTime = null;
        }, remainingTime);
    }
};

// Listen to Inertia navigation events (but not for display view)
document.addEventListener('inertia:start', (event) => {
    // Don't show loading spinner on display view
    if (!window.location.pathname.includes('/dashboard/display')) {
        showLoadingSpinner();
    }
});
document.addEventListener('inertia:finish', hideLoadingSpinner);

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: "pusher",
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
});
