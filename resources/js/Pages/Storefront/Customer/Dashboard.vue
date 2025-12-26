<template>
    <div class="min-h-screen" :class="themeConfig.background">
        <!-- Header -->
        <header :class="store.theme === 'bold' ? 'bg-gray-900 border-b border-orange-500/20' : 'bg-white shadow-sm'">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex justify-between items-center">
                    <a :href="`/store/${store.id}`" class="flex items-center">
                        <h1 class="text-2xl font-bold" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                            {{ store.name }}
                        </h1>
                    </a>
                    <div class="flex items-center gap-4">
                        <a :href="`/store/${store.id}`" :class="themeConfig.link">Store</a>
                        <a :href="`/store/${store.id}/orders`" :class="themeConfig.link">Orders</a>
                        <button
                            @click="handleLogout"
                            type="button"
                            :class="themeConfig.link"
                        >
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Welcome Section -->
            <div class="mb-8">
                <h2 class="text-3xl font-bold" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                    Welcome back, {{ customer.first_name }}!
                </h2>
                <p :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">
                    Member since {{ stats.member_since }}
                </p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Total Orders -->
                <div class="rounded-lg shadow-md p-6" :class="themeConfig.cardBackground">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium" :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">
                                Total Orders
                            </p>
                            <p class="text-3xl font-bold mt-2" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                                {{ stats.total_orders }}
                            </p>
                        </div>
                        <div class="p-3 rounded-full" :class="store.theme === 'bold' ? 'bg-orange-500/10' : 'bg-blue-100'">
                            <svg class="w-8 h-8" :class="store.theme === 'bold' ? 'text-orange-500' : 'text-blue-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Spent -->
                <div class="rounded-lg shadow-md p-6" :class="themeConfig.cardBackground">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium" :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">
                                Total Spent
                            </p>
                            <p class="text-3xl font-bold mt-2" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                                ${{ stats.total_spent.toFixed(2) }}
                            </p>
                        </div>
                        <div class="p-3 rounded-full" :class="store.theme === 'bold' ? 'bg-orange-500/10' : 'bg-green-100'">
                            <svg class="w-8 h-8" :class="store.theme === 'bold' ? 'text-orange-500' : 'text-green-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Loyalty Points -->
                <div class="rounded-lg shadow-md p-6" :class="themeConfig.cardBackground">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium" :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">
                                Loyalty Points
                            </p>
                            <p class="text-3xl font-bold mt-2" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                                {{ stats.loyalty_points }}
                            </p>
                        </div>
                        <div class="p-3 rounded-full" :class="store.theme === 'bold' ? 'bg-orange-500/10' : 'bg-purple-100'">
                            <svg class="w-8 h-8" :class="store.theme === 'bold' ? 'text-orange-500' : 'text-purple-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="rounded-lg shadow-md p-6" :class="themeConfig.cardBackground">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                        Recent Orders
                    </h3>
                    <a :href="`/store/${store.id}/orders`" :class="themeConfig.link">
                        View All →
                    </a>
                </div>

                <!-- Orders List -->
                <div v-if="recent_orders.length > 0" class="space-y-4">
                    <div
                        v-for="order in recent_orders"
                        :key="order.id"
                        class="border rounded-lg p-4"
                        :class="store.theme === 'bold' ? 'border-gray-700' : 'border-gray-200'"
                    >
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <p class="font-semibold" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                                    Order #{{ order.public_id }}
                                </p>
                                <p class="text-sm" :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">
                                    {{ formatDate(order.created_at) }}
                                </p>
                            </div>
                            <div class="text-right">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    :class="getStatusClass(order.status)"
                                >
                                    {{ formatStatus(order.status) }}
                                </span>
                                <p class="font-semibold mt-1" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                                    ${{ order.total.toFixed(2) }}
                                </p>
                            </div>
                        </div>
                        <div class="text-sm" :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">
                            {{ order.items_count }} item{{ order.items_count !== 1 ? 's' : '' }}
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12">
                    <svg class="mx-auto h-12 w-12" :class="store.theme === 'bold' ? 'text-gray-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <p class="mt-2" :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">
                        No orders yet. Start shopping!
                    </p>
                    <a
                        :href="`/store/${store.id}/products`"
                        class="inline-block mt-4 px-6 py-2 rounded-lg font-semibold transition-all"
                        :class="themeConfig.buttonPrimary"
                    >
                        Browse Products
                    </a>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useTheme } from '@/Composables/useTheme';

const props = defineProps({
    store: {
        type: Object,
        required: true,
    },
    customer: {
        type: Object,
        required: true,
    },
    stats: {
        type: Object,
        required: true,
    },
    recent_orders: {
        type: Array,
        default: () => [],
    },
});

const { config: themeConfig } = useTheme(props.store.theme);

const handleLogout = () => {
    router.post(`/store/${props.store.id}/logout`);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const formatStatus = (status) => {
    const statusLabels = {
        'pending': 'Pending',
        'accepted': 'Accepted',
        'in_progress': 'In Progress',
        'preparing': 'Preparing',
        'ready': 'Ready',
        'ready_for_pickup': 'Ready for Pickup',
        'packing': 'Packing',
        'packed': 'Packed',
        'shipped': 'Shipped',
        'delivered': 'Delivered',
        'picked_up': 'Picked Up',
        'completed': 'Completed',
        'cancelled': 'Cancelled',
        'refunded': 'Refunded',
    };
    return statusLabels[status] || status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const getStatusClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        accepted: 'bg-blue-100 text-blue-800',
        preparing: 'bg-purple-100 text-purple-800',
        ready: 'bg-green-100 text-green-800',
        completed: 'bg-green-100 text-green-800',
        shipped: 'bg-blue-100 text-blue-800',
        delivered: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};
</script>
