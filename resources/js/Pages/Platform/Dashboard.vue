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
            <div class="bg-gray-800 shadow rounded-lg border border-gray-700 mb-8">
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

            <!-- System Notice Management -->
            <div class="bg-gray-800 shadow rounded-lg border border-gray-700">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-white mb-4">System Notice Management</h3>

                    <!-- Current Notice Preview -->
                    <div v-if="systemNotice" class="mb-6">
                        <label class="block text-sm font-medium text-gray-400 mb-2">Current Notice Preview:</label>
                        <div
                            class="p-4 rounded-lg text-center font-medium"
                            :style="{ backgroundColor: systemNotice.bg_color, color: systemNotice.text_color }"
                            v-html="systemNotice.message"
                        >
                        </div>
                        <form @submit.prevent="removeNotice" class="mt-2">
                            <button
                                type="submit"
                                class="text-sm text-red-400 hover:text-red-300"
                            >
                                Remove Notice
                            </button>
                        </form>
                    </div>

                    <!-- Notice Form -->
                    <form @submit.prevent="saveNotice" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Message</label>
                            <textarea
                                v-model="noticeForm.message"
                                rows="3"
                                required
                                maxlength="1000"
                                class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Enter system-wide notice (HTML supported: <strong>, <a>, <br>, etc.)"
                            ></textarea>
                            <p class="mt-1 text-xs text-gray-400">{{ noticeForm.message.length }}/1000 characters • HTML supported</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Background Color</label>
                                <div class="flex items-center space-x-2">
                                    <input
                                        v-model="noticeForm.bg_color"
                                        type="color"
                                        class="h-10 w-20 rounded cursor-pointer"
                                    />
                                    <input
                                        v-model="noticeForm.bg_color"
                                        type="text"
                                        pattern="^#[0-9A-Fa-f]{6}$"
                                        class="flex-1 px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Text Color</label>
                                <div class="flex items-center space-x-2">
                                    <input
                                        v-model="noticeForm.text_color"
                                        type="color"
                                        class="h-10 w-20 rounded cursor-pointer"
                                    />
                                    <input
                                        v-model="noticeForm.text_color"
                                        type="text"
                                        pattern="^#[0-9A-Fa-f]{6}$"
                                        class="flex-1 px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Live Preview -->
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-2">Live Preview:</label>
                            <div
                                class="p-4 rounded-lg text-center font-medium"
                                :style="{ backgroundColor: noticeForm.bg_color, color: noticeForm.text_color }"
                                v-html="noticeForm.message || 'Your notice message will appear here...'"
                            >
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button
                                type="submit"
                                :disabled="processing"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            >
                                <span v-if="!processing">Save Notice</span>
                                <span v-else>Saving...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    stats: Object,
    systemNotice: Object,
});

const processing = ref(false);
const noticeForm = ref({
    message: props.systemNotice?.message || '',
    bg_color: props.systemNotice?.bg_color || '#3b82f6',
    text_color: props.systemNotice?.text_color || '#ffffff',
});

const logout = () => {
    router.post(route('platform.logout'));
};

const saveNotice = () => {
    processing.value = true;
    router.post(route('platform.notice.store'), noticeForm.value, {
        onFinish: () => {
            processing.value = false;
        },
    });
};

const removeNotice = () => {
    if (confirm('Are you sure you want to remove the system notice?')) {
        router.delete(route('platform.notice.destroy'), {
            onSuccess: () => {
                noticeForm.value = {
                    message: '',
                    bg_color: '#3b82f6',
                    text_color: '#ffffff',
                };
            },
        });
    }
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
