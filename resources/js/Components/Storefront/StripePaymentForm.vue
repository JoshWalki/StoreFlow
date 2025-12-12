<template>
    <div class="stripe-payment-form">
        <!-- Payment Form -->
        <div v-if="clientSecret" class="space-y-4">
            <!-- Payment Element Container -->
            <div
                ref="paymentElementRef"
                id="payment-element"
                class="min-h-[200px]"
            ></div>

            <!-- Error Message -->
            <div
                v-if="stripeError"
                class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg"
            >
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="flex-1">
                        <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                            Payment Error
                        </h3>
                        <p class="mt-1 text-sm text-red-700 dark:text-red-300">
                            {{ stripeError }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <button
                @click="handleSubmit"
                :disabled="isProcessing || !isReady"
                class="w-full py-3 px-6 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
            >
                <svg v-if="isProcessing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span v-if="isProcessing">Processing Payment...</span>
                <span v-else>Complete Payment</span>
            </button>

            <!-- Powered by Stripe -->
            <div class="flex items-center justify-center text-xs text-gray-500 dark:text-gray-400">
                <svg class="w-4 h-4 mr-1" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M9 0v1.5a.5.5 0 01-1 0V0H6v1.5a.5.5 0 01-1 0V0H3a2 2 0 00-2 2v11a2 2 0 002 2h10a2 2 0 002-2V2a2 2 0 00-2-2h-2v1.5a.5.5 0 01-1 0V0H9z"/>
                </svg>
                Secured by Stripe
            </div>
        </div>

        <!-- Loading State -->
        <div v-else class="flex flex-col items-center justify-center py-12">
            <svg class="animate-spin h-8 w-8 text-blue-600 mb-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-sm text-gray-600 dark:text-gray-400">Loading payment form...</p>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { useStripe } from '@/Composables/useStripe';

const props = defineProps({
    publishableKey: {
        type: String,
        required: true,
    },
    clientSecret: {
        type: String,
        default: null,
    },
    returnUrl: {
        type: String,
        default: window.location.href,
    },
    appearance: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits(['payment-success', 'payment-error']);

const paymentElementRef = ref(null);
const isProcessing = ref(false);
const isReady = ref(false);

const {
    initializeStripe,
    createElements,
    mountPaymentElement,
    confirmPayment,
    destroyPaymentElement,
    error: stripeError,
    clearError,
} = useStripe();

// Initialize Stripe when component mounts
onMounted(() => {
    console.log('StripePaymentForm mounted');
    console.log('Publishable key:', props.publishableKey);

    if (!props.publishableKey) {
        console.error('No Stripe publishable key provided');
        return;
    }

    if (props.publishableKey) {
        console.log('Initializing Stripe...');
        initializeStripe(props.publishableKey);
    }
});

// Watch for client secret changes and mount payment element
watch(() => props.clientSecret, async (newClientSecret) => {
    if (newClientSecret) {
        console.log('Client secret received:', newClientSecret);

        // Wait for DOM to be ready
        await nextTick();

        if (!paymentElementRef.value) {
            console.error('Payment element ref not found');
            return;
        }

        // Clear any previous errors
        clearError();

        console.log('Creating Stripe elements...');
        // Create elements with client secret
        createElements(newClientSecret, {
            appearance: props.appearance,
        });

        console.log('Mounting payment element...');
        // Mount payment element to DOM
        mountPaymentElement(paymentElementRef.value);

        // Mark as ready after a short delay (allows Stripe to render)
        setTimeout(() => {
            console.log('Payment form ready');
            isReady.value = true;
        }, 500);
    }
}, { immediate: true });

// Handle payment submission
const handleSubmit = async () => {
    if (isProcessing.value || !isReady.value) return;

    isProcessing.value = true;
    clearError();

    try {
        const result = await confirmPayment(props.returnUrl);

        if (result.error) {
            emit('payment-error', result.error);
        } else {
            emit('payment-success', result.paymentIntent);
        }
    } catch (err) {
        console.error('Payment confirmation error:', err);
        emit('payment-error', { message: err.message || 'Payment failed' });
    } finally {
        isProcessing.value = false;
    }
};

// Cleanup on unmount
onUnmounted(() => {
    destroyPaymentElement();
});
</script>

<style scoped>
/* Additional styling for Stripe Elements */
#payment-element {
    /* Stripe will inject its form here */
}
</style>
