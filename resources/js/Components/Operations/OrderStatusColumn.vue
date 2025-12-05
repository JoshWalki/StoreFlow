<template>
    <div class="min-w-[280px] sm:min-w-[320px] w-full">
        <!-- Column Header -->
        <div class="bg-white dark:bg-gray-800 rounded-t-lg px-4 py-3 border-b-2 border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <div
                        class="w-3 h-3 rounded-full"
                        :class="statusColor"
                    ></div>
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ title }}</h3>
                    <span class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-medium px-2 py-1 rounded-full">
                        {{ orders.length }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Orders List -->
        <div
            class="bg-white dark:bg-gray-800 rounded-b-lg p-3 space-y-3 min-h-[calc(100vh-350px)] max-h-[calc(100vh-350px)] overflow-y-auto transition-colors shadow-sm border border-t-0 border-gray-200 dark:border-gray-700"
            :class="{ 'bg-blue-50 dark:bg-blue-900/30 ring-2 ring-blue-400 dark:ring-blue-500 border-blue-400 dark:border-blue-500': isDropTarget }"
            @dragover.prevent="handleDragOver"
            @dragenter.prevent="handleDragEnter"
            @dragleave.prevent="handleDragLeave"
            @drop.prevent="handleDrop"
        >
            <OrderCard
                v-for="order in orders"
                :key="order.id"
                :order="order"
                @click="$emit('order-click', order)"
            />

            <!-- Empty State -->
            <div
                v-if="orders.length === 0"
                class="flex flex-col items-center justify-center py-12 text-gray-400 dark:text-gray-500"
            >
                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-sm">No orders</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import OrderCard from './OrderCard.vue';

const props = defineProps({
    status: {
        type: String,
        required: true,
    },
    title: {
        type: String,
        required: true,
    },
    orders: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['order-click', 'order-drop']);

const isDropTarget = ref(false);
const dragCounter = ref(0);

const handleDragOver = (e) => {
    e.dataTransfer.dropEffect = 'move';
};

const handleDragEnter = (e) => {
    dragCounter.value++;
    isDropTarget.value = true;
};

const handleDragLeave = (e) => {
    dragCounter.value--;
    if (dragCounter.value === 0) {
        isDropTarget.value = false;
    }
};

const handleDrop = (e) => {
    dragCounter.value = 0;
    isDropTarget.value = false;

    try {
        const data = JSON.parse(e.dataTransfer.getData('application/json'));

        // Don't do anything if dropping in the same column
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

const statusColor = computed(() => {
    const colors = {
        pending: 'bg-yellow-400',
        accepted: 'bg-blue-400',
        in_progress: 'bg-purple-400',
        ready: 'bg-green-400',
        packing: 'bg-indigo-400',
        shipped: 'bg-teal-400',
        ready_for_pickup: 'bg-green-400',
        delivered: 'bg-gray-400',
        picked_up: 'bg-gray-400',
        cancelled: 'bg-red-400',
    };
    return colors[props.status] || 'bg-gray-400';
});
</script>
