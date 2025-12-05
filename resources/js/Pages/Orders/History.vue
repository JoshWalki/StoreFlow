<template>
    <DashboardLayout :store="store" :user="user">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Order History</h1>
            </div>

            <!-- Search & Filters -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2 relative">
                        <input
                            v-model="searchForm.search"
                            type="text"
                            placeholder="Search by order number or customer..."
                            class="w-full px-4 py-2 pr-10 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500"
                            @input="debouncedSearch"
                        />
                        <button
                            v-if="searchForm.search"
                            @click="clearSearch"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300"
                            type="button"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <select
                        v-model="searchForm.status"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                        @change="applyFilters"
                    >
                        <option value="">All Statuses</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="refunded">Refunded</option>
                    </select>
                    <select
                        v-model="searchForm.period"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                        @change="applyFilters"
                    >
                        <option value="all">All Time</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                        <option value="year">This Year</option>
                    </select>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Orders</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total_orders }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Revenue</div>
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ formatCurrencyValue(stats.total_revenue) }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Average Order</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatCurrencyValue(stats.average_order) }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Cancelled</div>
                    <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ stats.cancelled_orders }}</div>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Order
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Customer
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Total
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Items
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="order in orders.data" :key="order.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer" @click="openOrderDetails(order)">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-blue-600 dark:text-blue-400">#{{ order.order_number }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">{{ order.customer_name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ order.customer_email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">{{ formatDate(order.created_at) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                                    :class="getStatusClass(order.status)"
                                >
                                    {{ order.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ formatCurrencyValue(order.total) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">{{ order.items_count }} items</div>
                            </td>
                        </tr>

                        <tr v-if="orders.data.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                No orders found.
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="orders.links.length > 3" class="bg-gray-50 dark:bg-gray-700 px-6 py-3 flex items-center justify-between border-t border-gray-200 dark:border-gray-600">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <Link
                            v-if="orders.prev_page_url"
                            :href="orders.prev_page_url"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                            Previous
                        </Link>
                        <Link
                            v-if="orders.next_page_url"
                            :href="orders.next_page_url"
                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                            Next
                        </Link>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                Showing
                                <span class="font-medium">{{ orders.from }}</span>
                                to
                                <span class="font-medium">{{ orders.to }}</span>
                                of
                                <span class="font-medium">{{ orders.total }}</span>
                                results
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                <Link
                                    v-for="(link, index) in orders.links"
                                    :key="index"
                                    :href="link.url"
                                    :class="[
                                        'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                        link.active
                                            ? 'z-10 bg-blue-50 dark:bg-blue-900 border-blue-500 dark:border-blue-600 text-blue-600 dark:text-blue-200'
                                            : 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700',
                                        index === 0 ? 'rounded-l-md' : '',
                                        index === orders.links.length - 1 ? 'rounded-r-md' : '',
                                    ]"
                                    v-html="link.label"
                                />
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Details Modal -->
        <OrderDetailsModal
            v-if="selectedOrder"
            :isOpen="isModalOpen"
            :order="selectedOrder"
            @close="closeOrderDetails"
            @status-updated="handleStatusUpdated"
        />
    </DashboardLayout>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import OrderDetailsModal from '@/Components/Operations/OrderDetailModal.vue';

const props = defineProps({
    orders: Object,
    stats: Object,
    filters: Object,
    store: Object,
    user: Object,
});

const searchForm = reactive({
    search: props.filters.search || '',
    status: props.filters.status || '',
    period: props.filters.period || 'all',
});

let searchTimeout = null;

const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
};

const applyFilters = () => {
    const params = {};

    // Only include non-empty values
    if (searchForm.search) {
        params.search = searchForm.search;
    }
    if (searchForm.status) {
        params.status = searchForm.status;
    }
    if (searchForm.period) {
        params.period = searchForm.period;
    }

    router.get(route('orders.history'), params, {
        preserveState: true,
        replace: true,
    });
};

const clearSearch = () => {
    searchForm.search = '';
    applyFilters();
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getStatusClass = (status) => {
    const classes = {
        completed: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800',
        refunded: 'bg-yellow-100 text-yellow-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const formatCurrencyValue = (amount) => {
    return new Intl.NumberFormat('en-AU', {
        style: 'currency',
        currency: 'AUD',
    }).format(amount);
};

// Modal state and handlers
const isModalOpen = ref(false);
const selectedOrder = ref(null);

const openOrderDetails = async (order) => {
    // Fetch full order details from backend
    try {
        const response = await fetch(route('orders.show', order.id), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        });
        const fullOrderData = await response.json();
        selectedOrder.value = fullOrderData;
        isModalOpen.value = true;
    } catch (error) {
        console.error('Failed to load order details:', error);
        // Fallback to using the order data we have
        selectedOrder.value = order;
        isModalOpen.value = true;
    }
};

const closeOrderDetails = () => {
    isModalOpen.value = false;
    selectedOrder.value = null;
};

const handleStatusUpdated = (newStatus) => {
    // Refresh the orders list after status update
    router.reload({ only: ['orders', 'stats'] });
};
</script>
