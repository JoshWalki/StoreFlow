<template>
    <div class="min-h-screen bg-gray-900">
        <!-- Header -->
        <header class="bg-gray-800 border-b border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-white">StoreFlow Platform</h1>
                    <p class="text-sm text-gray-400">Platform Owner Dashboard</p>
                </div>
                <div class="flex items-center space-x-4">
                    <Link :href="route('platform.merchants')" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                        Merchants
                    </Link>
                    <form @submit.prevent="logout" class="inline">
                        <button type="submit" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 mb-8">
                <!-- Total Merchants -->
                <div class="bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-700">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <dt class="text-sm font-medium text-gray-400 truncate">Total Merchants</dt>
                                <dd class="mt-1 text-3xl font-semibold text-white">{{ stats.total_merchants }}</dd>
                            </div>
                        </div>
                        <div class="mt-2 text-sm text-gray-400">
                            <span :class="stats.growth_rate >= 0 ? 'text-green-400' : 'text-red-400'">
                                {{ stats.growth_rate }}%
                            </span>
                            growth this month
                        </div>
                    </div>
                </div>

                <!-- Total Stores -->
                <div class="bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-700">
                    <div class="p-5">
                        <dt class="text-sm font-medium text-gray-400 truncate">Total Stores</dt>
                        <dd class="mt-1 text-3xl font-semibold text-white">{{ stats.total_stores }}</dd>
                    </div>
                </div>

                <!-- Total Revenue -->
                <div class="bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-700">
                    <div class="p-5">
                        <dt class="text-sm font-medium text-gray-400 truncate">Total Revenue</dt>
                        <dd class="mt-1 text-3xl font-semibold text-white">${{ stats.total_revenue }}</dd>
                    </div>
                </div>

                <!-- Total Orders -->
                <div class="bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-700">
                    <div class="p-5">
                        <dt class="text-sm font-medium text-gray-400 truncate">Total Orders</dt>
                        <dd class="mt-1 text-3xl font-semibold text-white">{{ stats.total_orders }}</dd>
                    </div>
                </div>

                <!-- Total Customers -->
                <div class="bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-700">
                    <div class="p-5">
                        <dt class="text-sm font-medium text-gray-400 truncate">Total Customers</dt>
                        <dd class="mt-1 text-3xl font-semibold text-white">{{ stats.total_customers }}</dd>
                    </div>
                </div>

                <!-- Total Users -->
                <div class="bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-700">
                    <div class="p-5">
                        <dt class="text-sm font-medium text-gray-400 truncate">Total Users</dt>
                        <dd class="mt-1 text-3xl font-semibold text-white">{{ stats.total_users }}</dd>
                    </div>
                </div>
            </div>

            <!-- Recent Merchants -->
            <div class="bg-gray-800 shadow rounded-lg border border-gray-700 mb-8">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg leading-6 font-medium text-white">Recent Merchants</h3>
                        <Link :href="route('platform.merchants')" class="text-sm text-blue-400 hover:text-blue-300">
                            View All →
                        </Link>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Owner Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Stores</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Created</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                <tr v-for="merchant in stats.recent_merchants" :key="merchant.id" class="hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-white">{{ merchant.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">{{ merchant.owner_email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">{{ merchant.stores_count }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">{{ formatDate(merchant.created_at) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Orders Status Breakdown -->
            <div class="bg-gray-800 shadow rounded-lg border border-gray-700">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-white mb-4">Orders by Status</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                        <div v-for="(count, status) in stats.orders_by_status" :key="status" class="text-center">
                            <div class="text-2xl font-bold text-white">{{ count }}</div>
                            <div class="text-sm text-gray-400 capitalize">{{ formatStatus(status) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';

defineProps({
    stats: Object,
});

const logout = () => {
    router.post(route('platform.logout'));
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const formatStatus = (status) => {
    return status.replace('_', ' ');
};
</script>
