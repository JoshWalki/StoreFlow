<template>
    <DashboardLayout :store="store" :user="user">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Products</h1>
                <Link
                    v-if="user && user.role !== 'staff'"
                    :href="route('products.create')"
                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors"
                >
                    Add Product
                </Link>
            </div>

            <!-- Search & Filters -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4">
                <div class="flex gap-4">
                    <input
                        v-model="searchForm.search"
                        type="text"
                        placeholder="Search products..."
                        class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500"
                        @input="debouncedSearch"
                    />
                    <select
                        v-model="searchForm.category"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                        @change="applyFilters"
                    >
                        <option value="">All Categories</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">
                            {{ category.name }}
                        </option>
                    </select>
                    <select
                        v-model="searchForm.status"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                        @change="applyFilters"
                    >
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <div
                    v-for="product in products.data"
                    :key="product.id"
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow"
                >
                    <div class="aspect-square bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                        <img
                            v-if="product.image"
                            :src="product.image"
                            :alt="product.name"
                            class="w-full h-full object-cover"
                        />
                        <span v-else class="text-gray-400 dark:text-gray-500 text-4xl">📦</span>
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-1 truncate">{{ product.name }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ product.category?.name || 'No category' }}</p>
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-lg font-bold text-gray-900 dark:text-white">{{ formatCurrency(product.price_cents) }}</span>
                            <span v-if="product.images_count" class="text-sm text-gray-600 dark:text-gray-400">
                                {{ product.images_count }} {{ product.images_count === 1 ? 'image' : 'images' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span
                                class="px-2 py-1 text-xs font-semibold rounded-full"
                                :class="product.is_active ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300'"
                            >
                                {{ product.is_active ? 'Active' : 'Inactive' }}
                            </span>
                            <div v-if="user && user.role !== 'staff'" class="flex space-x-2">
                                <Link
                                    :href="route('products.edit', product.id)"
                                    class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 text-sm"
                                >
                                    Edit
                                </Link>
                                <button
                                    @click="deleteProduct(product)"
                                    class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 text-sm"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="products.data.length === 0" class="col-span-full">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-12 text-center text-gray-500 dark:text-gray-400">
                        No products found. Create your first product to get started.
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="products.links.length > 3" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm px-6 py-3 flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                    <Link
                        v-if="products.prev_page_url"
                        :href="products.prev_page_url"
                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
                    >
                        Previous
                    </Link>
                    <Link
                        v-if="products.next_page_url"
                        :href="products.next_page_url"
                        class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
                    >
                        Next
                    </Link>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            Showing
                            <span class="font-medium">{{ products.from }}</span>
                            to
                            <span class="font-medium">{{ products.to }}</span>
                            of
                            <span class="font-medium">{{ products.total }}</span>
                            results
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                            <Link
                                v-for="(link, index) in products.links"
                                :key="index"
                                :href="link.url"
                                :class="[
                                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                    link.active
                                        ? 'z-10 bg-blue-50 dark:bg-blue-900 border-blue-500 dark:border-blue-600 text-blue-600 dark:text-blue-200'
                                        : 'bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-600',
                                    index === 0 ? 'rounded-l-md' : '',
                                    index === products.links.length - 1 ? 'rounded-r-md' : '',
                                ]"
                                v-html="link.label"
                            />
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    products: Object,
    categories: Array,
    filters: Object,
    store: Object,
    user: Object,
});

const searchForm = reactive({
    search: props.filters.search || '',
    category: props.filters.category || '',
    status: props.filters.status || '',
});

let searchTimeout = null;

const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
};

const applyFilters = () => {
    router.get(route('products.index'), {
        search: searchForm.search,
        category: searchForm.category,
        status: searchForm.status,
    }, {
        preserveState: true,
        replace: true,
    });
};

const deleteProduct = (product) => {
    if (confirm(`Are you sure you want to delete "${product.name}"?`)) {
        router.delete(route('products.destroy', product.id));
    }
};

const formatCurrency = (cents) => {
    return new Intl.NumberFormat('en-AU', {
        style: 'currency',
        currency: 'AUD',
    }).format(cents / 100);
};
</script>
