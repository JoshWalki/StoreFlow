<template>
    <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Customers</h1>
            </div>

            <!-- Search & Filters -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-3 sm:p-4">
                <div class="space-y-3 sm:space-y-4">
                    <!-- Search and Sort Row -->
                    <div class="flex flex-col sm:flex-row gap-2 sm:gap-4">
                        <input
                            v-model="searchForm.search"
                            type="text"
                            placeholder="Search by name, email, or phone..."
                            class="flex-1 px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500"
                            @input="debouncedSearch"
                            aria-label="Search customers"
                        />
                        <div class="flex gap-2">
                            <select
                                v-model="searchForm.sort"
                                class="flex-1 sm:flex-none px-3 sm:px-4 py-2 text-xs sm:text-base border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                                @change="applyFilters"
                                aria-label="Sort customers"
                            >
                                <option value="recent">Recent</option>
                                <option value="lifetime_value">Value</option>
                                <option value="name">Name</option>
                            </select>
                            <button
                                @click="showFilters = !showFilters"
                                class="flex-1 sm:flex-none px-3 sm:px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors flex items-center justify-center gap-2"
                                aria-label="Toggle filters"
                            >
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                </svg>
                                <span class="text-xs sm:text-base">Filters</span>
                            </button>
                        </div>
                    </div>

                    <!-- Advanced Filters (Collapsible) -->
                    <Transition name="slide-down">
                        <div v-if="showFilters" class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4 pt-3 sm:pt-4 border-t border-gray-200 dark:border-gray-700">
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Status
                                </label>
                                <select
                                    v-model="searchForm.status"
                                    class="w-full px-2 sm:px-4 py-2 text-xs sm:text-base border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                                    @change="applyFilters"
                                >
                                    <option value="all">All Customers</option>
                                    <option value="active">Active (90 days)</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    From Date
                                </label>
                                <input
                                    v-model="searchForm.date_from"
                                    type="date"
                                    class="w-full px-2 sm:px-4 py-2 text-xs sm:text-base border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                                    @change="applyFilters"
                                />
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    To Date
                                </label>
                                <input
                                    v-model="searchForm.date_to"
                                    type="date"
                                    class="w-full px-2 sm:px-4 py-2 text-xs sm:text-base border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                                    @change="applyFilters"
                                />
                            </div>
                        </div>
                    </Transition>

                    <!-- Active Filters Display -->
                    <div v-if="hasActiveFilters" class="flex flex-wrap gap-2">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Active filters:</span>
                        <button
                            v-if="searchForm.status !== 'all'"
                            @click="clearFilter('status')"
                            class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-md text-xs"
                        >
                            {{ searchForm.status }}
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                        <button
                            v-if="searchForm.date_from"
                            @click="clearFilter('date_from')"
                            class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-md text-xs"
                        >
                            From: {{ searchForm.date_from }}
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                        <button
                            v-if="searchForm.date_to"
                            @click="clearFilter('date_to')"
                            class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-md text-xs"
                        >
                            To: {{ searchForm.date_to }}
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                        <button
                            @click="clearAllFilters"
                            class="text-xs text-blue-600 dark:text-blue-400 hover:underline"
                        >
                            Clear all
                        </button>
                    </div>
                </div>
            </div>

            <!-- Customers Table -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Customer
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Email
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Phone
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Total Orders
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Lifetime Value
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Last Order
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <tr
                            v-for="customer in customers.data"
                            :key="customer.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors"
                            @click="openCustomerOrders(customer.id)"
                            tabindex="0"
                            @keypress.enter="openCustomerOrders(customer.id)"
                            @keypress.space.prevent="openCustomerOrders(customer.id)"
                            role="button"
                            :aria-label="`View orders for ${customer.name}`"
                        >
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                        <span class="text-blue-600 dark:text-blue-200 font-medium text-sm">
                                            {{ customer.name.charAt(0).toUpperCase() }}
                                        </span>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ customer.name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">{{ customer.email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">{{ customer.phone || '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">{{ customer.orders_count }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ formatCurrencyValue(customer.lifetime_value) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ formatDate(customer.last_order_date) }}</div>
                            </td>
                        </tr>

                        <tr v-if="customers.data.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                No customers found.
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="customers.links.length > 3" class="bg-gray-50 dark:bg-gray-700 px-6 py-3 flex items-center justify-between border-t border-gray-200 dark:border-gray-600">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <Link
                            v-if="customers.prev_page_url"
                            :href="customers.prev_page_url"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                            Previous
                        </Link>
                        <Link
                            v-if="customers.next_page_url"
                            :href="customers.next_page_url"
                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                            Next
                        </Link>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                Showing
                                <span class="font-medium">{{ customers.from }}</span>
                                to
                                <span class="font-medium">{{ customers.to }}</span>
                                of
                                <span class="font-medium">{{ customers.total }}</span>
                                results
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                <template v-for="(link, index) in customers.links" :key="index">
                                    <Link
                                        v-if="link.url"
                                        :href="link.url"
                                        :class="[
                                            'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                            link.active
                                                ? 'z-10 bg-blue-50 dark:bg-blue-900 border-blue-500 dark:border-blue-600 text-blue-600 dark:text-blue-200'
                                                : 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700',
                                            index === 0 ? 'rounded-l-md' : '',
                                            index === customers.links.length - 1 ? 'rounded-r-md' : '',
                                        ]"
                                        v-html="link.label"
                                    />
                                    <span
                                        v-else
                                        :class="[
                                            'relative inline-flex items-center px-4 py-2 border text-sm font-medium cursor-not-allowed',
                                            'bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-400 dark:text-gray-500',
                                            index === 0 ? 'rounded-l-md' : '',
                                            index === customers.links.length - 1 ? 'rounded-r-md' : '',
                                        ]"
                                        v-html="link.label"
                                    />
                                </template>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Orders Modal -->
            <CustomerOrdersModal
                :show="showOrdersModal"
                :customer-id="selectedCustomerId"
                @close="closeOrdersModal"
            />
        </div>
</template>

<script setup>
import { reactive, ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import CustomerOrdersModal from '@/Components/Customers/CustomerOrdersModal.vue';

const props = defineProps({
    customers: Object,
    filters: Object,
    store: Object,
    user: Object,
});

const searchForm = reactive({
    search: props.filters.search || '',
    sort: props.filters.sort || 'recent',
    status: props.filters.status || 'all',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
});

const showFilters = ref(false);
const showOrdersModal = ref(false);
const selectedCustomerId = ref(null);

let searchTimeout = null;

// Computed property to check if there are active filters
const hasActiveFilters = computed(() => {
    return searchForm.status !== 'all' || searchForm.date_from || searchForm.date_to;
});

const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
};

const applyFilters = () => {
    router.get(route('customers.index'), {
        search: searchForm.search,
        sort: searchForm.sort,
        status: searchForm.status,
        date_from: searchForm.date_from,
        date_to: searchForm.date_to,
    }, {
        preserveState: true,
        replace: true,
    });
};

const clearFilter = (filterName) => {
    if (filterName === 'status') {
        searchForm.status = 'all';
    } else {
        searchForm[filterName] = '';
    }
    applyFilters();
};

const clearAllFilters = () => {
    searchForm.status = 'all';
    searchForm.date_from = '';
    searchForm.date_to = '';
    applyFilters();
};

const openCustomerOrders = (customerId) => {
    selectedCustomerId.value = customerId;
    showOrdersModal.value = true;
};

const closeOrdersModal = () => {
    showOrdersModal.value = false;
    selectedCustomerId.value = null;
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const formatCurrencyValue = (amount) => {
    return new Intl.NumberFormat('en-AU', {
        style: 'currency',
        currency: 'AUD',
    }).format(amount);
};
</script>

<style scoped>
/* Slide down animation for filters */
.slide-down-enter-active,
.slide-down-leave-active {
    transition: all 0.3s ease;
    max-height: 300px;
    overflow: hidden;
}

.slide-down-enter-from,
.slide-down-leave-to {
    max-height: 0;
    opacity: 0;
}
</style>
