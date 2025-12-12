<template>
    <div class="dark min-h-screen bg-gray-900 p-2">
        <!-- Sound Notification Permission Banner -->
        <div
            v-if="showSoundPermissionBanner"
            class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 max-w-md w-full mx-4"
        >
            <div class="bg-blue-600 text-white rounded-lg shadow-xl p-4 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" />
                    </svg>
                    <div>
                        <p class="font-medium">Enable Sound Notifications?</p>
                        <p class="text-sm text-blue-100">Get audio alerts for new orders</p>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <button
                        @click="enableSoundNotifications"
                        class="px-4 py-2 bg-white text-blue-600 rounded-lg font-medium hover:bg-blue-50 transition-colors"
                    >
                        Enable
                    </button>
                    <button
                        @click="dismissSoundPermission"
                        class="px-3 py-2 text-blue-100 hover:text-white transition-colors"
                    >
                        ✕
                    </button>
                </div>
            </div>
        </div>

        <!-- Display Columns - Optimized Layout -->
        <div class="grid gap-4" style="grid-template-columns: 1fr 1fr 1fr 0.6fr;">
            <!-- Pending Orders -->
            <DisplayColumn
                status="pending"
                title="Pending"
                :orders="filteredPendingOrders"
                @order-click="openOrderDetail"
                @order-drop="handleOrderDrop"
            />

            <!-- In Progress -->
            <DisplayColumn
                status="in_progress"
                title="In Progress"
                :orders="inProgressOrders"
                @order-click="openOrderDetail"
                @order-drop="handleOrderDrop"
            />

            <!-- Shipped or Ready for Pickup -->
            <DisplayColumn
                v-if="activeTab === 'shipping'"
                status="shipped"
                title="Shipped"
                :orders="filteredShippedOrders"
                @order-click="openOrderDetail"
                @order-drop="handleOrderDrop"
            />
            <DisplayColumn
                v-else
                status="ready_for_pickup"
                title="Ready for Pickup"
                :orders="filteredReadyForPickupOrders"
                @order-click="openOrderDetail"
                @order-drop="handleOrderDrop"
            />

            <!-- Completed -->
            <DisplayColumn
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
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from "vue";
import { router } from "@inertiajs/vue3";
import DisplayColumn from "@/Components/Operations/DisplayColumn.vue";
import OrderDetailModal from "@/Components/Operations/OrderDetailModal.vue";

// Remove the DashboardLayout - display view should be full-screen
defineOptions({
    layout: null
});

const props = defineProps({
    store: Object,
    orders: Array,
});

// Real-time connection state
const isConnected = ref(false);

// Active tab (shipping or pickup)
const activeTab = ref("pickup");

// Modal state
const isModalOpen = ref(false);
const selectedOrder = ref(null);

// Sound notification permission
const showSoundPermissionBanner = ref(false);
const soundEnabled = ref(false);

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

// Helper function to filter orders by active tab
const filterOrders = (orders) => {
    return orders.filter((order) => order.fulfilment_type === activeTab.value);
};

// Computed properties
const filteredPendingOrders = computed(() =>
    filterOrders(ordersByStatus.pending)
);

const inProgressOrders = computed(() => {
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
    Object.keys(ordersByStatus).forEach((status) => {
        ordersByStatus[status] = [];
    });

    if (props.orders && props.orders.length > 0) {
        props.orders.forEach((order) => {
            if (ordersByStatus[order.status]) {
                ordersByStatus[order.status].push(order);
            }
        });
    }
};

// Polling setup
let pollingInterval = null;

const startPolling = () => {
    if (!props.store) return;

    pollingInterval = setInterval(async () => {
        try {
            const response = await fetch(
                `/dashboard/orders/poll/${props.store.id}`,
                {
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        Accept: "application/json",
                    },
                }
            );

            if (response.ok) {
                const data = await response.json();

                if (data.orders) {
                    Object.keys(ordersByStatus).forEach((status) => {
                        ordersByStatus[status] = [];
                    });

                    data.orders.forEach((order) => {
                        if (ordersByStatus[order.status]) {
                            ordersByStatus[order.status].push(order);
                        }
                    });
                }

                isConnected.value = true;
            } else {
                isConnected.value = false;
            }
        } catch (error) {
            console.error("Polling error:", error);
            isConnected.value = false;
        }
    }, 3000); // Poll every 3 seconds for display view
};

const stopPolling = () => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
};

// WebSocket setup
let storeChannel = null;

const setupWebSocket = () => {
    if (!props.store) return;

    if (!window.Echo) {
        startPolling();
        return;
    }

    try {
        const channelName = `store.${props.store.id}.orders`;

        storeChannel = window.Echo.private(channelName)
            .listen(".OrderCreated", (event) => {
                // Play sound immediately for new orders
                playNotificationSound();
                addOrUpdateOrder(event.order);
            })
            .listen(".OrderStatusUpdated", (event) => {
                updateOrderStatus(event.order, event.old_status, event.new_status);
            })
            .listen(".ShippingStatusUpdated", (event) => {
                addOrUpdateOrder(event.order);
            });

        window.Echo.connector.pusher.connection.bind("connected", () => {
            isConnected.value = true;
        });

        window.Echo.connector.pusher.connection.bind("disconnected", () => {
            isConnected.value = false;
        });

        window.Echo.connector.pusher.connection.bind("error", () => {
            isConnected.value = false;
        });

        isConnected.value =
            window.Echo.connector.pusher.connection.state === "connected";
    } catch (error) {
        console.error("WebSocket setup failed, falling back to polling:", error);
        startPolling();
    }
};

const addOrUpdateOrder = (order) => {
    // Remove order from all status arrays first
    Object.keys(ordersByStatus).forEach((status) => {
        const index = ordersByStatus[status].findIndex((o) => o.id === order.id);
        if (index !== -1) {
            ordersByStatus[status].splice(index, 1);
        }
    });

    // Add to appropriate status array
    if (ordersByStatus[order.status]) {
        const exists = ordersByStatus[order.status].some((o) => o.id === order.id);
        if (!exists) {
            ordersByStatus[order.status].unshift(order);
        } else {
            const index = ordersByStatus[order.status].findIndex((o) => o.id === order.id);
            ordersByStatus[order.status][index] = order;
        }
    }
};

const updateOrderStatus = (order, oldStatus, newStatus) => {
    if (ordersByStatus[oldStatus]) {
        const index = ordersByStatus[oldStatus].findIndex((o) => o.id === order.id);
        if (index !== -1) {
            ordersByStatus[oldStatus].splice(index, 1);
        }
    }

    // Add to new status (only if not already there to prevent duplicates)
    if (ordersByStatus[newStatus]) {
        const exists = ordersByStatus[newStatus].some((o) => o.id === order.id);
        if (!exists) {
            ordersByStatus[newStatus].unshift(order);
        } else {
            // Update existing order data
            const index = ordersByStatus[newStatus].findIndex((o) => o.id === order.id);
            ordersByStatus[newStatus][index] = order;
        }
    }
};

const cleanupWebSocket = () => {
    stopPolling();
    if (storeChannel && props.store) {
        window.Echo.leave(`store.${props.store.id}.orders`);
    }
};

// Sound notification functions
const enableSoundNotifications = async () => {
    try {
        // Play a silent sound to unlock audio context
        const audio = new Audio('/sounds/notification.wav');
        audio.volume = 0.01;
        await audio.play();
        audio.pause();

        soundEnabled.value = true;
        localStorage.setItem('displaySoundNotificationsEnabled', 'true');
        showSoundPermissionBanner.value = false;
    } catch (error) {
        console.error('Failed to enable sound:', error);
    }
};

const dismissSoundPermission = () => {
    localStorage.setItem('displaySoundNotificationsEnabled', 'false');
    showSoundPermissionBanner.value = false;
};

const playNotificationSound = () => {
    // Only play if user has enabled sound
    if (!soundEnabled.value) return;

    try {
        const audio = new Audio('/sounds/notification.wav');
        audio.volume = 0.7;
        audio.play().catch(e => {
            // Browser blocked autoplay - show permission banner again
            if (e.name === 'NotAllowedError') {
                showSoundPermissionBanner.value = true;
            }
        });
    } catch (error) {
        console.error('Notification sound error:', error);
    }
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

    // Update the order in the local state if needed
    if (selectedOrder.value) {
        // Find and update the order
        Object.keys(ordersByStatus).forEach((status) => {
            const index = ordersByStatus[status].findIndex(
                (o) => o.id === selectedOrder.value.id
            );
            if (index !== -1) {
                const order = ordersByStatus[status][index];
                // Remove from current status
                ordersByStatus[status].splice(index, 1);
                // Add to new status
                order.status = newStatus;
                if (ordersByStatus[newStatus]) {
                    ordersByStatus[newStatus].unshift(order);
                }
            }
        });
    }
};

// Drag and drop handler with optimistic updates
const handleOrderDrop = async ({ orderId, fromStatus, toStatus }) => {
    // Find the order
    const order = ordersByStatus[fromStatus]?.find((o) => o.id === orderId);
    if (!order) {
        console.error("Order not found:", orderId);
        return;
    }

    // Store original order for potential rollback
    const originalOrder = { ...order };
    const originalStatus = fromStatus;

    // Map display statuses to actual database statuses
    const statusMap = {
        pending: "pending",
        in_progress: "in_progress",
        shipped: "shipped",
        ready_for_pickup: "ready_for_pickup",
        completed: "completed",
    };

    // Map "completed" to the appropriate final status based on fulfillment type
    let actualStatus = statusMap[toStatus] || toStatus;
    if (toStatus === "completed") {
        actualStatus = order.fulfilment_type === "shipping" ? "delivered" : "picked_up";
    }

    // OPTIMISTIC UPDATE: Update UI immediately
    const orderIndex = ordersByStatus[fromStatus].findIndex((o) => o.id === orderId);
    if (orderIndex !== -1) {
        ordersByStatus[fromStatus].splice(orderIndex, 1);
    }

    // Update order status and add to appropriate status array
    const updatedOrder = { ...order, status: actualStatus };

    // Add to the actual status array (delivered or picked_up for completed, or the mapped status)
    const targetStatus = actualStatus;
    if (ordersByStatus[targetStatus]) {
        ordersByStatus[targetStatus].unshift(updatedOrder);
    }

    // Make API call in background using Inertia (non-blocking)
    router.put(
        route("orders.status.update", { order: orderId }),
        { status: actualStatus },
        {
            preserveState: true,
            preserveScroll: true,
            async: true,
            onError: (errors) => {
                console.error("Failed to update order status:", errors);

                // ROLLBACK: Revert the optimistic update on error
                const newIndex = ordersByStatus[targetStatus]?.findIndex((o) => o.id === orderId);
                if (newIndex !== -1) {
                    ordersByStatus[targetStatus].splice(newIndex, 1);
                }
                ordersByStatus[originalStatus].push(originalOrder);

                // Show error notification
                alert("Failed to update order status. Please try again.");
            },
            onSuccess: () => {
                // Auto-print receipt when status changes to in_progress
                if (actualStatus === "in_progress") {
                    setTimeout(() => printReceipt(orderId), 500);
                }
            },
        }
    );
};

// Print receipt function
const printReceipt = (orderId) => {
    const receiptUrl = `/orders/${orderId}/receipt`;
    const printWindow = window.open(receiptUrl, "_blank", "width=800,height=600");

    if (!printWindow || printWindow.closed || typeof printWindow.closed === "undefined") {
        console.warn("Print window was blocked. Please allow pop-ups.");
    }
};

// Lifecycle hooks
onMounted(() => {
    initializeOrders();
    setupWebSocket();

    // Check if sound permission has been set
    const soundPref = localStorage.getItem('displaySoundNotificationsEnabled');
    if (soundPref === null) {
        // Show banner if not set
        showSoundPermissionBanner.value = true;
    } else {
        soundEnabled.value = soundPref === 'true';
    }
});

onUnmounted(() => {
    cleanupWebSocket();
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
</style>
