<template>
    <div
        draggable="true"
        class="bg-white dark:bg-gray-700 rounded-lg shadow-sm border border-gray-200 dark:border-gray-600 p-3 cursor-grab active:cursor-grabbing hover:shadow-md transition-shadow min-h-[100px] flex flex-col justify-between"
        :class="{ 'opacity-50': isDragging }"
        @click="handleClick"
        @dragstart="handleDragStart"
        @dragend="handleDragEnd"
    >
        <!-- Header Row -->
        <div class="flex justify-between items-start">
            <div class="flex-1 min-w-0">
                <div class="font-bold text-base text-gray-900 dark:text-white truncate">
                    {{ order.customer_name }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    {{ order.public_id }}
                </div>
            </div>
            <div class="ml-2 flex-shrink-0">
                <span
                    v-if="order.fulfilment_type === 'pickup'"
                    class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200"
                >
                    Pickup
                </span>
                <span
                    v-else
                    class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200"
                >
                    Ship
                </span>
            </div>
        </div>

        <!-- Middle Row -->
        <div class="flex justify-between items-center text-sm">
            <div class="flex items-center gap-3">
                <span class="font-semibold text-gray-900 dark:text-white">
                    {{ formatCurrency(order.total_cents) }}
                </span>
                <span class="text-gray-600 dark:text-gray-400">
                    {{ order.items_count }} {{ order.items_count === 1 ? 'item' : 'items' }}
                </span>
            </div>
        </div>

        <!-- Order Items with Addons -->
        <div v-if="order.items && order.items.length > 0" class="mt-2 space-y-1 text-xs border-t border-gray-200 dark:border-gray-600 pt-2">
            <div v-for="item in order.items" :key="item.id" class="text-gray-700 dark:text-gray-300">
                <div class="flex justify-between">
                    <span>{{ item.quantity }}x {{ item.product_name }}</span>
                    <span class="text-gray-500 dark:text-gray-400">{{ formatCurrency(item.total_cents) }}</span>
                </div>
                <!-- Addons -->
                <div v-if="item.addons && item.addons.length > 0" class="ml-3 mt-0.5 space-y-0.5">
                    <div v-for="(addon, addonIdx) in item.addons" :key="addonIdx" class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span class="flex items-center gap-1">
                            <span class="text-gray-400">+</span>
                            {{ addon.name }}
                            <span v-if="addon.quantity > 1" class="text-gray-500">(x{{ addon.quantity }})</span>
                        </span>
                        <span v-if="addon.total_price_cents > 0" class="text-gray-500 dark:text-gray-500">
                            +{{ formatCurrency(addon.total_price_cents) }}
                        </span>
                    </div>
                </div>
                <!-- Special Instructions -->
                <div v-if="item.special_instructions" class="ml-3 mt-1 text-xs italic text-blue-600 dark:text-blue-400">
                    Note: {{ item.special_instructions }}
                </div>
            </div>
        </div>

        <!-- Bottom Row -->
        <div class="flex justify-between items-center mt-2">
            <span class="text-sm font-medium text-blue-600 dark:text-blue-400">
                {{ formatTime(order.created_at) }}
            </span>
            <span
                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
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
