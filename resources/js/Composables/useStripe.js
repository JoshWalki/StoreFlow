import { ref, computed } from 'vue';

/**
 * Stripe.js Composable
 *
 * Provides methods for initializing Stripe and creating payment elements.
 * Requires Stripe.js to be loaded in the page (via script tag in app.blade.php).
 */

let stripeInstance = null;
let elementsInstance = null;

export function useStripe() {
    const isLoading = ref(false);
    const error = ref(null);
    const paymentElement = ref(null);

    /**
     * Initialize Stripe with publishable key
     *
     * @param {string} publishableKey - Stripe publishable key
     * @param {string|null} stripeAccount - Stripe Connect account ID (for Direct Charges)
     * @returns {object} Stripe instance
     */
    const initializeStripe = (publishableKey, stripeAccount = null) => {
        if (!window.Stripe) {
            error.value = 'Stripe.js is not loaded. Please check your internet connection.';
            console.error('Stripe.js not loaded');
            return null;
        }

        if (!stripeInstance) {
            // For Direct Charges with Stripe Connect, pass the connected account ID
            const options = stripeAccount ? { stripeAccount } : {};
            stripeInstance = window.Stripe(publishableKey, options);
            console.log('Stripe initialized with options:', options);
        }

        return stripeInstance;
    };

    /**
     * Create Stripe Elements instance
     *
     * @param {string} clientSecret - PaymentIntent client secret
     * @param {object} options - Elements options (appearance, etc.)
     * @returns {object} Elements instance
     */
    const createElements = (clientSecret, options = {}) => {
        if (!stripeInstance) {
            error.value = 'Stripe not initialized. Call initializeStripe first.';
            return null;
        }

        const defaultOptions = {
            clientSecret,
            appearance: {
                theme: 'stripe',
                variables: {
                    colorPrimary: '#3b82f6',
                    colorBackground: '#ffffff',
                    colorText: '#1f2937',
                    colorDanger: '#ef4444',
                    fontFamily: 'system-ui, sans-serif',
                    spacingUnit: '4px',
                    borderRadius: '8px',
                },
            },
        };

        elementsInstance = stripeInstance.elements({
            ...defaultOptions,
            ...options,
        });

        return elementsInstance;
    };

    /**
     * Mount payment element to DOM
     *
     * @param {HTMLElement} element - DOM element to mount to
     * @param {object} options - Payment element options
     */
    const mountPaymentElement = (element, options = {}) => {
        if (!elementsInstance) {
            error.value = 'Elements not created. Call createElements first.';
            console.error('Elements not created');
            return;
        }

        try {
            const defaultOptions = {
                layout: 'tabs',
            };

            console.log('Creating payment element...');
            paymentElement.value = elementsInstance.create('payment', {
                ...defaultOptions,
                ...options,
            });

            console.log('Mounting to element:', element);
            paymentElement.value.mount(element);

            // Listen for errors
            paymentElement.value.on('change', (event) => {
                if (event.error) {
                    console.error('Stripe element error:', event.error);
                    error.value = event.error.message;
                } else {
                    error.value = null;
                }
            });

            console.log('Payment element mounted successfully');
        } catch (err) {
            console.error('Error mounting payment element:', err);
            error.value = err.message;
        }
    };

    /**
     * Confirm payment with Stripe
     *
     * @param {string} returnUrl - URL to redirect after payment
     * @returns {Promise<object>} Payment result
     */
    const confirmPayment = async (returnUrl) => {
        if (!stripeInstance || !elementsInstance) {
            error.value = 'Stripe not properly initialized';
            return { error: { message: error.value } };
        }

        isLoading.value = true;
        error.value = null;

        try {
            const result = await stripeInstance.confirmPayment({
                elements: elementsInstance,
                confirmParams: {
                    return_url: returnUrl,
                },
                redirect: 'if_required', // Don't redirect if payment succeeds
            });

            if (result.error) {
                error.value = result.error.message;
                return { error: result.error };
            }

            return { paymentIntent: result.paymentIntent };
        } catch (err) {
            error.value = err.message || 'An unexpected error occurred';
            return { error: { message: error.value } };
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * Destroy payment element (cleanup)
     */
    const destroyPaymentElement = () => {
        if (paymentElement.value) {
            paymentElement.value.unmount();
            paymentElement.value = null;
        }
    };

    /**
     * Reset error state
     */
    const clearError = () => {
        error.value = null;
    };

    return {
        // State
        isLoading: computed(() => isLoading.value),
        error: computed(() => error.value),

        // Methods
        initializeStripe,
        createElements,
        mountPaymentElement,
        confirmPayment,
        destroyPaymentElement,
        clearError,
    };
}
