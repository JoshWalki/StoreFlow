<template>
    <div
        draggable="true"
        class="bg-white dark:bg-gray-700 rounded-lg shadow-sm border border-gray-200 dark:border-gray-600 p-4 cursor-grab active:cursor-grabbing hover:shadow-md transition-shadow"
        :class="{ 'opacity-50': isDragging }"
        @click="handleClick"
        @dragstart="handleDragStart"
        @dragend="handleDragEnd"
    >
        <!-- Order Header -->
        <div class="flex justify-between items-start mb-3">
            <div>
                <div class="font-semibold text-gray-900 dark:text-white">
                    {{ order.public_id }}
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-300">
                    {{ order.customer_name }}
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <span
                    v-if="order.fulfilment_type === 'pickup'"
                    class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200"
                >
                    <svg
                        class="w-3 h-3 mr-1"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                        />
                    </svg>
                    Pickup
                </span>
                <span
                    v-else
                    class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200"
                >
                    <svg
                        class="w-3 h-3 mr-1"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"
                        />
                    </svg>
                    Shipping
                </span>
            </div>
        </div>

        <!-- Order Details -->
        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Total:</span>
                <span class="font-medium text-gray-900 dark:text-white">{{
                    formatCurrency(order.total_cents)
                }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Items:</span>
                <span class="text-gray-900 dark:text-white">{{
                    order.items_count
                }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Time:</span>
                <span class="text-gray-900 dark:text-white">{{
                    formatTime(order.created_at)
                }}</span>
            </div>
        </div>

        <!-- Payment Status Badge -->
        <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-600">
            <span
                class="inline-flex items-center px-2 py-1 rounded text-xs font-medium"
                :class="paymentStatusClass"
            >
                {{ order.payment_status }}
            </span>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from "vue";

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["click", "drag-start", "drag-end"]);

const isDragging = ref(false);

const handleClick = (e) => {
    // Only emit click if we're not dragging
    if (!isDragging.value) {
        emit("click", props.order);
    }
};

const handleDragStart = (e) => {
    isDragging.value = true;
    e.dataTransfer.effectAllowed = "move";
    e.dataTransfer.setData(
        "application/json",
        JSON.stringify({
            orderId: props.order.id,
            currentStatus: props.order.status,
        })
    );

    // Add a slight delay to show dragging state
    setTimeout(() => {
        e.target.classList.add("dragging");
    }, 0);

    emit("drag-start", props.order);
};

const handleDragEnd = (e) => {
    isDragging.value = false;
    e.target.classList.remove("dragging");
    emit("drag-end", props.order);
};

const paymentStatusClass = computed(() => {
    switch (props.order.payment_status) {
        case "paid":
            return "bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200";
        case "unpaid":
            return "bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200";
        case "refunded":
            return "bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200";
        default:
            return "bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200";
    }
});

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
