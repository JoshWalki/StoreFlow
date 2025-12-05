<template>
    <div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8" :class="themeConfig.background">
        <div class="max-w-3xl mx-auto">
            <!-- Success Header -->
            <div class="rounded-lg shadow-sm p-8 mb-6 text-center" :class="themeConfig.cardBackground">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                    <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold mb-2" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">Order Confirmed!</h1>
                <p class="mb-4" :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">
                    Thank you for your order. We've received your payment and will begin processing shortly.
                </p>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 inline-block">
                    <p class="text-sm text-gray-600 mb-1">Order Number</p>
                    <p class="text-2xl font-mono font-bold text-blue-700">{{ order.public_id }}</p>
                </div>
            </div>

            <!-- Order Status Progress -->
            <div class="rounded-lg shadow-sm p-6 mb-6" :class="themeConfig.cardBackground">
                <h2 class="text-xl font-semibold mb-6" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">Order Status</h2>

                <!-- Status Progress Bar -->
                <div class="relative">
                    <!-- Progress Line -->
                    <div class="absolute top-5 left-0 right-0 h-0.5 bg-gray-200" aria-hidden="true">
                        <div
                            class="h-full bg-blue-600 transition-all duration-500"
                            :style="{ width: progressPercentage + '%' }"
                        ></div>
                    </div>

                    <!-- Status Steps -->
                    <div class="relative flex justify-between">
                        <div
                            v-for="(step, index) in statusStepsWithState"
                            :key="step.status"
                            class="flex flex-col items-center"
                            :class="index === 0 ? 'items-start' : index === statusStepsWithState.length - 1 ? 'items-end' : 'items-center'"
                        >
                            <!-- Step Circle -->
                            <div class="relative z-10 flex items-center justify-center">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-full border-2 transition-all duration-300"
                                    :class="getStepCircleClass(step)"
                                >
                                    <!-- Checkmark for completed -->
                                    <svg
                                        v-if="step.completed"
                                        class="h-5 w-5 text-white"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                    <!-- Number for pending/current -->
                                    <span
                                        v-else
                                        class="text-sm font-semibold"
                                        :class="step.current ? 'text-white' : 'text-gray-400'"
                                    >
                                        {{ index + 1 }}
                                    </span>
                                </div>
                            </div>

                            <!-- Step Label -->
                            <div class="mt-3 text-center max-w-[120px]">
                                <p
                                    class="text-sm font-medium"
                                    :class="step.current || step.completed ? 'text-gray-900' : 'text-gray-400'"
                                >
                                    {{ step.label }}
                                </p>
                                <p
                                    v-if="step.current"
                                    class="text-xs text-blue-600 mt-1 font-medium"
                                >
                                    Current Status
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Details -->
            <div class="rounded-lg shadow-sm p-6 mb-6" :class="themeConfig.cardBackground">
                <h2 class="text-xl font-semibold mb-4" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">Order Details</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div>
                        <p class="text-sm text-gray-600">Order Date</p>
                        <p class="font-medium text-gray-900">{{ order.created_at }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Order Status</p>
                        <span :class="statusBadgeClass" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                            {{ formatStatus(order.status) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Payment Status</p>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                            {{ formatPaymentStatus(order.payment_status) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Fulfillment Type</p>
                        <p class="font-medium text-gray-900">{{ formatFulfillmentType(order.fulfilment_type) }}</p>
                    </div>
                </div>

                <!-- Pickup Address (if pickup) -->
                <div v-if="order.fulfilment_type === 'pickup' && hasStoreAddress" class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-medium mb-3" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">Pickup Location</h3>
                    <div :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">
                        <p class="font-medium">{{ store.name }}</p>
                        <p>{{ store.address_primary }}</p>
                        <p>{{ store.address_city }}, {{ store.address_state }} {{ store.address_postcode }}</p>
                    </div>
                    <div class="mt-4">
                        <a
                            :href="googleMapsUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors"
                        >
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                            </svg>
                            Open in Google Maps
                        </a>
                    </div>
                </div>

                <!-- Estimated Delivery (if shipping) -->
                <div v-if="order.fulfilment_type === 'shipping' && order.estimated_delivery" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex items-start">
                        <svg class="h-5 w-5 text-blue-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <p class="font-medium text-blue-900">Estimated Delivery</p>
                            <p class="text-sm text-blue-700">{{ order.estimated_delivery }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="rounded-lg shadow-sm p-6 mb-6" :class="themeConfig.cardBackground">
                <h2 class="text-xl font-semibold mb-4" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">Customer Information</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Name</p>
                        <p class="font-medium text-gray-900">{{ order.customer_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Email</p>
                        <p class="font-medium text-gray-900">{{ order.customer_email }}</p>
                    </div>
                    <div v-if="order.customer_mobile">
                        <p class="text-sm text-gray-600">Mobile</p>
                        <p class="font-medium text-gray-900">{{ order.customer_mobile }}</p>
                    </div>
                </div>

                <!-- Shipping Address (if applicable) -->
                <div v-if="order.fulfilment_type === 'shipping' && order.shipping_address" class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-3">Shipping Address</h3>
                    <div class="text-gray-700">
                        <p>{{ order.shipping_address.address_line_1 }}</p>
                        <p v-if="order.shipping_address.address_line_2">{{ order.shipping_address.address_line_2 }}</p>
                        <p>
                            {{ order.shipping_address.city }}
                            {{ order.shipping_address.state }} {{ order.shipping_address.postcode }}
                        </p>
                        <p>{{ order.shipping_address.country }}</p>
                    </div>
                    <div v-if="order.shipping_method" class="mt-4">
                        <p class="text-sm text-gray-600">Shipping Method</p>
                        <p class="font-medium text-gray-900">{{ order.shipping_method.name }}</p>
                        <p v-if="order.shipping_method.description" class="text-sm text-gray-600">{{ order.shipping_method.description }}</p>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="rounded-lg shadow-sm p-6 mb-6" :class="themeConfig.cardBackground">
                <h2 class="text-xl font-semibold mb-4" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">Order Items</h2>

                <div class="divide-y divide-gray-200">
                    <div v-for="item in order.items" :key="item.product_name" class="py-4 flex items-center">
                        <div class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-md border border-gray-200 bg-gray-100">
                            <img
                                v-if="item.image"
                                :src="item.image"
                                :alt="item.product_name"
                                class="h-full w-full object-cover object-center"
                            />
                            <div v-else class="h-full w-full flex items-center justify-center text-gray-400">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="font-medium text-gray-900">{{ item.product_name }}</p>
                            <p class="text-sm text-gray-600">Quantity: {{ item.quantity }}</p>
                        </div>
                        <p class="font-medium text-gray-900">{{ formatPrice(item.total_cents) }}</p>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <p class="text-gray-600">Subtotal</p>
                            <p class="font-medium text-gray-900">{{ formatPrice(order.subtotal_cents) }}</p>
                        </div>
                        <div v-if="order.shipping_cost_cents > 0" class="flex justify-between text-sm">
                            <p class="text-gray-600">Shipping</p>
                            <p class="font-medium text-gray-900">{{ formatPrice(order.shipping_cost_cents) }}</p>
                        </div>
                        <div v-if="order.tax_cents > 0" class="flex justify-between text-sm">
                            <p class="text-gray-600">Tax</p>
                            <p class="font-medium text-gray-900">{{ formatPrice(order.tax_cents) }}</p>
                        </div>
                        <div class="flex justify-between text-lg font-semibold pt-2 border-t border-gray-200">
                            <p class="text-gray-900">Total</p>
                            <p class="text-gray-900">{{ formatPrice(order.total_cents) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Next Steps -->
            <div class="rounded-lg shadow-sm p-6 mb-6" :class="themeConfig.cardBackground">
                <h2 class="text-xl font-semibold mb-4" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">What Happens Next?</h2>

                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-100 text-blue-600 font-semibold text-sm">
                                1
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="font-medium text-gray-900">Order Confirmation Email</p>
                            <p class="text-sm text-gray-600">We've sent a confirmation email to {{ order.customer_email }}</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-100 text-blue-600 font-semibold text-sm">
                                2
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="font-medium text-gray-900">Order Processing</p>
                            <p class="text-sm text-gray-600">We'll begin preparing your order for {{ order.fulfilment_type === 'shipping' ? 'shipment' : 'pickup' }}</p>
                        </div>
                    </div>

                    <div v-if="order.fulfilment_type === 'shipping'" class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-100 text-blue-600 font-semibold text-sm">
                                3
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="font-medium text-gray-900">Shipping Updates</p>
                            <p class="text-sm text-gray-600">You'll receive tracking information once your order ships</p>
                        </div>
                    </div>

                    <div v-else class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-100 text-blue-600 font-semibold text-sm">
                                3
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="font-medium text-gray-900">Ready for Pickup</p>
                            <p class="text-sm text-gray-600">We'll notify you when your order is ready to collect</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4">
                <a
                    :href="`/store/${store.id}`"
                    class="flex-1 text-center px-6 py-3 rounded-lg font-semibold transition-all shadow-lg hover:shadow-xl"
                    :class="themeConfig.buttonPrimary"
                >
                    Continue Shopping
                </a>
                <a
                    href="/track"
                    class="flex-1 text-center px-6 py-3 rounded-lg font-semibold transition-all shadow-lg hover:shadow-xl"
                    :class="themeConfig.buttonSecondary"
                >
                    Track Order
                </a>
            </div>

            <!-- Contact Information -->
            <div class="mt-6 text-center text-sm text-gray-600">
                <p>Questions about your order?</p>
                <div class="mt-2 space-x-4">
                    <a v-if="store.contact_email" :href="`mailto:${store.contact_email}`" class="text-blue-600 hover:text-blue-700">
                        {{ store.contact_email }}
                    </a>
                    <span v-if="store.contact_email && store.contact_phone" class="text-gray-400">|</span>
                    <a v-if="store.contact_phone" :href="`tel:${store.contact_phone}`" class="text-blue-600 hover:text-blue-700">
                        {{ store.contact_phone }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useTheme } from '@/Composables/useTheme';

const props = defineProps({
    store: Object,
    order: Object,
});

// Initialize theme
const { config: themeConfig } = useTheme(props.store.theme);

// Check if store has address information
const hasStoreAddress = computed(() => {
    return props.store.address_primary && props.store.address_city;
});

// Build full address string for map URLs
const fullAddress = computed(() => {
    const parts = [
        props.store.address_primary,
        props.store.address_city,
        props.store.address_state,
        props.store.address_postcode
    ].filter(Boolean);
    return parts.join(', ');
});

// Google Maps URL
const googleMapsUrl = computed(() => {
    return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(fullAddress.value)}`;
});

const formatPrice = (cents) => {
    return new Intl.NumberFormat('en-AU', {
        style: 'currency',
        currency: 'AUD',
    }).format(cents / 100);
};

const formatStatus = (status) => {
    const statusMap = {
        'pending': 'Pending',
        'processing': 'Processing',
        'accepted': 'Accepted',
        'in_progress': 'In Progress',
        'ready': 'In Progress',  // Merged with in_progress
        'packing': 'In Progress',  // Merged with in_progress
        'shipped': 'Shipped',
        'delivered': 'Delivered',
        'ready_for_pickup': 'Ready for Pickup',
        'picked_up': 'Picked Up',
        'cancelled': 'Cancelled',
    };
    return statusMap[status] || status;
};

const formatPaymentStatus = (status) => {
    const statusMap = {
        'pending': 'Pending',
        'paid': 'Paid',
        'failed': 'Failed',
        'refunded': 'Refunded',
    };
    return statusMap[status] || status;
};

const formatFulfillmentType = (type) => {
    return type === 'shipping' ? 'Delivery' : 'Pickup';
};

const statusBadgeClass = computed(() => {
    const status = props.order.status;
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'processing': 'bg-blue-100 text-blue-800',
        'accepted': 'bg-blue-100 text-blue-800',
        'in_progress': 'bg-blue-100 text-blue-800',
        'ready': 'bg-blue-100 text-blue-800',  // Merged with in_progress
        'packing': 'bg-blue-100 text-blue-800',  // Merged with in_progress
        'shipped': 'bg-purple-100 text-purple-800',
        'delivered': 'bg-green-100 text-green-800',
        'ready_for_pickup': 'bg-green-100 text-green-800',
        'picked_up': 'bg-gray-100 text-gray-800',
        'cancelled': 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
});

// Define status progression based on fulfillment type
const statusSteps = computed(() => {
    const isShipping = props.order.fulfilment_type === 'shipping';

    if (isShipping) {
        // Shipping flow: Pending → Accepted → Shipped → Delivered
        return [
            { status: 'pending', label: 'Order Placed' },
            { status: 'accepted', label: 'Accepted' },
            { status: 'shipped', label: 'Shipped' },
            { status: 'delivered', label: 'Delivered' },
        ];
    } else {
        // Pickup flow: Pending → Accepted → Ready for Pickup → Picked Up
        return [
            { status: 'pending', label: 'Order Placed' },
            { status: 'accepted', label: 'Accepted' },
            { status: 'ready_for_pickup', label: 'Ready for Pickup' },
            { status: 'picked_up', label: 'Picked Up' },
        ];
    }
});

// Determine which steps are completed and current
const statusStepsWithState = computed(() => {
    const currentStatus = props.order.status;
    const steps = statusSteps.value;

    // Define status order/priority
    const statusOrder = {
        'pending': 1,
        'accepted': 2,
        'in_progress': 2.5,
        'ready': 2.5,  // Merged with in_progress
        'packing': 2.5,  // Merged with in_progress
        'shipped': 3,
        'ready_for_pickup': 3,
        'delivered': 4,
        'picked_up': 4,
        'completed': 4,
        'cancelled': 99,
    };

    const currentStatusPriority = statusOrder[currentStatus] || 1;

    return steps.map((step, index) => {
        const stepPriority = statusOrder[step.status] || index + 1;

        return {
            ...step,
            completed: currentStatusPriority > stepPriority,
            current: currentStatusPriority === stepPriority ||
                     (currentStatus === 'in_progress' && step.status === 'accepted') ||
                     (currentStatus === 'ready' && step.status === 'accepted') ||
                     (currentStatus === 'packing' && step.status === 'accepted'),
        };
    });
});

// Calculate progress bar percentage
const progressPercentage = computed(() => {
    const steps = statusStepsWithState.value;
    const completedSteps = steps.filter(s => s.completed).length;
    const currentStepIndex = steps.findIndex(s => s.current);

    if (currentStepIndex === -1) {
        // No current step, use completed count
        return (completedSteps / (steps.length - 1)) * 100;
    }

    // Progress to current step (count completed + half of current)
    const progress = (completedSteps + 0.5) / (steps.length - 1);
    return Math.min(progress * 100, 100);
});

// Get CSS classes for step circle
const getStepCircleClass = (step) => {
    if (step.completed) {
        return 'bg-blue-600 border-blue-600';
    } else if (step.current) {
        return 'bg-blue-600 border-blue-600';
    } else {
        return 'bg-white border-gray-300';
    }
};
</script>
