<template>
    <!-- Full-width container with custom padding -->
    <div class="-m-8 min-h-screen bg-gray-50 dark:bg-gray-900 overflow-x-hidden">
            <div class="px-4 sm:px-5 lg:px-6 py-4 max-w-full">
                <!-- Header with Status Indicators -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-3 gap-2">
                    <div class="flex items-center gap-2">
                        <h1 class="text-lg sm:text-xl font-bold text-gray-800 dark:text-white">
                            Operations Dashboard
                        </h1>

                        <!-- Real-time Connection Indicator (light only) -->
                        <div
                            v-if="store"
                            class="w-2 h-2 rounded-full"
                            :class="[
                                !store.is_active
                                    ? 'bg-red-500'
                                    : isConnected
                                    ? 'bg-green-500 animate-pulse-subtle'
                                    : 'bg-red-500',
                            ]"
                            :title="!store.is_active ? 'Store Offline' : isConnected ? 'Live' : 'Disconnected'"
                        ></div>

                        <!-- Countdown to Closing -->
                        <div
                            v-if="store && store.is_active && store.close_time && timeUntilClose"
                            class="flex items-center space-x-1 px-2 py-0.5 bg-orange-50 dark:bg-orange-900/20 rounded border border-orange-200 dark:border-orange-800"
                        >
                            <svg class="w-3 h-3 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-[10px] font-medium text-orange-700 dark:text-orange-300">
                                Closes in {{ timeUntilClose }}
                            </span>
                        </div>
                    </div>

                    <!-- Full Screen Display Button -->
                    <button
                        @click="openFullScreenDisplay"
                        class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-md"
                        title="Open full-screen display view"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                        </svg>
                        <span class="hidden sm:inline text-sm font-medium">Display View</span>
                    </button>
                </div>

                <!-- Tabs for Pickup/Shipping with Search Toggle -->
                <div class="flex justify-between items-center mb-3">
                    <div class="flex items-center gap-2">
                        <div class="flex space-x-1.5">
                            <button
                                @click="activeTab = 'shipping'"
                                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors"
                                :class="
                                    activeTab === 'shipping'
                                        ? 'bg-blue-600 text-white'
                                        : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600'
                                "
                            >
                                <svg
                                    class="w-4 h-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"
                                    />
                                </svg>
                                <span>Shipping</span>
                                <span
                                    v-if="shippingPendingCount > 0"
                                    class="inline-flex items-center justify-center w-5 h-5 text-[10px] font-bold rounded-full"
                                    :class="
                                        activeTab === 'shipping'
                                            ? 'bg-white dark:bg-gray-800 text-blue-600 dark:text-blue-400'
                                            : 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-200'
                                    "
                                >
                                    {{ shippingPendingCount }}
                                </span>
                            </button>
                            <button
                                @click="activeTab = 'pickup'"
                                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors"
                                :class="
                                    activeTab === 'pickup'
                                        ? 'bg-blue-600 text-white'
                                        : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600'
                                "
                            >
                                <svg
                                    class="w-4 h-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"
                                    />
                                </svg>
                                <span>Pickup</span>
                                <span
                                    v-if="pickupPendingCount > 0"
                                    class="inline-flex items-center justify-center w-5 h-5 text-[10px] font-bold rounded-full"
                                    :class="
                                        activeTab === 'pickup'
                                            ? 'bg-white dark:bg-gray-800 text-blue-600 dark:text-blue-400'
                                            : 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-200'
                                    "
                                >
                                    {{ pickupPendingCount }}
                                </span>
                            </button>
                        </div>

                        <!-- Compact Pickup ETA (next to tabs) -->
                        <Transition name="search">
                            <div v-if="activeTab === 'pickup'" class="flex items-center gap-2 px-3 py-1.5 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-xs text-green-900 dark:text-green-100">ETA:</span>
                                <input
                                    v-model.number="defaultPickupMinutes"
                                    type="number"
                                    min="5"
                                    max="480"
                                    step="5"
                                    class="w-14 px-2 py-0.5 text-xs border border-green-300 dark:border-green-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-1 focus:ring-green-500"
                                />
                                <span class="text-xs text-green-700 dark:text-green-300">min</span>
                                <button
                                    @click="updateDefaultPickupTime"
                                    :disabled="updatingPickupTime"
                                    class="px-2 py-0.5 text-xs font-medium text-white bg-green-600 rounded hover:bg-green-700 disabled:opacity-50 transition-colors"
                                >
                                    {{ updatingPickupTime ? '...' : 'Save' }}
                                </button>
                            </div>
                        </Transition>
                    </div>

                    <!-- Search Toggle Button -->
                    <button
                        @click="showSearch = !showSearch"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors border"
                        :class="showSearch
                            ? 'bg-blue-600 text-white border-blue-600'
                            : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600'"
                        title="Toggle search"
                    >
                        <svg
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                            />
                        </svg>
                        <span class="hidden sm:inline">Search</span>
                    </button>
                </div>

                <!-- Search Box (Toggleable) -->
                <Transition name="search">
                    <div v-if="showSearch" class="mb-3">
                        <div class="relative max-w-2xl">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search by order ID, customer, product..."
                                class="w-full px-3 py-1.5 pl-8 pr-3 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            />
                            <div
                                class="absolute inset-y-0 left-0 flex items-center pl-2.5 pointer-events-none"
                            >
                                <svg
                                    class="w-4 h-4 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                    />
                                </svg>
                            </div>
                            <button
                                v-if="searchQuery"
                                @click="searchQuery = ''"
                                class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-400 hover:text-gray-600"
                            >
                                <svg
                                    class="w-4 h-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </Transition>

                <!-- Mobile Status Tabs (visible on mobile only) -->
                <div class="lg:hidden mb-4">
                    <div class="flex gap-1.5 overflow-x-auto pb-2">
                        <button
                            @click="mobileStatusView = 'pending'"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium whitespace-nowrap transition-colors"
                            :class="mobileStatusView === 'pending'
                                ? 'bg-blue-600 text-white'
                                : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200'"
                        >
                            Pending ({{ filteredPendingOrders.length }})
                        </button>
                        <button
                            @click="mobileStatusView = 'in_progress'"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium whitespace-nowrap transition-colors"
                            :class="mobileStatusView === 'in_progress'
                                ? 'bg-blue-600 text-white'
                                : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200'"
                        >
                            In Progress ({{ inProgressOrders.length }})
                        </button>
                        <button
                            v-if="activeTab === 'shipping'"
                            @click="mobileStatusView = 'shipped'"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium whitespace-nowrap transition-colors"
                            :class="mobileStatusView === 'shipped'
                                ? 'bg-blue-600 text-white'
                                : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200'"
                        >
                            Shipped ({{ filteredShippedOrders.length }})
                        </button>
                        <button
                            v-else
                            @click="mobileStatusView = 'ready_for_pickup'"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium whitespace-nowrap transition-colors"
                            :class="mobileStatusView === 'ready_for_pickup'
                                ? 'bg-blue-600 text-white'
                                : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200'"
                        >
                            Ready ({{ filteredReadyForPickupOrders.length }})
                        </button>
                        <button
                            @click="mobileStatusView = 'completed'"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium whitespace-nowrap transition-colors"
                            :class="mobileStatusView === 'completed'
                                ? 'bg-blue-600 text-white'
                                : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200'"
                        >
                            Completed ({{ filteredCompletedOrders.length }})
                        </button>
                    </div>
                </div>

                <!-- Mobile Single Column View -->
                <div class="lg:hidden">
                    <OrderStatusColumn
                        v-if="mobileStatusView === 'pending'"
                        status="pending"
                        title="Pending"
                        :orders="filteredPendingOrders"
                        @order-click="openOrderDetail"
                        @order-drop="handleOrderDrop"
                    />
                    <OrderStatusColumn
                        v-if="mobileStatusView === 'in_progress'"
                        status="in_progress"
                        title="In Progress"
                        :orders="inProgressOrders"
                        @order-click="openOrderDetail"
                        @order-drop="handleOrderDrop"
                    />
                    <OrderStatusColumn
                        v-if="mobileStatusView === 'shipped' && activeTab === 'shipping'"
                        status="shipped"
                        title="Shipped"
                        :orders="filteredShippedOrders"
                        @order-click="openOrderDetail"
                        @order-drop="handleOrderDrop"
                    />
                    <OrderStatusColumn
                        v-if="mobileStatusView === 'ready_for_pickup' && activeTab === 'pickup'"
                        status="ready_for_pickup"
                        title="Ready for Pickup"
                        :orders="filteredReadyForPickupOrders"
                        @order-click="openOrderDetail"
                        @order-drop="handleOrderDrop"
                    />
                    <OrderStatusColumn
                        v-if="mobileStatusView === 'completed'"
                        status="completed"
                        title="Completed"
                        :orders="filteredCompletedOrders"
                        @order-click="openOrderDetail"
                        @order-drop="handleOrderDrop"
                    />
                </div>

                <!-- Desktop Kanban Board (4 columns) -->
                <div class="hidden lg:grid gap-3 pb-3 lg:grid-cols-4 mt-1.5">
                    <!-- Pending Orders -->
                    <OrderStatusColumn
                        status="pending"
                        title="Pending"
                        :orders="filteredPendingOrders"
                        @order-click="openOrderDetail"
                        @order-drop="handleOrderDrop"
                    />

                    <!-- In Progress (includes ready and packing statuses) -->
                    <OrderStatusColumn
                        status="in_progress"
                        title="In Progress"
                        :orders="inProgressOrders"
                        @order-click="openOrderDetail"
                        @order-drop="handleOrderDrop"
                    />

                    <!-- Shipped (Shipping only) or Ready for Pickup (Pickup only) -->
                    <OrderStatusColumn
                        v-if="activeTab === 'shipping'"
                        status="shipped"
                        title="Shipped"
                        :orders="filteredShippedOrders"
                        @order-click="openOrderDetail"
                        @order-drop="handleOrderDrop"
                    />
                    <OrderStatusColumn
                        v-else
                        status="ready_for_pickup"
                        title="Ready for Pickup"
                        :orders="filteredReadyForPickupOrders"
                        @order-click="openOrderDetail"
                        @order-drop="handleOrderDrop"
                    />

                    <!-- Completed -->
                    <OrderStatusColumn
                        status="completed"
                        title="Completed"
                        :orders="filteredCompletedOrders"
                        @order-click="openOrderDetail"
                        @order-drop="handleOrderDrop"
                    />
                </div>

                <!-- Order Detail Modal -->
                <OrderDetailModal
                    v-if="selectedOrder"
                    :is-open="isModalOpen"
                    :order="selectedOrder"
                    :default-pickup-minutes="store?.default_pickup_minutes || 30"
                    @close="closeOrderDetail"
                    @status-updated="handleStatusUpdated"
                />
            </div>
        </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from "vue";
import { router } from "@inertiajs/vue3";
import axios from "axios";
import OrderStatusColumn from "@/Components/Operations/OrderStatusColumn.vue";
import OrderDetailModal from "@/Components/Operations/OrderDetailModal.vue";
import { useToast } from "@/Composables/useToast";

const props = defineProps({
    store: Object,
    user: Object,
    orders: Array,
});

const toast = useToast();

// Real-time connection state
const isConnected = ref(false);

// Search query
const searchQuery = ref("");
const showSearch = ref(false);

// Active tab (shipping or pickup)
const activeTab = ref("pickup");

// Default pickup time
const defaultPickupMinutes = ref(props.store?.default_pickup_minutes || 30);
const updatingPickupTime = ref(false);

// Mobile status view
const mobileStatusView = ref("pending");

// Modal state
const isModalOpen = ref(false);
const selectedOrder = ref(null);

// Countdown to closing time
const timeUntilClose = ref(null);
let countdownInterval = null;

// Sound notification permission (managed by layout's bell icon)
const soundEnabled = ref(false);
const notificationInterval = ref(null);

const calculateTimeUntilClose = () => {
    if (!props.store?.close_time) {
        timeUntilClose.value = null;
        return;
    }

    // Get current time in store's timezone
    const timezone = props.store.timezone || 'UTC';
    const now = new Date();

    // Create a date object for today's closing time
    const [hours, minutes] = props.store.close_time.split(':');
    const closeTime = new Date();
    closeTime.setHours(parseInt(hours), parseInt(minutes), 0, 0);

    // Calculate difference in milliseconds
    let diff = closeTime - now;

    // If the closing time has passed today, trigger store closure check
    if (diff < 0) {
        timeUntilClose.value = null;
        // Trigger the store closure check on the server
        if (props.store.is_active) {
            fetch('/dashboard/check-hours', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
            }).then(() => {
                router.reload({ only: ['store'] });
            });
        }
        return;
    }

    // Calculate hours, minutes, and seconds
    const hoursLeft = Math.floor(diff / (1000 * 60 * 60));
    const minutesLeft = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const secondsLeft = Math.floor((diff % (1000 * 60)) / 1000);

    // Format the countdown
    if (hoursLeft > 0) {
        timeUntilClose.value = `${hoursLeft}h ${minutesLeft}m`;
    } else if (minutesLeft > 0) {
        timeUntilClose.value = `${minutesLeft}m ${secondsLeft}s`;
    } else {
        timeUntilClose.value = `${secondsLeft}s`;
    }

    // When countdown reaches 0, trigger store closure check
    if (diff <= 1000 && props.store.is_active) {
        setTimeout(() => {
            fetch('/dashboard/check-hours', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
            }).then(() => {
                router.reload({ only: ['store'] });
            });
        }, 1000);
    }
};

// Update default pickup time
const updateDefaultPickupTime = async () => {
    if (defaultPickupMinutes.value < 5 || defaultPickupMinutes.value > 480) {
        toast.error('Pickup time must be between 5 and 480 minutes');
        return;
    }

    updatingPickupTime.value = true;

    try {
        await axios.put('/store/settings/pickup', {
            default_pickup_minutes: defaultPickupMinutes.value,
        });

        toast.success('Default pickup time updated');
    } catch (error) {
        console.error('Failed to update default pickup time:', error);
        toast.error('Failed to update default pickup time');
    } finally {
        updatingPickupTime.value = false;
    }
};

// Sound notification function
const playNotificationSound = (fulfilmentType = 'pickup') => {
    // Only play if user has enabled sound
    if (!soundEnabled.value) return;

    try {
        // Use bell.mp3 for shipping orders, notification.wav for pickup
        const soundFile = fulfilmentType === 'shipping'
            ? '/sounds/bell.mp3'
            : '/sounds/notification.wav';

        const audio = new Audio(soundFile);
        audio.volume = 0.7;
        audio.play().catch(e => {
            // Browser blocked autoplay - user needs to enable via bell icon
            console.log("Audio autoplay blocked. Please enable via notification bell icon.");
        });
    } catch (error) {
        console.error('Notification sound error:', error);
    }
};

// Start repeating notification for pending orders
const startRepeatingNotification = () => {
    // Clear any existing interval
    if (notificationInterval.value) {
        clearInterval(notificationInterval.value);
    }

    // Only start if there are pending pickup orders (shipping orders don't repeat)
    const hasPendingPickupOrders = ordersByStatus.pending.some(order => order.fulfilment_type === 'pickup');

    if (hasPendingPickupOrders) {
        notificationInterval.value = setInterval(() => {
            // Check if there are still pending pickup orders
            const stillHasPendingPickup = ordersByStatus.pending.some(order => order.fulfilment_type === 'pickup');
            if (stillHasPendingPickup) {
                playNotificationSound('pickup');
            } else {
                stopRepeatingNotification();
            }
        }, 3000); // Repeat every 3 seconds
    }
};

// Stop repeating notification
const stopRepeatingNotification = () => {
    if (notificationInterval.value) {
        clearInterval(notificationInterval.value);
        notificationInterval.value = null;
    }
};

// Orders grouped by status
const ordersByStatus = reactive({
    pending: [],
    in_progress: [],
    ready: [],
    packing: [],
    shipped: [],
    delivered: [],
    ready_for_pickup: [],
    picked_up: [],
    cancelled: [],
});

// Helper function to filter orders by search query and active tab
const filterOrders = (orders) => {
    let filtered = orders;

    // Filter by active tab (fulfilment_type)
    filtered = filtered.filter(
        (order) => order.fulfilment_type === activeTab.value
    );

    // Filter by search query
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter((order) => {
            // Search in order details
            const matchesOrder =
                order.public_id?.toLowerCase().includes(query) ||
                order.customer_name?.toLowerCase().includes(query) ||
                order.customer_email?.toLowerCase().includes(query);

            // Search in order items (product names)
            const matchesProducts = order.items?.some(item =>
                item.product_name?.toLowerCase().includes(query) ||
                item.name?.toLowerCase().includes(query)
            );

            return matchesOrder || matchesProducts;
        });
    }

    return filtered;
};

// Computed properties
const filteredPendingOrders = computed(() =>
    filterOrders(ordersByStatus.pending)
);

const inProgressOrders = computed(() => {
    // Combine in_progress, ready, and packing orders
    const combined = [
        ...ordersByStatus.in_progress,
        ...ordersByStatus.ready,
        ...ordersByStatus.packing,
    ];
    return filterOrders(combined);
});

const filteredShippedOrders = computed(() =>
    filterOrders(ordersByStatus.shipped)
);
const filteredReadyForPickupOrders = computed(() =>
    filterOrders(ordersByStatus.ready_for_pickup)
);

const filteredCompletedOrders = computed(() => {
    const combined = [...ordersByStatus.delivered, ...ordersByStatus.picked_up];
    return filterOrders(combined);
});

// Pending counts for tab badges
const shippingPendingCount = computed(
    () =>
        ordersByStatus.pending.filter(
            (order) => order.fulfilment_type === "shipping"
        ).length
);

const pickupPendingCount = computed(
    () =>
        ordersByStatus.pending.filter(
            (order) => order.fulfilment_type === "pickup"
        ).length
);

// Initialize orders
const initializeOrders = () => {
    // Reset all status arrays
    Object.keys(ordersByStatus).forEach((status) => {
        ordersByStatus[status] = [];
    });

    // Group orders by status
    if (props.orders && props.orders.length > 0) {
        props.orders.forEach((order) => {
            if (ordersByStatus[order.status]) {
                ordersByStatus[order.status].push(order);
            }
        });
    }
};

// Modal handlers
const openOrderDetail = async (order) => {
    // Fetch full order details from backend to ensure we have the latest data (including refund status)
    try {
        const response = await fetch(route('orders.show', order.id), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Cache-Control': 'no-cache',
            },
        });
        const fullOrderData = await response.json();

        console.log('Dashboard - Order data received:', fullOrderData);
        console.log('Dashboard - Order items:', fullOrderData.items);
        if (fullOrderData.items && fullOrderData.items.length > 0) {
            console.log('Dashboard - First item refund status:', fullOrderData.items[0].is_refunded);
        }

        selectedOrder.value = fullOrderData;
        isModalOpen.value = true;
    } catch (error) {
        console.error('Failed to load order details:', error);
        // Fallback to using the order data we have
        selectedOrder.value = order;
        isModalOpen.value = true;
    }
};

const closeOrderDetail = () => {
    isModalOpen.value = false;
    setTimeout(() => {
        selectedOrder.value = null;
    }, 300);
};

const handleStatusUpdated = (newStatus) => {
    // Close modal after status update
    closeOrderDetail();

    // The order will be updated via WebSocket event
    // or we could manually update it here for immediate feedback
};

// Drag and drop handler
const handleOrderDrop = ({ orderId, fromStatus, toStatus }) => {
    // Find the order
    const order = ordersByStatus[fromStatus]?.find((o) => o.id === orderId);
    if (!order) {
        console.error("Order not found:", orderId);
        return;
    }

    // Store original order for potential rollback
    const originalOrder = { ...order };

    // Map "completed" to the appropriate final status based on fulfillment type
    let actualStatus = toStatus;
    if (toStatus === "completed") {
        actualStatus =
            order.fulfilment_type === "shipping" ? "delivered" : "picked_up";
    }

    // Optimistically update UI
    const orderIndex = ordersByStatus[fromStatus].findIndex(
        (o) => o.id === orderId
    );
    if (orderIndex !== -1) {
        ordersByStatus[fromStatus].splice(orderIndex, 1);
    }

    // Update order status and add to appropriate status array
    const updatedOrder = { ...order, status: actualStatus };

    // Add to the actual status array (delivered or picked_up)
    if (ordersByStatus[actualStatus]) {
        ordersByStatus[actualStatus].unshift(updatedOrder);
    }

    // Make API call to update the order status using Inertia
    router.put(
        route("orders.status.update", { order: orderId }),
        { status: actualStatus },
        {
            preserveScroll: true,
            preserveState: true,
            only: [], // Don't reload any props
            onError: (errors) => {
                // Revert on failure - remove from the actual status array
                const newIndex = ordersByStatus[actualStatus]?.findIndex(
                    (o) => o.id === orderId
                );
                if (newIndex !== -1) {
                    ordersByStatus[actualStatus].splice(newIndex, 1);
                }
                ordersByStatus[fromStatus].push(originalOrder);

                // Get the error message
                const errorMessage =
                    errors.status ||
                    Object.values(errors)[0] ||
                    "Failed to update order status";

                // Show toast notification
                toast.error(errorMessage);
                console.error("Status update error:", errors);
            },
            onSuccess: (page) => {
                // Success is silent - real-time updates will handle UI changes
                // Only show errors to avoid notification overload

                // Auto-print receipt when status changes to in_progress (pickup orders only)
                if (actualStatus === 'in_progress' && order.fulfilment_type === 'pickup') {
                    setTimeout(() => printReceipt(orderId), 500);
                }
            },
        }
    );
};

// Print receipt function
const printReceipt = (orderId) => {
    const receiptUrl = route('orders.receipt', orderId);
    const printWindow = window.open(receiptUrl, '_blank', 'width=800,height=600');

    if (!printWindow || printWindow.closed || typeof printWindow.closed === 'undefined') {
        console.warn('Print window was blocked. Please allow pop-ups.');
    }
};

// Polling setup
let pollingInterval = null;

const startPolling = () => {
    if (!props.store) return;

    // Poll every 5 seconds for updates
    pollingInterval = setInterval(async () => {
        try {
            const response = await fetch(
                route("dashboard.orders.poll", { store: props.store.id }),
                {
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        Accept: "application/json",
                    },
                }
            );

            if (response.ok) {
                const data = await response.json();

                // Update orders if data has changed
                if (data.orders) {
                    // Reset all status arrays
                    Object.keys(ordersByStatus).forEach((status) => {
                        ordersByStatus[status] = [];
                    });

                    // Re-populate with updated orders
                    data.orders.forEach((order) => {
                        if (ordersByStatus[order.status]) {
                            ordersByStatus[order.status].push(order);
                        }
                    });

                    // Update selected order if it's open in modal
                    if (selectedOrder.value) {
                        const updatedOrder = data.orders.find(
                            (o) => o.id === selectedOrder.value.id
                        );
                        if (updatedOrder) {
                            selectedOrder.value = updatedOrder;
                        }
                    }
                }

                isConnected.value = true;
            } else {
                isConnected.value = false;
            }
        } catch (error) {
            console.error("Polling error:", error);
            isConnected.value = false;
        }
    }, 5000);
};

const stopPolling = () => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
};

// WebSocket setup (for future upgrade)
let storeChannel = null;

const setupWebSocket = () => {
    if (!props.store) return;

    if (!window.Echo) {
        startPolling();
        return;
    }

    try {
        const channelName = `store.${props.store.id}.orders`;

        // Subscribe to store orders channel and listen for events
        storeChannel = window.Echo.private(channelName)
            .listen(".OrderCreated", (event) => {
                // Add order first to ensure it's in the pending array
                addOrUpdateOrder(event.order);
                // Play sound immediately for new orders (different sound for shipping vs pickup)
                playNotificationSound(event.order.fulfilment_type);
                // Start repeating notification for pending orders
                startRepeatingNotification();
            })
            .listen(".OrderStatusUpdated", (event) => {
                updateOrderStatus(
                    event.order,
                    event.old_status,
                    event.new_status
                );
            })
            .listen(".ShippingStatusUpdated", (event) => {
                addOrUpdateOrder(event.order);
            });

        // Add subscription error handler
        storeChannel.subscription.bind('pusher:subscription_error', (status) => {
            console.error("Failed to subscribe to order updates:", status.status);
        });

        // Connection status handlers
        window.Echo.connector.pusher.connection.bind("connected", () => {
            isConnected.value = true;
        });

        window.Echo.connector.pusher.connection.bind("disconnected", () => {
            isConnected.value = false;
        });

        window.Echo.connector.pusher.connection.bind("error", (err) => {
            isConnected.value = false;
            console.error("WebSocket connection error:", err);
        });

        // Set initial connection state
        isConnected.value =
            window.Echo.connector.pusher.connection.state === "connected";
    } catch (error) {
        console.error(
            "WebSocket setup failed, falling back to polling:",
            error
        );
        startPolling();
    }
};

const addOrUpdateOrder = (order) => {
    // Remove order from all status arrays
    Object.keys(ordersByStatus).forEach((status) => {
        const index = ordersByStatus[status].findIndex(
            (o) => o.id === order.id
        );
        if (index !== -1) {
            ordersByStatus[status].splice(index, 1);
        }
    });

    // Add order to appropriate status array
    if (ordersByStatus[order.status]) {
        // Check if order already exists
        const exists = ordersByStatus[order.status].some(
            (o) => o.id === order.id
        );
        if (!exists) {
            ordersByStatus[order.status].unshift(order); // Add to beginning
        } else {
            // Update existing order
            const index = ordersByStatus[order.status].findIndex(
                (o) => o.id === order.id
            );
            ordersByStatus[order.status][index] = order;
        }
    }

    // Update selected order if it's open in modal
    if (selectedOrder.value && selectedOrder.value.id === order.id) {
        selectedOrder.value = order;
    }
};

const updateOrderStatus = (order, oldStatus, newStatus) => {
    // Remove from old status
    if (ordersByStatus[oldStatus]) {
        const index = ordersByStatus[oldStatus].findIndex(
            (o) => o.id === order.id
        );
        if (index !== -1) {
            ordersByStatus[oldStatus].splice(index, 1);
        }
    }

    // Add to new status (only if not already there to prevent duplicates)
    if (ordersByStatus[newStatus]) {
        const exists = ordersByStatus[newStatus].some(
            (o) => o.id === order.id
        );
        if (!exists) {
            ordersByStatus[newStatus].unshift(order);
        } else {
            // Update existing order data
            const index = ordersByStatus[newStatus].findIndex(
                (o) => o.id === order.id
            );
            ordersByStatus[newStatus][index] = order;
        }
    }

    // Stop repeating notification if no more pending orders
    if (oldStatus === 'pending' && ordersByStatus.pending.length === 0) {
        stopRepeatingNotification();
    }

    // Update selected order if it's open in modal
    if (selectedOrder.value && selectedOrder.value.id === order.id) {
        selectedOrder.value = order;
    }
};

const cleanupWebSocket = () => {
    stopPolling();
    if (storeChannel && props.store) {
        window.Echo.leave(`store.${props.store.id}.orders`);
    }
};

// Open full-screen display view in new window
const openFullScreenDisplay = () => {
    const displayUrl = route('dashboard.display');
    window.open(displayUrl, '_blank', 'width=1920,height=1080,menubar=no,toolbar=no,location=no,status=no');
};

// Lifecycle hooks
onMounted(() => {
    initializeOrders();
    setupWebSocket();

    // Start countdown timer
    calculateTimeUntilClose();
    countdownInterval = setInterval(calculateTimeUntilClose, 1000);

    // Check if sound permission has been set (managed by layout's bell icon)
    const soundPref = localStorage.getItem('soundNotificationsEnabled');
    soundEnabled.value = soundPref === 'true';

    // Start repeating notification if there are already pending orders
    if (ordersByStatus.pending.length > 0) {
        startRepeatingNotification();
    }
});

onUnmounted(() => {
    cleanupWebSocket();
    stopRepeatingNotification();

    // Clear countdown interval
    if (countdownInterval) {
        clearInterval(countdownInterval);
    }
});
</script>

<style scoped>
@keyframes pulse-subtle {
    0%,
    100% {
        opacity: 1;
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
    }
    50% {
        opacity: 0.8;
        box-shadow: 0 0 8px 3px rgba(34, 197, 94, 0.6);
    }
}

.animate-pulse-subtle {
    animation: pulse-subtle 1.5s ease-in-out infinite;
}

/* Search transition */
.search-enter-active,
.search-leave-active {
    transition: all 0.3s ease;
}

.search-enter-from {
    opacity: 0;
    transform: translateY(-10px);
}

.search-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
