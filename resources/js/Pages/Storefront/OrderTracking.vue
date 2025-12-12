<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <h1 class="text-2xl font-bold text-gray-900">Track Your Order</h1>
            </div>
        </header>

        <main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Search Form -->
            <div v-if="!order" class="bg-white rounded-lg shadow-sm p-6 mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Enter Your Order Number</h2>
                <form @submit.prevent="trackOrder" class="space-y-4">
                    <div>
                        <label for="public_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Order Number (e.g., SF-XXXXX)
                        </label>
                        <input
                            id="public_id"
                            v-model="orderNumber"
                            type="text"
                            placeholder="SF-12345"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                            required
                        />
                    </div>

                    <!-- Error Message -->
                    <div v-if="error" class="bg-red-50 border border-red-200 rounded-md p-4">
                        <p class="text-sm text-red-800">{{ error }}</p>
                    </div>

                    <button
                        type="submit"
                        class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition-colors"
                    >
                        Track Order
                    </button>
                </form>
            </div>

            <!-- Order Details -->
            <div v-if="order" class="space-y-6">
                <!-- Order Header -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">{{ order.public_id }}</h2>
                            <p class="text-sm text-gray-600 mt-1">
                                Ordered {{ formatDateTime(order.created_at) }}
                            </p>
                        </div>
                        <div class="text-right">
                            <div
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                :class="isConnected ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                            >
                                <div
                                    class="w-2 h-2 rounded-full mr-2"
                                    :class="isConnected ? 'bg-green-500' : 'bg-gray-400'"
                                ></div>
                                {{ isConnected ? 'Live Updates' : 'Offline' }}
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-200">
                        <div>
                            <p class="text-sm text-gray-600">Store</p>
                            <p class="text-base font-medium text-gray-900">{{ order.store_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Total</p>
                            <p class="text-base font-medium text-gray-900">{{ formatCurrency(order.total_cents) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Items</p>
                            <p class="text-base font-medium text-gray-900">{{ order.items_count }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Fulfilment</p>
                            <p class="text-base font-medium text-gray-900">
                                {{ order.fulfilment_type === 'pickup' ? 'Pickup' : 'Shipping' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Status Timeline -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Order Status</h3>

                    <div class="space-y-4">
                        <div
                            v-for="(step, index) in statusSteps"
                            :key="step.status"
                            class="flex items-start"
                        >
                            <div class="flex flex-col items-center mr-4">
                                <div
                                    class="w-8 h-8 rounded-full flex items-center justify-center"
                                    :class="step.completed ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-400'"
                                >
                                    <svg v-if="step.completed" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    <span v-else>{{ index + 1 }}</span>
                                </div>
                                <div
                                    v-if="index < statusSteps.length - 1"
                                    class="w-0.5 h-12 mt-2"
                                    :class="step.completed ? 'bg-blue-600' : 'bg-gray-200'"
                                ></div>
                            </div>
                            <div class="pt-1">
                                <p class="font-medium text-gray-900">{{ step.label }}</p>
                                <p v-if="step.status === order.status" class="text-sm text-blue-600 mt-1">
                                    Current Status
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shipping/Pickup Details -->
                <div v-if="order.fulfilment_type === 'shipping' && order.shipping" class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Shipping Information</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-600">Method</p>
                            <p class="text-base text-gray-900">{{ order.shipping.method || 'Standard Shipping' }}</p>
                        </div>
                        <div v-if="order.shipping.tracking_code">
                            <p class="text-sm text-gray-600">Tracking Code</p>
                            <p class="text-base font-mono text-gray-900">{{ order.shipping.tracking_code }}</p>
                        </div>
                        <div v-if="order.shipping.tracking_url">
                            <a
                                :href="order.shipping.tracking_url"
                                target="_blank"
                                class="inline-flex items-center text-blue-600 hover:text-blue-800"
                            >
                                Track shipment
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <div v-else-if="order.fulfilment_type === 'pickup'" class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Pickup Details</h3>

                    <!-- Pickup Time -->
                    <div v-if="order.pickup_time" class="mb-6">
                        <p class="text-sm text-gray-600">Scheduled Pickup Time</p>
                        <p class="text-base text-gray-900 font-medium">{{ formatDateTime(order.pickup_time) }}</p>
                    </div>

                    <!-- Pickup Location -->
                    <div v-if="hasStoreAddress">
                        <p class="text-sm text-gray-600 mb-2">Pickup Location</p>
                        <div class="text-gray-900 mb-4">
                            <p class="font-medium">{{ store.name }}</p>
                            <p>{{ store.address_primary }}</p>
                            <p>{{ store.address_city }}, {{ store.address_state }} {{ store.address_postcode }}</p>
                        </div>
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

                <!-- Track Another Order -->
                <div class="text-center">
                    <button
                        @click="resetTracking"
                        class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                    >
                        Track another order →
                    </button>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    order: Object,
    publicId: String,
    error: String,
    websocketChannel: String,
    store: Object,
});

const orderNumber = ref(props.publicId || '');
const isConnected = ref(false);

const statusSteps = computed(() => {
    if (!props.order) return [];

    if (props.order.fulfilment_type === 'shipping') {
        return [
            { status: 'pending', label: 'Order Placed', completed: true },
            { status: 'in_progress', label: 'Processing', completed: ['in_progress', 'ready', 'packing', 'shipped', 'delivered'].includes(props.order.status) },
            { status: 'packing', label: 'Packing', completed: ['packing', 'shipped', 'delivered'].includes(props.order.status) },
            { status: 'shipped', label: 'Shipped', completed: ['shipped', 'delivered'].includes(props.order.status) },
            { status: 'delivered', label: 'Delivered', completed: props.order.status === 'delivered' },
        ];
    } else {
        return [
            { status: 'pending', label: 'Order Placed', completed: true },
            { status: 'in_progress', label: 'Preparing', completed: ['in_progress', 'ready_for_pickup', 'picked_up'].includes(props.order.status) },
            { status: 'ready_for_pickup', label: 'Ready for Pickup', completed: ['ready_for_pickup', 'picked_up'].includes(props.order.status) },
            { status: 'picked_up', label: 'Picked Up', completed: props.order.status === 'picked_up' },
        ];
    }
});

const trackOrder = () => {
    router.post(route('storefront.track.post'), {
        public_id: orderNumber.value,
    });
};

const resetTracking = () => {
    router.get(route('storefront.track'));
};

const formatCurrency = (cents) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(cents / 100);
};

const formatDateTime = (datetime) => {
    return new Date(datetime).toLocaleString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
};

// Store address helpers
const hasStoreAddress = computed(() => {
    return props.store && props.store.address_primary && props.store.address_city;
});

const fullAddress = computed(() => {
    if (!props.store) return '';
    const parts = [
        props.store.address_primary,
        props.store.address_city,
        props.store.address_state,
        props.store.address_postcode
    ].filter(Boolean);
    return parts.join(', ');
});

const googleMapsUrl = computed(() => {
    return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(fullAddress.value)}`;
});

// WebSocket setup for real-time updates
let orderChannel = null;

const setupWebSocket = () => {
    if (!props.websocketChannel || !window.Echo) {
        return;
    }

    orderChannel = window.Echo.channel(props.websocketChannel)
        .listen('OrderStatusUpdated', (event) => {
            console.log('Order status updated:', event);
            // Reload the page to get updated order data
            router.reload({ only: ['order'] });
        })
        .listen('ShippingStatusUpdated', (event) => {
            console.log('Shipping status updated:', event);
            router.reload({ only: ['order'] });
        });

    // Connection status
    if (window.Echo.connector && window.Echo.connector.pusher) {
        window.Echo.connector.pusher.connection.bind('connected', () => {
            isConnected.value = true;
        });

        window.Echo.connector.pusher.connection.bind('disconnected', () => {
            isConnected.value = false;
        });

        isConnected.value = window.Echo.connector.pusher.connection.state === 'connected';
    }
};

const cleanupWebSocket = () => {
    if (orderChannel && props.websocketChannel) {
        window.Echo.leave(props.websocketChannel);
    }
};

onMounted(() => {
    if (props.order) {
        setupWebSocket();
    }
});

onUnmounted(() => {
    cleanupWebSocket();
});
</script>
