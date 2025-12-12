<template>
    <DashboardLayout :store="store" :user="user">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Subscription Plans</h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Choose the plan that best fits your business needs
                </p>
            </div>

            <!-- Current Subscription Status -->
            <div v-if="currentSubscription" class="mb-8 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Current Subscription</h2>

                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="text-lg font-medium text-gray-900 dark:text-white">
                                Status:
                            </span>
                            <span
                                class="px-3 py-1 rounded-full text-sm font-medium"
                                :class="{
                                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': currentSubscription.status === 'active',
                                    'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': currentSubscription.status === 'trialing',
                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': currentSubscription.status === 'past_due',
                                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': ['canceled', 'unpaid'].includes(currentSubscription.status)
                                }"
                            >
                                {{ currentSubscription.status }}
                            </span>
                        </div>

                        <p v-if="currentSubscription.current_period_end" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Current period ends: {{ formatDate(currentSubscription.current_period_end) }}
                        </p>

                        <p v-if="currentSubscription.trial_end" class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Trial ends: {{ formatDate(currentSubscription.trial_end) }}
                        </p>
                    </div>

                    <div v-if="currentSubscription.can_accept_payments" class="flex items-center gap-2 text-green-600 dark:text-green-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span class="text-sm font-medium">Can accept payments</span>
                    </div>
                </div>
            </div>

            <!-- Subscription Plans Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="plan in plans"
                    :key="plan.id"
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow"
                    :class="{
                        'ring-2 ring-blue-500': currentSubscription?.plan_id === plan.id
                    }"
                >
                    <!-- Plan Header -->
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 text-white">
                        <h3 class="text-2xl font-bold">{{ plan.name }}</h3>
                        <div class="mt-4 flex items-baseline">
                            <span class="text-4xl font-bold">{{ plan.formatted_price }}</span>
                            <span class="ml-2 text-blue-100">/{{ plan.billing_interval }}</span>
                        </div>
                        <p v-if="plan.trial_days" class="mt-2 text-sm text-blue-100">
                            {{ plan.trial_days }} days free trial
                        </p>
                    </div>

                    <!-- Plan Details -->
                    <div class="p-6">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                            {{ plan.description }}
                        </p>

                        <!-- Features List -->
                        <ul class="space-y-3 mb-6">
                            <li
                                v-for="(feature, index) in plan.features"
                                :key="index"
                                class="flex items-start gap-2"
                            >
                                <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ feature }}</span>
                            </li>
                        </ul>

                        <!-- Limits -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mb-6">
                            <div class="text-sm text-gray-600 dark:text-gray-400 space-y-2">
                                <div class="flex justify-between">
                                    <span>Products:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        {{ plan.max_products ?? 'Unlimited' }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Orders/month:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        {{ plan.max_orders_per_month ?? 'Unlimited' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <button
                            v-if="currentSubscription?.plan_id === plan.id"
                            disabled
                            class="w-full py-3 px-4 bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 rounded-lg font-medium cursor-not-allowed"
                        >
                            Current Plan
                        </button>
                        <button
                            v-else
                            @click="subscribeToPlan(plan.id)"
                            class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors"
                        >
                            {{ currentSubscription ? 'Switch Plan' : 'Subscribe' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Cancel/Resume Actions -->
            <div v-if="currentSubscription && currentSubscription.status !== 'canceled'" class="mt-8 text-center">
                <button
                    @click="cancelSubscription"
                    class="text-sm text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 underline"
                >
                    Cancel Subscription
                </button>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    plans: Array,
    currentSubscription: Object,
    store: Object,
    user: Object,
});

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const subscribeToPlan = (planId) => {
    if (confirm('Are you sure you want to subscribe to this plan?')) {
        router.post('/subscriptions', { plan_id: planId }, {
            preserveScroll: true,
            onSuccess: () => {
                alert('Subscription updated successfully!');
            },
            onError: (errors) => {
                alert('Failed to update subscription: ' + Object.values(errors).join(', '));
            }
        });
    }
};

const cancelSubscription = () => {
    if (confirm('Are you sure you want to cancel your subscription? You will retain access until the end of your billing period.')) {
        router.post('/subscriptions/cancel', {}, {
            preserveScroll: true,
            onSuccess: () => {
                alert('Subscription canceled successfully. You will retain access until the end of your billing period.');
            },
            onError: (errors) => {
                alert('Failed to cancel subscription: ' + Object.values(errors).join(', '));
            }
        });
    }
};
</script>
