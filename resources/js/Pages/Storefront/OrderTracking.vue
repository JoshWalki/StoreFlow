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
                                    class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0"
                                    :class="step.completed ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-400'"
                                >
                                    <svg v-if="step.completed" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    <span v-else>{{ index + 1 }}</span>
                                </div>
                                <div
                                    v-if="index < statusSteps.length - 1"
                                    class="w-0.5 mt-2"
                                    :class="[
                                        step.completed ? 'bg-blue-600' : 'bg-gray-200',
                                        step.status === 'shipped' && step.completed && (order.shipping?.method || order.courier_company || order.tracking_number) ? 'h-32' : 'h-12'
                                    ]"
                                ></div>
                            </div>
                            <div class="pt-1 flex-1">
                                <p class="font-medium text-gray-900">{{ step.label }}</p>
                                <p v-if="step.status === order.status" class="text-sm text-blue-600 mt-1">
                                    Current Status
                                </p>

                                <!-- Tracking Information for Shipped Status -->
                                <div v-if="step.status === 'shipped' && step.completed && (order.fulfilment_type === 'shipping' || order.fulfilment_type === 'delivery')" class="mt-3 space-y-2">
                                    <!-- Delivery Method and Courier (side by side) -->
                                    <div v-if="order.shipping?.method || order.courier_company" class="grid grid-cols-2 gap-4">
                                        <!-- Delivery Method -->
                                        <div v-if="order.shipping?.method" class="flex items-start">
                                            <svg class="w-4 h-4 text-gray-400 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                                                <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z" />
                                            </svg>
                                            <div>
                                                <p class="text-xs text-gray-500">Delivery Method</p>
                                                <p class="text-sm text-gray-900 font-medium">{{ order.shipping.method }}</p>
                                            </div>
                                        </div>

                                        <!-- Courier Company -->
                                        <div v-if="order.courier_company" class="flex items-start">
                                            <svg class="w-4 h-4 text-gray-400 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                                                <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z" />
                                            </svg>
                                            <div>
                                                <p class="text-xs text-gray-500">Courier</p>
                                                <p class="text-sm text-gray-900 font-medium">{{ order.courier_company }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tracking Number -->
                                    <div v-if="order.tracking_number" class="flex items-start">
                                        <svg class="w-4 h-4 text-gray-400 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                                        </svg>
                                        <div>
                                            <p class="text-xs text-gray-500">Tracking Number</p>
                                            <p class="text-sm text-gray-900 font-mono">{{ order.tracking_number }}</p>
                                        </div>
                                    </div>

                                    <!-- Tracking URL -->
                                    <div v-if="order.shipping?.tracking_url" class="flex items-start">
                                        <a
                                            :href="order.shipping.tracking_url"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800 font-medium"
                                        >
                                            Track your shipment
                                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                            </svg>
                                        </a>
                                    </div>

                                    <!-- Message if no tracking available yet -->
                                    <div v-if="!order.shipping?.method && !order.courier_company && !order.tracking_number" class="text-xs text-gray-500 italic">
                                        Tracking information will be available soon
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div v-if="order.items && order.items.length > 0" class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Items</h3>
                    <div class="space-y-4">
                        <div v-for="item in order.items" :key="item.id" class="flex justify-between items-start border-b border-gray-200 pb-4 last:border-0 last:pb-0">
                            <div class="flex-1">
                                <p class="font-medium text-gray-900">{{ item.name }}</p>
                                <p class="text-sm text-gray-600">Quantity: {{ item.quantity }}</p>

                                <!-- Customizations -->
                                <div v-if="item.customizations && item.customizations.length > 0" class="mt-1">
                                    <p class="text-xs text-gray-500">
                                        {{ item.customizations.map(c => `${c.group_name}: ${c.option_name}`).join(', ') }}
                                    </p>
                                </div>

                                <!-- Addons -->
                                <div v-if="item.addons && item.addons.length > 0" class="mt-1">
                                    <p class="text-xs text-gray-500">
                                        Add-ons: {{ item.addons.map(a => `${a.addon_name}: ${a.option_name} (x${a.quantity || 1})`).join(', ') }}
                                    </p>
                                </div>

                                <!-- Special Instructions -->
                                <div v-if="item.special_instructions" class="mt-1">
                                    <p class="text-xs text-gray-500 italic">Note: {{ item.special_instructions }}</p>
                                </div>
                            </div>
                            <div class="ml-4 text-right">
                                <p class="font-medium text-gray-900">{{ formatCurrency(item.total_cents) }}</p>
                                <p class="text-xs text-gray-500">{{ formatCurrency(item.unit_price_cents) }} each</p>
                            </div>
                        </div>
                    </div>

                    <!-- Order Totals -->
                    <div class="mt-4 pt-4 border-t border-gray-200 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="text-gray-900">{{ formatCurrency(order.items_total_cents) }}</span>
                        </div>
                        <div v-if="order.shipping_cost_cents > 0" class="flex justify-between text-sm">
                            <span class="text-gray-600">Delivery</span>
                            <span class="text-gray-900">{{ formatCurrency(order.shipping_cost_cents) }}</span>
                        </div>
                        <div v-if="order.discount_cents > 0" class="flex justify-between text-sm">
                            <span class="text-gray-600">Discount</span>
                            <span class="text-green-600">-{{ formatCurrency(order.discount_cents) }}</span>
                        </div>
                        <div class="flex justify-between text-base font-semibold pt-2 border-t border-gray-200">
                            <span class="text-gray-900">Total</span>
                            <span class="text-gray-900">{{ formatCurrency(order.total_cents) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Delivery Address with Verification -->
                <div v-if="(order.fulfilment_type === 'shipping' || order.fulfilment_type === 'delivery') && order.shipping_address" class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Delivery Address</h3>

                    <!-- Verification Form -->
                    <div v-if="!isAddressVerified" class="space-y-4">
                        <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                            <p class="text-sm text-blue-800">
                                To view the full delivery address, please verify your email or mobile number.
                            </p>
                        </div>

                        <form @submit.prevent="verifyAddress" class="space-y-3">
                            <div>
                                <label for="verification_input" class="block text-sm font-medium text-gray-700 mb-1">
                                    Email or Mobile Number
                                </label>
                                <input
                                    id="verification_input"
                                    v-model="verificationInput"
                                    type="text"
                                    placeholder="Enter your email or mobile number"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                    required
                                />
                            </div>

                            <div v-if="verificationError" class="bg-red-50 border border-red-200 rounded-md p-3">
                                <p class="text-sm text-red-800">{{ verificationError }}</p>
                            </div>

                            <button
                                type="submit"
                                class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition-colors"
                            >
                                Verify & View Address
                            </button>
                        </form>
                    </div>

                    <!-- Verified Address Display -->
                    <div v-else class="space-y-3">
                        <div class="bg-green-50 border border-green-200 rounded-md p-3 flex items-center mb-4">
                            <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <p class="text-sm text-green-800 font-medium">Address Verified</p>
                        </div>

                        <div class="text-gray-900">
                            <p class="font-medium">{{ order.shipping_address.name }}</p>
                            <p>{{ order.shipping_address.line1 }}</p>
                            <p v-if="order.shipping_address.line2">{{ order.shipping_address.line2 }}</p>
                            <p>{{ order.shipping_address.city }}, {{ order.shipping_address.state }} {{ order.shipping_address.postcode }}</p>
                            <p>{{ order.shipping_address.country }}</p>
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
const isAddressVerified = ref(false);
const verificationInput = ref('');
const verificationError = ref('');

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
    // Navigate to tracking page with order number in URL
    router.get(route('storefront.track', { order: orderNumber.value }));
};

const resetTracking = () => {
    router.get(route('storefront.track'));
};

const verifyAddress = () => {
    verificationError.value = '';

    if (!props.order) return;

    const input = verificationInput.value.trim().toLowerCase();
    const customerEmail = props.order.customer_email?.toLowerCase();
    const customerMobile = props.order.customer_mobile?.replace(/\s+/g, '');
    const inputMobile = input.replace(/\s+/g, '');

    // Check if input matches email or mobile
    if (input === customerEmail || inputMobile === customerMobile) {
        isAddressVerified.value = true;
        verificationError.value = '';
    } else {
        verificationError.value = 'The email or mobile number you entered does not match our records. Please try again.';
    }
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
