<template>
    <div
        class="bg-gray-800 rounded-xl p-4 flex flex-col h-[calc(100vh-16px)]"
        :data-column-status="status"
    >
        <!-- Column Header -->
        <div class="mb-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded-full" :class="statusColor"></div>
                <h2 class="text-xl font-bold text-white">{{ title }}</h2>
            </div>
            <div
                class="inline-flex items-center justify-center min-w-[32px] h-8 px-2 rounded-lg text-sm font-bold"
                :class="statusBadgeColor"
            >
                {{ orders.length }}
            </div>
        </div>

        <!-- Orders List -->
        <div
            class="flex-1 overflow-y-auto space-y-2 scrollbar-thin scrollbar-thumb-gray-600 scrollbar-track-gray-700"
            @dragenter="handleDragEnter"
            @dragover.prevent="handleDragOver"
            @dragleave="handleDragLeave"
            @drop.prevent="handleDrop"
            :class="{ 'bg-blue-500/10 border-2 border-blue-500 border-dashed rounded-lg': isDropTarget }"
        >
            <div
                v-for="order in orders"
                :key="order.id"
                draggable="true"
                @dragstart="handleDragStart(order, $event)"
                @dragend="handleDragEnd"
                @touchstart="handleTouchStart(order, $event)"
                @touchmove="handleTouchMove(order, $event)"
                @touchend="handleTouchEnd(order, $event)"
                @touchcancel="handleTouchCancel"
                @click="$emit('order-click', order)"
                class="bg-white dark:bg-gray-700 rounded-lg shadow-sm border border-gray-200 dark:border-gray-600 p-3 cursor-move hover:shadow-md transition-shadow min-h-[100px] flex flex-col justify-between select-none"
                :class="{ 'opacity-50': isDragging && draggingOrderId === order.id }"
            >
                <!-- Header Row -->
                <div class="flex justify-between items-start">
                    <div class="flex-1 min-w-0">
                        <div class="font-bold text-base text-gray-900 dark:text-white truncate">
                            {{ order.customer_name }} <span class="text-xs text-gray-500 dark:text-gray-400">{{ order.public_id }}</span>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 space-y-0.5">
                            <div v-for="item in order.items" :key="item.id" class="truncate">
                                {{ item.quantity }}x {{ item.product_name || item.name }}
                                <span v-if="item.addons && item.addons.length > 0" class="text-gray-400">
                                    ({{ item.addons.map(a => a.name).join(', ') }})
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Middle Row -->
                <div class="flex justify-between items-center text-sm">
                    <div class="flex items-center gap-3">
                        <span class="font-semibold text-gray-900 dark:text-white">
                            {{ formatCurrency(order.total_cents) }}
                        </span>
                    </div>
                </div>

                <!-- Bottom Row -->
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-blue-600 dark:text-blue-400">
                        {{ formatTime(order.created_at) }}
                    </span>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-if="orders.length === 0"
                class="flex flex-col items-center justify-center h-full text-gray-500"
            >
                <svg class="w-16 h-16 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-lg font-medium">No orders</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";

const props = defineProps({
    status: String,
    title: String,
    orders: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['order-click', 'order-drop']);

// Drag and drop state
const isDropTarget = ref(false);
const isDragging = ref(false);
const draggingOrderId = ref(null);
let dragCounter = 0;

// Touch drag state
const touchStartY = ref(0);
const touchStartX = ref(0);
const touchDragThreshold = 5; // pixels to move before starting drag
const dragGhost = ref(null);

// Drag handlers
const handleDragStart = (order, e) => {
    isDragging.value = true;
    draggingOrderId.value = order.id;
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('application/json', JSON.stringify({
        orderId: order.id,
        currentStatus: props.status
    }));
};

const handleDragEnd = () => {
    isDragging.value = false;
    draggingOrderId.value = null;
    isDropTarget.value = false;
    dragCounter = 0;
};

const handleDragEnter = (e) => {
    dragCounter++;
    isDropTarget.value = true;
};

const handleDragOver = (e) => {
    // Just prevent default to allow drop - don't increment counter
    isDropTarget.value = true;
};

const handleDragLeave = (e) => {
    dragCounter--;
    if (dragCounter === 0) {
        isDropTarget.value = false;
    }
};

const handleDrop = (e) => {
    dragCounter = 0;
    isDropTarget.value = false;

    try {
        const data = JSON.parse(e.dataTransfer.getData('application/json'));

        // Don't allow dropping in the same column
        if (data.currentStatus === props.status) {
            return;
        }

        emit('order-drop', {
            orderId: data.orderId,
            fromStatus: data.currentStatus,
            toStatus: props.status,
        });
    } catch (error) {
        console.error('Error handling drop:', error);
    }
};

// Touch handlers for tablets/mobile
let touchedOrder = null;
let touchStartStatus = null;

const handleTouchStart = (order, e) => {
    touchedOrder = order;
    touchStartStatus = props.status;
    const touch = e.touches[0];
    touchStartX.value = touch.clientX;
    touchStartY.value = touch.clientY;

    // Store reference to the element for cloning later
    const target = e.currentTarget;
    target.dataset.orderElement = 'true';
};

const handleTouchMove = (order, e) => {
    if (!touchedOrder) return;

    const touch = e.touches[0];
    const deltaX = Math.abs(touch.clientX - touchStartX.value);
    const deltaY = Math.abs(touch.clientY - touchStartY.value);

    // Start visual drag feedback once threshold is met
    if (deltaX > touchDragThreshold || deltaY > touchDragThreshold) {
        isDragging.value = true;
        draggingOrderId.value = order.id;

        // Prevent scrolling while dragging
        e.preventDefault();

        // Create ghost element if it doesn't exist
        if (!dragGhost.value) {
            const sourceElement = e.currentTarget;
            const ghost = sourceElement.cloneNode(true);
            ghost.classList.add('drag-ghost');
            ghost.style.position = 'fixed';
            ghost.style.pointerEvents = 'none';
            ghost.style.zIndex = '9999';
            ghost.style.width = sourceElement.offsetWidth + 'px';
            ghost.style.height = sourceElement.offsetHeight + 'px';
            ghost.style.opacity = '0.9';
            ghost.style.transform = 'scale(1.05) rotate(2deg)';
            ghost.style.transition = 'transform 0.1s ease-out';
            ghost.style.boxShadow = '0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2)';
            document.body.appendChild(ghost);
            dragGhost.value = ghost;
        }

        // Update ghost position to follow finger
        if (dragGhost.value) {
            const offsetX = dragGhost.value.offsetWidth / 2;
            const offsetY = dragGhost.value.offsetHeight / 2;
            dragGhost.value.style.left = (touch.clientX - offsetX) + 'px';
            dragGhost.value.style.top = (touch.clientY - offsetY) + 'px';
        }

        // Find which column we're over
        const element = document.elementFromPoint(touch.clientX, touch.clientY);
        const column = element?.closest('[data-column-status]');

        // Clear all drop targets first
        document.querySelectorAll('[data-column-status]').forEach(col => {
            col.classList.remove('drop-target-active');
        });

        // Highlight the column we're over
        if (column && column.dataset.columnStatus !== props.status) {
            column.classList.add('drop-target-active');
        }
    }
};

const handleTouchEnd = (order, e) => {
    if (!touchedOrder) return;

    const touch = e.changedTouches[0];
    const element = document.elementFromPoint(touch.clientX, touch.clientY);
    const column = element?.closest('[data-column-status]');

    // Animate ghost to target before removing
    if (dragGhost.value) {
        dragGhost.value.style.transition = 'all 0.2s ease-out';
        dragGhost.value.style.opacity = '0';
        dragGhost.value.style.transform = 'scale(0.8) rotate(0deg)';

        setTimeout(() => {
            if (dragGhost.value) {
                dragGhost.value.remove();
                dragGhost.value = null;
            }
        }, 200);
    }

    // Clear visual states
    isDragging.value = false;
    draggingOrderId.value = null;
    document.querySelectorAll('[data-column-status]').forEach(col => {
        col.classList.remove('drop-target-active');
    });

    // If dropped on a different column, emit the drop event
    if (column && column.dataset.columnStatus !== touchStartStatus) {
        emit('order-drop', {
            orderId: touchedOrder.id,
            fromStatus: touchStartStatus,
            toStatus: column.dataset.columnStatus,
        });
    }

    touchedOrder = null;
    touchStartStatus = null;
};

const handleTouchCancel = () => {
    // Remove ghost element
    if (dragGhost.value) {
        dragGhost.value.remove();
        dragGhost.value = null;
    }

    isDragging.value = false;
    draggingOrderId.value = null;
    touchedOrder = null;
    touchStartStatus = null;
    document.querySelectorAll('[data-column-status]').forEach(col => {
        col.classList.remove('drop-target-active');
    });
};

const statusColor = computed(() => {
    const colors = {
        pending: "bg-yellow-500",
        in_progress: "bg-blue-500",
        shipped: "bg-teal-500",
        ready_for_pickup: "bg-green-500",
        completed: "bg-gray-500",
    };
    return colors[props.status] || "bg-gray-500";
});

const statusBadgeColor = computed(() => {
    const colors = {
        pending: "bg-yellow-500/20 text-yellow-400",
        in_progress: "bg-blue-500/20 text-blue-400",
        shipped: "bg-teal-500/20 text-teal-400",
        ready_for_pickup: "bg-green-500/20 text-green-400",
        completed: "bg-gray-500/20 text-gray-400",
    };
    return colors[props.status] || "bg-gray-500/20 text-gray-400";
});

const getPaymentStatusClass = (paymentStatus) => {
    switch (paymentStatus) {
        case "paid":
            return "bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200";
        case "unpaid":
            return "bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200";
        case "refunded":
            return "bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200";
        default:
            return "bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200";
    }
};

const formatCurrency = (cents) => {
    return new Intl.NumberFormat("en-AU", {
        style: "currency",
        currency: "AUD",
    }).format(cents / 100);
};

const formatTime = (datetime) => {
    const date = new Date(datetime);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);

    if (diffMins < 1) return "Just now";
    if (diffMins < 60) return `${diffMins}m ago`;

    const diffHours = Math.floor(diffMins / 60);
    if (diffHours < 24) return `${diffHours}h ago`;

    return date.toLocaleDateString("en-US", { month: "short", day: "numeric" });
};
</script>

<style scoped>
/* Custom scrollbar */
.scrollbar-thin::-webkit-scrollbar {
    width: 8px;
}

.scrollbar-thumb-gray-600::-webkit-scrollbar-thumb {
    background-color: #4b5563;
    border-radius: 4px;
}

.scrollbar-track-gray-700::-webkit-scrollbar-track {
    background-color: #374151;
}

.bg-gray-650 {
    background-color: #4a5568;
}

/* Touch drag drop target */
:deep(.drop-target-active) {
    background-color: rgba(59, 130, 246, 0.1) !important;
    border: 2px dashed #3b82f6 !important;
    border-radius: 0.75rem;
    transition: all 0.2s ease-out;
}

/* Drag ghost animations */
:global(.drag-ghost) {
    animation: ghostAppear 0.15s ease-out;
    cursor: grabbing !important;
}

@keyframes ghostAppear {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 0.9;
        transform: scale(1.05) rotate(2deg);
    }
}
</style>
