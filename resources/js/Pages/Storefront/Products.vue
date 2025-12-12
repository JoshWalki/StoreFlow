<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Inactive Store Banner -->
        <div
            v-if="!store.is_active"
            class="bg-yellow-500 text-white py-3 px-4 text-center font-medium"
        >
            <div class="max-w-7xl mx-auto flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        fill-rule="evenodd"
                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd"
                    />
                </svg>
                <span>This store is currently not accepting orders.</span>
            </div>
        </div>

        <!-- Header -->
        <header class="bg-white shadow-sm sticky top-0 z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <a
                            :href="`/store/${store.id}`"
                            class="text-sm text-blue-600 hover:text-blue-700 mb-2 inline-block"
                        >
                            ← Back to Store
                        </a>
                        <div class="flex items-center">
                            <img
                                v-if="store.logo_url"
                                :src="store.logo_url"
                                :alt="store.name"
                                class="max-h-12 max-w-xs object-contain"
                            />
                            <h1 v-else class="text-2xl font-bold text-gray-900">
                                {{ store.name }}
                            </h1>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <!-- Track Order Button -->
                        <a
                            :href="`/store/${store.id}/track-order`"
                            :class="['hidden sm:flex items-center gap-1.5 px-4 py-2 rounded-lg transition-colors text-sm font-medium', buttonClasses.outline]"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                            Track Order
                        </a>

                        <!-- Profile/Login Button -->
                        <a
                            v-if="!customer"
                            :href="`/store/${store.id}/login`"
                            :class="['hidden sm:flex items-center gap-1.5 px-4 py-2 rounded-lg transition-colors text-sm font-medium', buttonClasses.primary]"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            Login
                        </a>
                        <a
                            v-else
                            :href="`/store/${store.id}/profile`"
                            :class="['hidden sm:flex items-center gap-1.5 px-4 py-2 rounded-lg transition-colors text-sm font-medium', buttonClasses.primary]"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            {{ customer.first_name }}
                        </a>

                        <!-- Cart Button -->
                        <CartButton />
                    </div>
                </div>
            </div>
        </header>

        <!-- Cart Drawer -->
        <CartDrawer :store="store" />

        <!-- Main Layout: Sidebar + Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex gap-8">
                <!-- Left Sidebar Navigation -->
                <aside class="w-64 flex-shrink-0 hidden lg:block">
                    <div class="bg-white rounded-lg shadow-sm p-6 sticky top-24">
                        <h2 class="text-xl font-bold text-gray-900 mb-2">Menu</h2>

                        <!-- Operating Hours -->
                        <div v-if="store.open_time && store.close_time" class="mb-6 pb-4 border-b border-gray-200">
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="font-medium">{{ formatTime(store.open_time) }} - {{ formatTime(store.close_time) }}</span>
                            </div>
                        </div>

                        <!-- Search -->
                        <div class="mb-6">
                            <input
                                type="text"
                                v-model="searchQuery"
                                placeholder="Search products..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                        </div>

                        <!-- Categories -->
                        <nav class="space-y-1">
                            <a
                                href="#"
                                @click.prevent="filterByCategory(null)"
                                class="block px-3 py-2 text-sm font-medium rounded transition-colors"
                                :class="selectedCategory === null ? 'bg-gray-200 text-gray-900' : 'text-gray-700 hover:bg-gray-100'"
                            >
                                All Products
                            </a>
                            <a
                                v-for="category in categories"
                                :key="category.id"
                                href="#"
                                @click.prevent="filterByCategory(category.slug)"
                                class="block px-3 py-2 text-sm font-medium rounded transition-colors"
                                :class="selectedCategory === category.slug ? 'bg-gray-200 text-gray-900 font-semibold' : 'text-gray-700 hover:bg-gray-100'"
                            >
                                {{ category.name }}
                            </a>
                        </nav>
                    </div>
                </aside>

                <!-- Main Content Area -->
                <main class="flex-1 min-w-0">
                    <!-- Page Title and Results Count -->
                    <div class="mb-6">
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">
                            {{ pageTitle }}
                        </h1>
                        <p class="text-gray-600">
                            {{ filteredProducts.length }} {{ filteredProducts.length === 1 ? 'product' : 'products' }}
                        </p>
                    </div>

                    <!-- Products Grid -->
                    <div v-if="filteredProducts.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <ProductCardGrid
                            v-for="product in paginatedProducts"
                            :key="product.id"
                            :product="product"
                            :store="store"
                        />
                    </div>

                    <!-- Empty State -->
                    <div v-else class="text-center py-12">
                        <svg
                            class="mx-auto h-24 w-24 text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                            />
                        </svg>
                        <h3 class="mt-4 text-xl font-medium text-gray-900">
                            No products found
                        </h3>
                        <p class="mt-2 text-gray-500">
                            Try adjusting your search or filter to find what you're looking for.
                        </p>
                    </div>

                    <!-- Pagination -->
                    <div v-if="totalPages > 1" class="mt-8 flex justify-center gap-2">
                        <button
                            @click="currentPage = Math.max(1, currentPage - 1)"
                            :disabled="currentPage === 1"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Previous
                        </button>
                        <span class="px-4 py-2 text-sm text-gray-700">
                            Page {{ currentPage }} of {{ totalPages }}
                        </span>
                        <button
                            @click="currentPage = Math.min(totalPages, currentPage + 1)"
                            :disabled="currentPage === totalPages"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Next
                        </button>
                    </div>
                </main>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import CartButton from "@/Components/Storefront/CartButton.vue";
import CartDrawer from "@/Components/Storefront/CartDrawer.vue";
import ProductCardGrid from "@/Components/Storefront/ProductCardGrid.vue";
import { useTheme } from '@/Composables/useTheme';

const props = defineProps({
    store: {
        type: Object,
        required: true,
    },
    products: {
        type: Array,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
    },
    customer: {
        type: Object,
        default: null,
    },
});

// Initialize theme
const { config: themeConfig } = useTheme(props.store.theme_key);

// Theme-based button colors
const buttonClasses = computed(() => {
    const theme = props.store.theme_key || 'classic';
    return {
        primary: themeConfig.value.buttonPrimary,
        outline: theme === 'modern'
            ? 'border-2 border-purple-600 text-purple-600 hover:bg-purple-50 bg-white'
            : theme === 'bold'
            ? 'border-2 border-orange-500 text-orange-500 hover:bg-orange-50 bg-gray-900'
            : 'border-2 border-blue-600 text-blue-600 hover:bg-blue-50 bg-white',
    };
});

const searchQuery = ref('');
const selectedCategory = ref(null);
const currentPage = ref(1);
const itemsPerPage = 12;

// Computed
const totalCount = computed(() => props.products.length);

const pageTitle = computed(() => {
    if (selectedCategory.value) {
        const category = props.categories.find(c => c.slug === selectedCategory.value);
        return category ? category.name : 'Products';
    }
    return searchQuery.value ? `Search Results for "${searchQuery.value}"` : 'All Products';
});

const filteredProducts = computed(() => {
    let filtered = props.products;

    // Filter by search query
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(product =>
            product.name.toLowerCase().includes(query) ||
            (product.description && product.description.toLowerCase().includes(query))
        );
    }

    // Filter by category
    if (selectedCategory.value) {
        filtered = filtered.filter(product =>
            product.category_slug === selectedCategory.value
        );
    }

    return filtered;
});

const totalPages = computed(() => Math.ceil(filteredProducts.value.length / itemsPerPage));

const paginatedProducts = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return filteredProducts.value.slice(start, end);
});

// Methods
const filterByCategory = (categorySlug) => {
    selectedCategory.value = categorySlug;
    currentPage.value = 1; // Reset to first page when filtering
};

const formatTime = (time) => {
    if (!time) return '';
    const [hours, minutes] = time.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour % 12 || 12;
    return `${displayHour}:${minutes} ${ampm}`;
};
</script>
