<template>
    <Teleport to="body">
        <Transition name="modal">
            <div
                v-if="show"
                class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50 p-4"
                @click.self="closeModal"
                role="dialog"
                aria-modal="true"
                aria-labelledby="modal-title"
            >
                <div
                    class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden"
                    @click.stop
                >
                    <!-- Header -->
                    <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                        <div>
                            <h2 id="modal-title" class="text-2xl font-bold text-gray-900 dark:text-white">
                                Customer Orders
                            </h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400" v-if="customerData">
                                {{ customerData.name }} - {{ customerData.email }}
                            </p>
                        </div>
                        <button
                            @click="closeModal"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors"
                            aria-label="Close modal"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Loading State -->
                    <div v-if="loading" class="flex items-center justify-center p-12">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                    </div>

                    <!-- Error State -->
                    <div v-else-if="error" class="p-6">
                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                            <p class="text-red-800 dark:text-red-200">{{ error }}</p>
                        </div>
                    </div>

                    <!-- Orders List -->
                    <div v-else class="overflow-y-auto max-h-[calc(90vh-180px)]">
                        <div v-if="orders.length === 0" class="p-12 text-center text-gray-500 dark:text-gray-400">
                            No orders found for this customer.
                        </div>

                        <div v-else class="p-6 space-y-4">
                            <div
                                v-for="order in orders"
                                :key="order.id"
                                class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow"
                            >
                                <!-- Order Header -->
                                <div class="flex items-start justify-between mb-3">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                            Order #{{ order.public_id }}
                                        </h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ formatDate(order.created_at) }}
                                        </p>
                                    </div>
                                    <div class="flex flex-col items-end gap-2">
                                        <span :class="getStatusBadgeClass(order.status)" class="px-3 py-1 rounded-full text-xs font-medium">
                                            {{ formatStatus(order.status) }}
                                        </span>
                                        <span :class="getPaymentStatusClass(order.payment_status)" class="px-3 py-1 rounded-full text-xs font-medium">
                                            {{ formatPaymentStatus(order.payment_status) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Order Items -->
                                <div class="border-t border-gray-200 dark:border-gray-600 pt-3 mt-3">
                                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Items</h4>
                                    <div class="space-y-3">
                                        <div
                                            v-for="(item, idx) in order.items"
                                            :key="idx"
                                            class="space-y-1"
                                        >
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-900 dark:text-white font-medium">
                                                    {{ item.quantity }}x {{ item.name }}
                                                </span>
                                                <span class="text-gray-600 dark:text-gray-400">
                                                    {{ formatCurrency(item.total) }}
                                                </span>
                                            </div>
                                            <!-- Addons -->
                                            <div v-if="item.addons && item.addons.length > 0" class="ml-4 space-y-1">
                                                <div
                                                    v-for="(addon, addonIdx) in item.addons"
                                                    :key="addonIdx"
                                                    class="flex justify-between text-xs text-gray-600 dark:text-gray-400"
                                                >
                                                    <span class="flex items-center gap-1">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                        </svg>
                                                        {{ addon.quantity }}x {{ addon.name }}
                                                    </span>
                                                    <span>{{ formatCurrency(addon.total) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Order Total -->
                                <div class="border-t border-gray-200 dark:border-gray-600 pt-3 mt-3">
                                    <div class="flex justify-between items-center">
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path v-if="order.fulfilment_type === 'pickup'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                </svg>
                                                {{ order.fulfilment_type === 'pickup' ? 'Pickup' : 'Delivery' }}
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                Items: {{ formatCurrency(order.items_total) }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400" v-if="order.shipping_cost > 0">
                                                Shipping: {{ formatCurrency(order.shipping_cost) }}
                                            </div>
                                            <div class="text-lg font-bold text-gray-900 dark:text-white">
                                                Total: {{ formatCurrency(order.total) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-between p-6 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-750">
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            Total Orders: <span class="font-semibold">{{ orders.length }}</span>
                        </div>
                        <button
                            @click="closeModal"
                            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    customerId: {
        type: Number,
        default: null,
    },
});

const emit = defineEmits(['close']);

const loading = ref(false);
const error = ref(null);
const orders = ref([]);
const customerData = ref(null);

// Watch for customerId changes to fetch orders
watch(() => [props.show, props.customerId], async ([show, customerId]) => {
    if (show && customerId) {
        await fetchOrders(customerId);
    }
});

const fetchOrders = async (customerId) => {
    loading.value = true;
    error.value = null;
    orders.value = [];
    customerData.value = null;

    try {
        const response = await axios.get(`/api/customers/${customerId}/orders`);
        orders.value = response.data.orders;
        customerData.value = response.data.customer;
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to load customer orders. Please try again.';
        console.error('Error fetching customer orders:', err);
    } finally {
        loading.value = false;
    }
};

const closeModal = () => {
    emit('close');
};

// Handle escape key
const handleEscape = (e) => {
    if (e.key === 'Escape' && props.show) {
        closeModal();
    }
};

// Add/remove event listener
watch(() => props.show, (show) => {
    if (show) {
        document.addEventListener('keydown', handleEscape);
        document.body.style.overflow = 'hidden';
    } else {
        document.removeEventListener('keydown', handleEscape);
        document.body.style.overflow = '';
    }
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-AU', {
        style: 'currency',
        currency: 'AUD',
    }).format(amount);
};

const formatStatus = (status) => {
    return status.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const formatPaymentStatus = (status) => {
    return status.charAt(0).toUpperCase() + status.slice(1);
};

const getStatusBadgeClass = (status) => {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        'accepted': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        'in_progress': 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
        'ready': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        'ready_for_pickup': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        'picked_up': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
        'shipped': 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200',
        'delivered': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
        'cancelled': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    };
    return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
};

const getPaymentStatusClass = (status) => {
    const classes = {
        'paid': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        'unpaid': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        'refunded': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    };
    return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
};
</script>

<style scoped>
/* Modal transition animations */
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-active > div,
.modal-leave-active > div {
    transition: transform 0.3s ease;
}

.modal-enter-from > div,
.modal-leave-to > div {
    transform: scale(0.95);
}
</style>
