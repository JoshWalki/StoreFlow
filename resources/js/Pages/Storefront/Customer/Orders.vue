<template>
    <div class="min-h-screen" :class="themeConfig.background">
        <!-- Toast Notifications -->
        <ToastContainer />

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
                        <a :href="`/store/${store.id}/profile`" :class="themeConfig.link">Profile</a>
                        <a :href="`/store/${store.id}`" :class="themeConfig.link">Store</a>
                        <form :action="`/store/${store.id}/logout`" method="POST" class="inline">
                            <input type="hidden" name="_token" :value="csrfToken" />
                            <button type="submit" :class="themeConfig.link">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <h2 class="text-3xl font-bold mb-8" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                Order History
            </h2>

            <!-- Orders List -->
            <div v-if="orders.data && orders.data.length > 0" class="space-y-4">
                <div
                    v-for="order in orders.data"
                    :key="order.id"
                    class="rounded-lg shadow-md p-6"
                    :class="themeConfig.cardBackground"
                >
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-lg font-semibold" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                                Order #{{ order.public_id }}
                            </h3>
                            <p class="text-sm" :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">
                                Placed on {{ formatDate(order.created_at) }}
                            </p>
                        </div>
                        <div class="text-right">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                :class="getStatusClass(order.status)"
                            >
                                {{ order.status }}
                            </span>
                            <p class="text-xl font-bold mt-1" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                                ${{ order.total.toFixed(2) }}
                            </p>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="border-t pt-4" :class="store.theme === 'bold' ? 'border-gray-700' : 'border-gray-200'">
                        <h4 class="text-sm font-medium mb-2" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">
                            Items ({{ order.items_count }})
                        </h4>
                        <div class="space-y-2">
                            <div
                                v-for="(item, index) in order.items"
                                :key="index"
                                class="flex justify-between text-sm"
                            >
                                <span :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">
                                    {{ item.quantity }}x {{ item.product_name }}
                                </span>
                                <span :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                                    ${{ (item.price * item.quantity).toFixed(2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="orders.links && orders.links.length > 3" class="flex justify-center gap-2 mt-8">
                    <a
                        v-for="(link, index) in orders.links"
                        :key="index"
                        :href="link.url"
                        v-html="link.label"
                        class="px-3 py-2 rounded-md text-sm font-medium transition-colors"
                        :class="[
                            link.active
                                ? (store.theme === 'bold' ? 'bg-orange-500 text-white' : 'bg-blue-600 text-white')
                                : (store.theme === 'bold' ? 'bg-gray-800 text-gray-300 hover:bg-gray-700' : 'bg-white text-gray-700 hover:bg-gray-50'),
                            !link.url ? 'opacity-50 cursor-not-allowed' : ''
                        ]"
                    ></a>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="rounded-lg shadow-md p-12 text-center" :class="themeConfig.cardBackground">
                <svg class="mx-auto h-16 w-16" :class="store.theme === 'bold' ? 'text-gray-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <h3 class="mt-4 text-lg font-medium" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                    No orders yet
                </h3>
                <p class="mt-2" :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">
                    Start shopping to see your orders here
                </p>
                <a
                    :href="`/store/${store.id}/products`"
                    class="inline-block mt-6 px-6 py-3 rounded-lg font-semibold transition-all"
                    :class="themeConfig.buttonPrimary"
                >
                    Browse Products
                </a>
            </div>
        </main>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useTheme } from '@/Composables/useTheme';
import ToastContainer from '@/Components/Notifications/ToastContainer.vue';

const props = defineProps({
    store: {
        type: Object,
        required: true,
    },
    customer: {
        type: Object,
        required: true,
    },
    orders: {
        type: Object,
        required: true,
    },
});

const { config: themeConfig } = useTheme(props.store.theme);

const csrfToken = computed(() => {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
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
