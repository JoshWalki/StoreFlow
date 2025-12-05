<template>
    <DashboardLayout :store="store" :user="user">
        <!-- Full-width container with custom padding -->
        <div class="-m-8 min-h-screen bg-gray-50 dark:bg-gray-900">
            <div class="px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 gap-4">
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Operations Dashboard</h1>

                    <!-- Real-time Connection Indicator -->
                    <div class="flex items-center space-x-2">
                        <div
                            class="w-2 h-2 rounded-full"
                            :class="[
                                !store.is_active ? 'bg-red-500' : (isConnected ? 'bg-green-500 animate-pulse-subtle' : 'bg-red-500')
                            ]"
                        ></div>
                        <span class="text-sm" :class="!store.is_active ? 'text-red-600 font-semibold' : 'text-gray-600'">
                            {{ !store.is_active ? 'Store Offline' : (isConnected ? 'Live' : 'Disconnected') }}
                        </span>
                    </div>
                </div>

                <!-- Tabs for Pickup/Shipping -->
                <div class="flex space-x-2 mb-6">
                    <button
                        @click="activeTab = 'shipping'"
                        class="flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-colors"
                        :class="activeTab === 'shipping' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600'"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                        </svg>
                        <span>Shipping</span>
                        <span v-if="shippingPendingCount > 0" class="inline-flex items-center justify-center w-6 h-6 text-xs font-bold rounded-full"
                              :class="activeTab === 'shipping' ? 'bg-white dark:bg-gray-800 text-blue-600 dark:text-blue-400' : 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-200'">
                            {{ shippingPendingCount }}
                        </span>
                    </button>
                    <button
                        @click="activeTab = 'pickup'"
                        class="flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-colors"
                        :class="activeTab === 'pickup' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600'"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <span>Pickup</span>
                        <span v-if="pickupPendingCount > 0" class="inline-flex items-center justify-center w-6 h-6 text-xs font-bold rounded-full"
                              :class="activeTab === 'pickup' ? 'bg-white dark:bg-gray-800 text-blue-600 dark:text-blue-400' : 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-200'">
                            {{ pickupPendingCount }}
                        </span>
                    </button>
                </div>

                <!-- Search Box -->
                <div class="mb-6">
                    <div class="relative max-w-2xl">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search by order ID, customer name, or email..."
                            class="w-full px-4 py-2 pl-10 pr-4 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <button
                            v-if="searchQuery"
                            @click="searchQuery = ''"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Kanban Board -->
                <div class="grid gap-4 pb-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
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
                    @close="closeOrderDetail"
                    @status-updated="handleStatusUpdated"
                />
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import OrderStatusColumn from '@/Components/Operations/OrderStatusColumn.vue';
import OrderDetailModal from '@/Components/Operations/OrderDetailModal.vue';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    store: Object,
    user: Object,
    orders: Array,
});

const toast = useToast();

// Real-time connection state
const isConnected = ref(false);

// Search query
const searchQuery = ref('');

// Active tab (shipping or pickup)
const activeTab = ref('shipping');

// Modal state
const isModalOpen = ref(false);
const selectedOrder = ref(null);

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
    filtered = filtered.filter(order => order.fulfilment_type === activeTab.value);

    // Filter by search query
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(order => {
            return (
                order.public_id?.toLowerCase().includes(query) ||
                order.customer_name?.toLowerCase().includes(query) ||
                order.customer_email?.toLowerCase().includes(query)
            );
        });
    }

    return filtered;
};

// Computed properties
const filteredPendingOrders = computed(() => filterOrders(ordersByStatus.pending));

const inProgressOrders = computed(() => {
    // Combine in_progress, ready, and packing orders
    const combined = [
        ...ordersByStatus.in_progress,
        ...ordersByStatus.ready,
        ...ordersByStatus.packing
    ];
    return filterOrders(combined);
});

const filteredShippedOrders = computed(() => filterOrders(ordersByStatus.shipped));
const filteredReadyForPickupOrders = computed(() => filterOrders(ordersByStatus.ready_for_pickup));

const filteredCompletedOrders = computed(() => {
    const combined = [
        ...ordersByStatus.delivered,
        ...ordersByStatus.picked_up,
    ];
    return filterOrders(combined);
});

// Pending counts for tab badges
const shippingPendingCount = computed(() =>
    ordersByStatus.pending.filter(order => order.fulfilment_type === 'shipping').length
);

const pickupPendingCount = computed(() =>
    ordersByStatus.pending.filter(order => order.fulfilment_type === 'pickup').length
);

// Initialize orders
const initializeOrders = () => {
    // Reset all status arrays
    Object.keys(ordersByStatus).forEach(status => {
        ordersByStatus[status] = [];
    });

    // Group orders by status
    props.orders.forEach(order => {
        if (ordersByStatus[order.status]) {
            ordersByStatus[order.status].push(order);
        }
    });
};

// Modal handlers
const openOrderDetail = (order) => {
    selectedOrder.value = order;
    isModalOpen.value = true;
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
    const order = ordersByStatus[fromStatus]?.find(o => o.id === orderId);
    if (!order) {
        console.error('Order not found:', orderId);
        return;
    }

    // Store original order for potential rollback
    const originalOrder = { ...order };

    // Map "completed" to the appropriate final status based on fulfillment type
    let actualStatus = toStatus;
    if (toStatus === 'completed') {
        actualStatus = order.fulfilment_type === 'shipping' ? 'delivered' : 'picked_up';
    }

    // Optimistically update UI
    const orderIndex = ordersByStatus[fromStatus].findIndex(o => o.id === orderId);
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
        route('orders.status.update', { order: orderId }),
        { status: actualStatus },
        {
            preserveScroll: true,
            preserveState: true,
            only: [], // Don't reload any props
            onError: (errors) => {
                // Revert on failure - remove from the actual status array
                const newIndex = ordersByStatus[actualStatus]?.findIndex(o => o.id === orderId);
                if (newIndex !== -1) {
                    ordersByStatus[actualStatus].splice(newIndex, 1);
                }
                ordersByStatus[fromStatus].push(originalOrder);

                // Get the error message
                const errorMessage = errors.status || Object.values(errors)[0] || 'Failed to update order status';

                // Show toast notification
                toast.error(errorMessage);
                console.error('Status update error:', errors);
            },
            onSuccess: (page) => {
                // Show success toast
                toast.success('Order status updated successfully');

                // Show flash message if available
                if (page.props?.flash?.success) {
                    toast.success(page.props.flash.success);
                }
            },
        }
    );
};

// Polling setup
let pollingInterval = null;

const startPolling = () => {
    // Poll every 5 seconds for updates
    pollingInterval = setInterval(async () => {
        try {
            const response = await fetch(route('dashboard.orders.poll', { store: props.store.id }), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });

            if (response.ok) {
                const data = await response.json();

                // Update orders if data has changed
                if (data.orders) {
                    // Reset all status arrays
                    Object.keys(ordersByStatus).forEach(status => {
                        ordersByStatus[status] = [];
                    });

                    // Re-populate with updated orders
                    data.orders.forEach(order => {
                        if (ordersByStatus[order.status]) {
                            ordersByStatus[order.status].push(order);
                        }
                    });

                    // Update selected order if it's open in modal
                    if (selectedOrder.value) {
                        const updatedOrder = data.orders.find(o => o.id === selectedOrder.value.id);
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
            console.error('Polling error:', error);
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
    if (!window.Echo) {
        console.log('WebSocket not available, using polling instead');
        startPolling();
        return;
    }

    try {
        // Subscribe to store orders channel
        storeChannel = window.Echo.private(`store.${props.store.id}.orders`)
            .listen('OrderCreated', (event) => {
                console.log('New order received:', event.order);
                addOrUpdateOrder(event.order);
            })
            .listen('OrderStatusUpdated', (event) => {
                console.log('Order status updated:', event.order);
                updateOrderStatus(event.order, event.old_status, event.new_status);
            })
            .listen('ShippingStatusUpdated', (event) => {
                console.log('Shipping status updated:', event.order);
                addOrUpdateOrder(event.order);
            });

        // Connection status handlers
        window.Echo.connector.pusher.connection.bind('connected', () => {
            isConnected.value = true;
            console.log('WebSocket connected');
        });

        window.Echo.connector.pusher.connection.bind('disconnected', () => {
            isConnected.value = false;
            console.log('WebSocket disconnected');
        });

        window.Echo.connector.pusher.connection.bind('error', (err) => {
            isConnected.value = false;
            console.error('WebSocket error:', err);
        });

        // Set initial connection state
        isConnected.value = window.Echo.connector.pusher.connection.state === 'connected';
    } catch (error) {
        console.error('WebSocket setup failed, falling back to polling:', error);
        startPolling();
    }
};

const addOrUpdateOrder = (order) => {
    // Remove order from all status arrays
    Object.keys(ordersByStatus).forEach(status => {
        const index = ordersByStatus[status].findIndex(o => o.id === order.id);
        if (index !== -1) {
            ordersByStatus[status].splice(index, 1);
        }
    });

    // Add order to appropriate status array
    if (ordersByStatus[order.status]) {
        // Check if order already exists
        const exists = ordersByStatus[order.status].some(o => o.id === order.id);
        if (!exists) {
            ordersByStatus[order.status].unshift(order); // Add to beginning
        } else {
            // Update existing order
            const index = ordersByStatus[order.status].findIndex(o => o.id === order.id);
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
        const index = ordersByStatus[oldStatus].findIndex(o => o.id === order.id);
        if (index !== -1) {
            ordersByStatus[oldStatus].splice(index, 1);
        }
    }

    // Add to new status
    if (ordersByStatus[newStatus]) {
        ordersByStatus[newStatus].unshift(order);
    }

    // Update selected order if it's open in modal
    if (selectedOrder.value && selectedOrder.value.id === order.id) {
        selectedOrder.value = order;
    }
};

const cleanupWebSocket = () => {
    stopPolling();
    if (storeChannel) {
        window.Echo.leave(`store.${props.store.id}.orders`);
    }
};

// Lifecycle hooks
onMounted(() => {
    initializeOrders();
    setupWebSocket();
});

onUnmounted(() => {
    cleanupWebSocket();
});
</script>

<style scoped>
@keyframes pulse-subtle {
    0%, 100% {
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
</style>
