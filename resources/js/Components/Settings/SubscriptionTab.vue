<template>
    <div class="max-w-6xl mx-auto">
        <!-- Subscription Status Card -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Subscription Status</h2>
                    <div v-if="subscription && subscription.stripe_subscription_id" class="flex items-center space-x-3">
                        <span class="text-sm text-gray-600 dark:text-gray-400 capitalize">
                            {{ formatPlanName(subscription.plan_id) }}
                        </span>
                        <span
                            class="px-3 py-1 text-sm font-semibold rounded-full"
                            :class="subscription.plan_id === 'basic' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300' : getStatusBadgeClass(subscription.status)"
                        >
                            {{ getStatusLabel(subscription.status) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="px-6 py-6">
                <div v-if="subscription && subscription.stripe_subscription_id" class="space-y-4">

                    <!-- Status Message -->
                    <div
                        v-if="metrics && metrics.status_message"
                        class="p-4 rounded-lg"
                        :class="getMessageClass(subscription.status)"
                    >
                        <p class="text-sm font-medium">{{ metrics.status_message }}</p>
                    </div>

                    <!-- Trial Ended - Payment Required -->
                    <div v-if="(subscription.status === 'trialing' || subscription.status === 'incomplete' || subscription.status === 'incomplete_expired') && metrics && metrics.trial_days_remaining === 0" class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-orange-600 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <div class="flex-1">
                                <h4 class="font-semibold text-orange-900">Trial Ended - Payment Required</h4>
                                <p class="text-sm text-orange-700 mt-1">
                                    Your free trial has ended. Add a payment method to continue using StoreFlow without interruption.
                                </p>
                                <button
                                    @click="continueSubscription"
                                    :disabled="processing"
                                    class="mt-3 px-4 py-2 bg-orange-600 text-white text-sm font-medium rounded hover:bg-orange-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <svg v-if="processing" class="inline w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ processing ? 'Processing...' : 'Continue Subscription' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Trial Info - Active with Days Remaining -->
                    <div v-if="subscription.status === 'trialing' && metrics && metrics.trial_days_remaining > 0" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-blue-600 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            <div>
                                <h4 class="font-semibold text-blue-900">Free Trial Active</h4>
                                <p class="text-sm text-blue-700 mt-1">
                                    You have {{ metrics.trial_days_remaining }} days remaining in your free trial.
                                    Add a payment method before your trial ends to continue using StoreFlow.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Overdue Warning -->
                    <div v-if="subscription.status === 'past_due' && metrics" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-yellow-600 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <div class="flex-1">
                                <h4 class="font-semibold text-yellow-900">Payment Failed</h4>
                                <p class="text-sm text-yellow-700 mt-1">
                                    {{ metrics.grace_period_days_remaining }} days remaining to update your payment method.
                                    After that, your stores will be deactivated.
                                </p>
                                <button
                                    @click="updatePaymentMethod"
                                    class="mt-3 px-4 py-2 bg-yellow-600 text-white text-sm font-medium rounded hover:bg-yellow-700"
                                >
                                    Update Payment Method
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Subscription Expired -->
                    <div v-if="metrics && metrics.is_expired" class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-red-600 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                            <div class="flex-1">
                                <h4 class="font-semibold text-red-900">Subscription Expired</h4>
                                <p class="text-sm text-red-700 mt-1">
                                    Your subscription has expired and your stores have been deactivated.
                                    Reactivate your subscription to continue accepting orders.
                                </p>
                                <button
                                    @click="reactivateSubscription"
                                    class="mt-3 px-4 py-2 bg-red-600 text-white text-sm font-medium rounded hover:bg-red-700"
                                >
                                    Reactivate Subscription
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Subscription Details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-900">
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ subscription.status === 'trialing' ? 'Trial Ends / First Billing' : 'Current Period' }}
                            </h4>
                            <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                                {{ formatDate(subscription.status === 'trialing' && subscription.trial_end ? subscription.trial_end : subscription.current_period_end) }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ subscription.status === 'trialing' ? 'Trial ends and billing begins' : 'Next billing date' }}
                            </p>
                        </div>

                        <div v-if="subscription.status === 'trialing' && metrics && metrics.trial_days_remaining > 0" class="border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-blue-900 dark:text-blue-300">Days Remaining</h4>
                            <p class="mt-1 text-lg font-semibold text-blue-900 dark:text-blue-200">
                                {{ metrics.trial_days_remaining }} days
                            </p>
                            <p class="text-xs text-blue-700 dark:text-blue-400 mt-1">Trial period remaining</p>
                        </div>

                        <div v-else class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-900">
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Saved</h4>
                            <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                                $0.00
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Lifetime savings</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex space-x-3 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <button
                            v-if="subscription.is_active && !subscription.cancel_at_period_end"
                            @click="showCancelModal = true"
                            class="px-4 py-2 text-sm font-medium text-red-600 border border-red-300 rounded hover:bg-red-50 transition-colors"
                        >
                            Cancel Subscription
                        </button>

                        <button
                            v-if="subscription.cancel_at_period_end"
                            @click="resumeSubscription"
                            class="px-4 py-2 text-sm font-medium text-blue-600 border border-blue-300 rounded hover:bg-blue-50 transition-colors"
                        >
                            Resume Subscription
                        </button>

                        <button
                            @click="viewInvoices"
                            class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded hover:bg-gray-50 transition-colors"
                        >
                            View Invoices
                        </button>
                    </div>
                </div>

                <!-- No Subscription -->
                <div v-else class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No Active Subscription</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started with a 14-day free trial</p>
                    <div class="mt-6">
                        <button
                            @click="startSubscription"
                            class="px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 shadow-sm"
                        >
                            Start Free Trial
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Method Card -->
        <div v-if="subscription && subscription.stripe_subscription_id" class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Payment Method</h2>
            </div>

            <div class="px-6 py-6">
                <p class="text-sm text-gray-600 mb-4">
                    Manage your payment method for automatic subscription billing.
                </p>
                <button
                    @click="updatePaymentMethod"
                    class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded hover:bg-gray-200"
                >
                    Update Payment Method
                </button>
            </div>
        </div>

        <!-- Cancel Subscription Modal -->
        <div
            v-if="showCancelModal"
            class="fixed inset-0 z-50 overflow-y-auto"
            @click.self="showCancelModal = false"
        >
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showCancelModal = false"></div>

                <!-- Modal panel -->
                <div class="relative inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <!-- Close button -->
                    <div class="absolute top-0 right-0 pt-4 pr-4">
                        <button
                            @click="showCancelModal = false"
                            class="text-gray-400 hover:text-gray-500 focus:outline-none"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Warning icon -->
                    <div class="sm:flex sm:items-start">
                        <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-red-100 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg font-medium leading-6 text-gray-900">
                                Cancel Subscription
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Are you sure you want to cancel your subscription? Your access will continue until
                                    <span class="font-semibold">{{ formatDate(subscription.current_period_end) }}</span>.
                                </p>
                            </div>

                            <!-- Cancellation reason -->
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Help us improve - Why are you canceling? (Optional)
                                </label>
                                <select
                                    v-model="cancelReason"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                >
                                    <option value="">Select a reason...</option>
                                    <option value="too_expensive">Too expensive</option>
                                    <option value="missing_features">Missing features I need</option>
                                    <option value="not_using">Not using it enough</option>
                                    <option value="switching_competitor">Switching to a competitor</option>
                                    <option value="business_closing">Business is closing</option>
                                    <option value="technical_issues">Technical issues</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <!-- Additional feedback -->
                            <div v-if="cancelReason" class="mt-3">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Additional feedback (Optional)
                                </label>
                                <textarea
                                    v-model="cancelFeedback"
                                    rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Tell us more about your experience..."
                                ></textarea>
                            </div>

                            <!-- What happens next -->
                            <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
                                <h4 class="text-sm font-medium text-blue-900">What happens next:</h4>
                                <ul class="mt-2 text-sm text-blue-700 space-y-1">
                                    <li>✓ You'll keep access until {{ formatDate(subscription.current_period_end) }}</li>
                                    <li>✓ No charges after current period ends</li>
                                    <li>✓ You can resume anytime before then</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        <button
                            @click="confirmCancel"
                            :disabled="canceling"
                            class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <svg v-if="canceling" class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ canceling ? 'Canceling...' : 'Yes, Cancel Subscription' }}
                        </button>
                        <button
                            @click="showCancelModal = false"
                            :disabled="canceling"
                            class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Keep Subscription
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const subscription = ref(null);
const metrics = ref(null);
const loading = ref(true);
const showCancelModal = ref(false);
const cancelReason = ref('');
const cancelFeedback = ref('');
const canceling = ref(false);
const processing = ref(false);

const props = defineProps({
    currentSubscription: Object,
    currentMetrics: Object,
});

onMounted(async () => {
    if (props.currentSubscription) {
        subscription.value = props.currentSubscription;
        metrics.value = props.currentMetrics;
        loading.value = false;
    } else {
        await loadSubscriptionStatus();
    }
});

const loadSubscriptionStatus = async () => {
    try {
        const response = await axios.get('/subscriptions/status');
        subscription.value = response.data.subscription;
        metrics.value = response.data.metrics;

        // Debug logging
        console.log('=== SUBSCRIPTION DATA LOADED ===');
        console.log('subscription:', subscription.value);
        console.log('stripe_subscription_id:', subscription.value?.stripe_subscription_id);
        console.log('status:', subscription.value?.status);
        console.log('Should show subscription section:', !!(subscription.value && subscription.value.stripe_subscription_id));
        console.log('================================');
    } catch (error) {
        console.error('Failed to load subscription status:', error);
    } finally {
        loading.value = false;
    }
};

const getStatusBadgeClass = (status) => {
    const classes = {
        'active': 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
        'trialing': 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300',
        'past_due': 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300',
        'canceled': 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300',
        'unpaid': 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300',
        'paused': 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300',
    };
    return classes[status] || 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300';
};

const getStatusLabel = (status) => {
    const labels = {
        'active': 'Active',
        'trialing': 'Free Trial',
        'past_due': 'Payment Overdue',
        'canceled': 'Canceled',
        'unpaid': 'Unpaid',
        'paused': 'Paused',
    };
    return labels[status] || 'Unknown';
};

const getMessageClass = (status) => {
    if (status === 'active') return 'bg-green-50 border border-green-200 text-green-800';
    if (status === 'trialing') return 'bg-blue-50 border border-blue-200 text-blue-800';
    if (status === 'past_due') return 'bg-yellow-50 border border-yellow-200 text-yellow-800';
    return 'bg-gray-50 border border-gray-200 text-gray-800';
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
};

const formatPlanName = (planId) => {
    if (!planId) return 'Basic Plan';
    return planId.charAt(0).toUpperCase() + planId.slice(1);
};

const confirmCancel = async () => {
    canceling.value = true;

    try {
        // Send cancellation request with feedback
        await axios.post('/subscriptions/cancel', {
            reason: cancelReason.value,
            feedback: cancelFeedback.value
        });

        // Reload subscription status
        await loadSubscriptionStatus();

        // Close modal and reset form
        showCancelModal.value = false;
        cancelReason.value = '';
        cancelFeedback.value = '';

        // Show success message
        alert('Subscription will be canceled at the end of the billing period. You can still resume before then.');
    } catch (error) {
        console.error('Failed to cancel subscription:', error);
        alert('Failed to cancel subscription. Please try again.');
    } finally {
        canceling.value = false;
    }
};

const resumeSubscription = async () => {
    try {
        await axios.post('/subscriptions/resume');
        await loadSubscriptionStatus();
        alert('Subscription resumed successfully!');
    } catch (error) {
        console.error('Failed to resume subscription:', error);
        alert('Failed to resume subscription. Please try again.');
    }
};

const startSubscription = async () => {
    try {
        const response = await axios.post('/subscriptions/checkout');
        // Redirect to Stripe Checkout
        window.location.href = response.data.checkout_url;
    } catch (error) {
        console.error('Failed to create checkout session:', error);
        alert('Failed to start subscription. Please try again.');
    }
};

const continueSubscription = async () => {
    processing.value = true;

    try {
        // Create checkout session to add payment method
        const response = await axios.post('/subscriptions/checkout');

        // Redirect to Stripe Checkout to add payment method
        window.location.href = response.data.checkout_url;
    } catch (error) {
        console.error('Failed to create checkout session:', error);
        alert('Failed to continue subscription. Please try again.');
        processing.value = false;
    }
};

const reactivateSubscription = async () => {
    try {
        const response = await axios.post('/subscriptions/checkout');
        // Redirect to Stripe Checkout
        window.location.href = response.data.checkout_url;
    } catch (error) {
        console.error('Failed to create checkout session:', error);
        alert('Failed to reactivate subscription. Please try again.');
    }
};

const updatePaymentMethod = () => {
    // Open Stripe payment method update flow
    // This would typically open a modal with Stripe Elements
    alert('Payment method update coming soon!');
};

const viewInvoices = async () => {
    try {
        const response = await axios.get('/subscriptions/invoices');
        if (response.data.invoices.length > 0) {
            // Open first invoice in new tab
            window.open(response.data.invoices[0].hosted_invoice_url, '_blank');
        } else {
            alert('No invoices available yet.');
        }
    } catch (error) {
        console.error('Failed to retrieve invoices:', error);
        alert('Failed to load invoices. Please try again.');
    }
};
</script>
