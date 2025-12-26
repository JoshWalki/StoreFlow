<template>
    <div class="dark min-h-screen bg-gray-900 p-2 overflow-x-hidden">
        <!-- Bell Notification Icon (Fixed Top Right) -->
        <div class="fixed top-4 right-4 z-50">
            <button
                @click="showBellDropdown = !showBellDropdown"
                class="relative p-2 text-gray-300 hover:text-white hover:bg-gray-700 rounded-md transition-colors bg-gray-800"
                title="Notification Settings"
            >
                <svg
                    class="w-5 h-5"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                >
                    <path
                        d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"
                    />
                </svg>
                <!-- Notification indicator dot when enabled -->
                <span
                    v-if="soundEnabled"
                    class="absolute top-1 right-1 w-2 h-2 bg-green-500 rounded-full"
                ></span>
            </button>

            <!-- Notification Settings Dropdown -->
            <Transition name="dropdown">
                <div
                    v-if="showBellDropdown"
                    @click.stop
                    class="absolute right-0 mt-2 w-72 bg-gray-800 rounded-lg shadow-xl border border-gray-700 z-50"
                >
                    <div class="p-4">
                        <h3 class="text-sm font-semibold text-white mb-3">
                            Sound Notifications
                        </h3>

                        <div class="flex items-center justify-between mb-3">
                            <div class="flex-1">
                                <p class="text-sm text-gray-300">
                                    Order Alerts
                                </p>
                                <p class="text-xs text-gray-400">
                                    Play sound for new orders
                                </p>
                            </div>

                            <!-- Toggle Switch -->
                            <button
                                @click="toggleSoundNotifications"
                                class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                :class="soundEnabled ? 'bg-green-600' : 'bg-gray-600'"
                            >
                                <span
                                    class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform"
                                    :class="soundEnabled ? 'translate-x-6' : 'translate-x-1'"
                                ></span>
                            </button>
                        </div>

                        <div class="pt-3 border-t border-gray-700">
                            <p class="text-xs text-gray-400">
                                <span v-if="soundEnabled" class="text-green-400 font-medium">
                                    ✓ Enabled
                                </span>
                                <span v-else class="text-gray-500">
                                    Disabled
                                </span>
                                - You will {{ soundEnabled ? '' : 'not' }} receive audio alerts when new orders arrive.
                            </p>
                        </div>
                    </div>
                </div>
            </Transition>
        </div>

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
        <div class="grid gap-2 md:gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4" style="lg:grid-template-columns: 1fr 1fr 1fr 0.6fr;">
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
            :default-pickup-minutes="store?.default_pickup_minutes || 30"
            @close="closeOrderDetail"
            @status-updated="handleStatusUpdated"
        />
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from "vue";
import { router } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";
import DisplayColumn from "@/Components/Operations/DisplayColumn.vue";
import OrderDetailModal from "@/Components/Operations/OrderDetailModal.vue";

const toast = useToast();

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
const notificationInterval = ref(null);

// Bell notification dropdown state
const showBellDropdown = ref(false);

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
                // Add order first to ensure it's in the pending array
                addOrUpdateOrder(event.order);
                // Play sound immediately for new orders (different sound for shipping vs pickup)
                playNotificationSound(event.order.fulfilment_type);
                // Start repeating notification for pending orders
                startRepeatingNotification();
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

    // Stop repeating notification if no more pending orders
    if (oldStatus === 'pending' && ordersByStatus.pending.length === 0) {
        stopRepeatingNotification();
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
        localStorage.setItem('soundNotificationsEnabled', 'true');
        showSoundPermissionBanner.value = false;
    } catch (error) {
        console.error('Failed to enable sound:', error);
    }
};

const dismissSoundPermission = () => {
    localStorage.setItem('soundNotificationsEnabled', 'false');
    showSoundPermissionBanner.value = false;
};

const toggleSoundNotifications = async () => {
    if (!soundEnabled.value) {
        // Enabling sound
        await enableSoundNotifications();
    } else {
        // Disabling sound
        soundEnabled.value = false;
        localStorage.setItem('soundNotificationsEnabled', 'false');
        // Note: Toast not available in DisplayView, using alert
        console.log('Sound notifications disabled');
    }
};

// Close bell dropdown when clicking outside
const handleClickOutside = (event) => {
    const bellButton = event.target.closest('button[title="Notification Settings"]');
    const bellDropdown = event.target.closest('.absolute.right-0.mt-2.w-72');

    if (!bellButton && !bellDropdown && showBellDropdown.value) {
        showBellDropdown.value = false;
    }
};

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
            // Browser blocked autoplay - show permission banner again
            if (e.name === 'NotAllowedError') {
                showSoundPermissionBanner.value = true;
            }
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

        console.log('DisplayView - Order data received:', fullOrderData);
        console.log('DisplayView - Order items:', fullOrderData.items);
        if (fullOrderData.items && fullOrderData.items.length > 0) {
            console.log('DisplayView - First item refund status:', fullOrderData.items[0].is_refunded);
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
                toast.error("Failed to update order status. Please try again.");
            },
            onSuccess: () => {
                // Auto-print receipt when status changes to in_progress (pickup orders only)
                if (actualStatus === "in_progress" && order.fulfilment_type === "pickup") {
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
    // Set PWA zoom to 50% for display view
    document.body.style.zoom = "50%";

    initializeOrders();
    setupWebSocket();

    // Check if sound permission has been set (synced with main dashboard)
    const soundPref = localStorage.getItem('soundNotificationsEnabled');
    if (soundPref === null) {
        // Show banner if not set
        showSoundPermissionBanner.value = true;
    } else {
        soundEnabled.value = soundPref === 'true';
    }

    // Start repeating notification if there are already pending orders
    if (ordersByStatus.pending.length > 0) {
        startRepeatingNotification();
    }

    // Add click outside listener for bell dropdown
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    // Reset zoom when leaving display view
    document.body.style.zoom = "100%";

    cleanupWebSocket();
    stopRepeatingNotification();

    // Remove click outside listener
    document.removeEventListener('click', handleClickOutside);
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

/* Dropdown animation for bell notifications */
.dropdown-enter-active,
.dropdown-leave-active {
    transition: all 0.2s ease;
}

.dropdown-enter-from {
    opacity: 0;
    transform: translateY(-8px) scale(0.95);
}

.dropdown-enter-to {
    opacity: 1;
    transform: translateY(0) scale(1);
}

.dropdown-leave-from {
    opacity: 1;
    transform: translateY(0) scale(1);
}

.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-8px) scale(0.95);
}
</style>
