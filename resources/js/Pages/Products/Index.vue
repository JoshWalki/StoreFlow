<template>
    <DashboardLayout :store="store" :user="user">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Products</h1>
                <div v-if="user && user.role !== 'staff'" class="flex gap-3">
                    <button
                        @click="showImportModal = true"
                        class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        Import CSV
                    </button>
                    <Link
                        :href="route('products.create')"
                        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors"
                    >
                        Add Product
                    </Link>
                </div>
            </div>

            <!-- Bulk Actions Toolbar -->
            <div v-if="selectedProducts.length > 0" class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg shadow-sm p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <span class="text-sm font-medium text-blue-900 dark:text-blue-100">
                            {{ selectedProducts.length }} product{{ selectedProducts.length > 1 ? 's' : '' }} selected
                        </span>
                        <button
                            @click="selectedProducts = []"
                            class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
                        >
                            Clear selection
                        </button>
                    </div>
                    <div v-if="user && user.role !== 'staff'" class="flex gap-3">
                        <button
                            @click="openBulkEdit"
                            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors flex items-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Bulk Edit
                        </button>
                        <button
                            @click="bulkDelete"
                            class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors flex items-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Delete Selected
                        </button>
                    </div>
                </div>
            </div>

            <!-- Search & Filters -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4">
                <div class="flex gap-4">
                    <div v-if="user && user.role !== 'staff'" class="flex items-center">
                        <input
                            type="checkbox"
                            :checked="selectedProducts.length === products.data.length && products.data.length > 0"
                            @change="toggleSelectAll"
                            class="w-4 h-4 text-blue-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500"
                        />
                        <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">Select All</label>
                    </div>
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
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow relative"
                    :class="{ 'ring-2 ring-blue-500': isProductSelected(product.id) }"
                >
                    <!-- Selection Checkbox -->
                    <div v-if="user && user.role !== 'staff'" class="absolute top-2 left-2 z-10">
                        <input
                            type="checkbox"
                            :checked="isProductSelected(product.id)"
                            @change="toggleProductSelection(product.id)"
                            class="w-5 h-5 text-blue-600 bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 cursor-pointer shadow-md"
                        />
                    </div>

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

        <!-- Import CSV Modal -->
        <div
            v-if="showImportModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            @click.self="closeImportModal"
        >
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full mx-4 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Import Products from CSV</h2>
                    <button
                        @click="closeImportModal"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- CSV Format Instructions -->
                <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-md">
                    <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-2">CSV Format Requirements:</h3>
                    <p class="text-sm text-blue-800 dark:text-blue-200 mb-2">Your CSV file should include the following columns:</p>
                    <ul class="text-sm text-blue-800 dark:text-blue-200 list-disc list-inside space-y-1">
                        <li><strong>name</strong> (required) - Product name</li>
                        <li><strong>description</strong> (optional) - Product description</li>
                        <li><strong>price_cents</strong> (required) - Price in cents (e.g., 2999 for $29.99)</li>
                        <li><strong>is_active</strong> (optional) - 1 for active, 0 for inactive (default: 1)</li>
                        <li><strong>weight_grams</strong> (optional) - Weight in grams</li>
                        <li><strong>length_cm</strong> (optional) - Length in centimeters</li>
                        <li><strong>width_cm</strong> (optional) - Width in centimeters</li>
                        <li><strong>height_cm</strong> (optional) - Height in centimeters</li>
                        <li><strong>is_shippable</strong> (optional) - 1 for shippable, 0 for non-shippable (default: 1)</li>
                        <li><strong>image_url</strong> (optional) - Image URL to download (first image will be primary)</li>
                        <li><strong>image_url_2, image_url_3, image_url_4, image_url_5</strong> (optional) - Additional image URLs (max 5 total)</li>
                    </ul>
                </div>

                <!-- File Upload -->
                <div v-if="!importResults && !importing" class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Select CSV File
                    </label>
                    <input
                        ref="fileInput"
                        type="file"
                        accept=".csv"
                        @change="handleFileSelect"
                        class="block w-full text-sm text-gray-900 dark:text-white border border-gray-300 dark:border-gray-600 rounded-md cursor-pointer bg-gray-50 dark:bg-gray-700 focus:outline-none"
                    />
                    <p v-if="selectedFile" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Selected: {{ selectedFile.name }} ({{ (selectedFile.size / 1024).toFixed(2) }} KB)
                    </p>
                </div>

                <!-- Import Progress -->
                <div v-if="importing && !importResults" class="mb-4">
                    <div class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                        <div class="flex items-center justify-center mb-3">
                            <svg class="animate-spin h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                        <h3 class="text-center text-lg font-semibold text-blue-900 dark:text-blue-100 mb-2">
                            Importing Products...
                        </h3>
                        <p class="text-center text-sm text-blue-800 dark:text-blue-200 mb-4">
                            Processing CSV file and downloading images. This may take a few minutes for large imports.
                        </p>

                        <!-- Animated Progress Bar -->
                        <div class="w-full bg-blue-200 dark:bg-blue-800 rounded-full h-2.5 overflow-hidden">
                            <div class="bg-blue-600 dark:bg-blue-400 h-2.5 rounded-full animate-progress"></div>
                        </div>

                        <p class="text-center text-xs text-blue-700 dark:text-blue-300 mt-3">
                            Please do not close this window
                        </p>
                    </div>
                </div>

                <!-- Import Results -->
                <div v-if="importResults" class="mb-4 p-4 rounded-md" :class="importResults.success ? 'bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700' : 'bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700'">
                    <h3 class="text-sm font-semibold mb-2" :class="importResults.success ? 'text-green-900 dark:text-green-100' : 'text-red-900 dark:text-red-100'">
                        {{ importResults.success ? 'Import Successful!' : 'Import Failed' }}
                    </h3>
                    <p class="text-sm mb-2" :class="importResults.success ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200'">
                        {{ importResults.message }}
                    </p>
                    <div v-if="importResults.details" class="text-sm" :class="importResults.success ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300'">
                        <p v-if="importResults.details.imported">Products imported: {{ importResults.details.imported }}</p>
                        <p v-if="importResults.details.failed">Failed: {{ importResults.details.failed }}</p>
                        <div v-if="importResults.details.errors && importResults.details.errors.length > 0" class="mt-2">
                            <p class="font-semibold">Errors:</p>
                            <ul class="list-disc list-inside">
                                <li v-for="(error, index) in importResults.details.errors.slice(0, 5)" :key="index">
                                    {{ error }}
                                </li>
                                <li v-if="importResults.details.errors.length > 5">
                                    ... and {{ importResults.details.errors.length - 5 }} more errors
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3">
                    <button
                        @click="closeImportModal"
                        class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-md transition-colors"
                    >
                        {{ importResults ? 'Close' : 'Cancel' }}
                    </button>
                    <button
                        v-if="!importResults"
                        @click="importCSV"
                        :disabled="!selectedFile || importing"
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors flex items-center gap-2"
                    >
                        <svg v-if="importing" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ importing ? 'Importing...' : 'Import Products' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Bulk Edit Modal -->
        <div
            v-if="showBulkEditModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            @click.self="closeBulkEditModal"
        >
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        Bulk Edit Products ({{ selectedProducts.length }})
                    </h2>
                    <button
                        @click="closeBulkEditModal"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="mb-4 p-3 bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded-md">
                    <p class="text-sm text-yellow-800 dark:text-yellow-200">
                        Only fields with a selection will be updated. Leave fields blank to keep existing values.
                    </p>
                </div>

                <div class="space-y-4">
                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Status
                        </label>
                        <select
                            v-model="bulkEditForm.is_active"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="">-- No Change --</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Category
                        </label>
                        <select
                            v-model="bulkEditForm.category_id"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="">-- No Change --</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3 mt-6">
                    <button
                        @click="closeBulkEditModal"
                        class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-md transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        @click="submitBulkEdit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
                    >
                        Update {{ selectedProducts.length }} Product{{ selectedProducts.length > 1 ? 's' : '' }}
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const page = usePage();

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

// CSV Import state
const showImportModal = ref(false);
const selectedFile = ref(null);
const importing = ref(false);
const importResults = ref(null);
const fileInput = ref(null);

// Multi-select state
const selectedProducts = ref([]);
const showBulkEditModal = ref(false);
const bulkEditForm = ref({
    is_active: '',
    category_id: '',
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

// CSV Import methods
const handleFileSelect = (event) => {
    const file = event.target.files[0];
    if (file && file.type === 'text/csv') {
        selectedFile.value = file;
    } else {
        alert('Please select a valid CSV file.');
        event.target.value = '';
        selectedFile.value = null;
    }
};

const getCsrfToken = () => {
    // Try to get CSRF token from meta tag
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    if (metaTag) {
        return metaTag.getAttribute('content');
    }

    // Try to get from cookie (Laravel default is XSRF-TOKEN)
    const cookies = document.cookie.split(';');
    for (let cookie of cookies) {
        const [name, value] = cookie.trim().split('=');
        if (name === 'XSRF-TOKEN') {
            return decodeURIComponent(value);
        }
    }

    return null;
};

const importCSV = async () => {
    if (!selectedFile.value) {
        return;
    }

    importing.value = true;
    importResults.value = null;

    const formData = new FormData();
    formData.append('csv_file', selectedFile.value);

    const csrfToken = getCsrfToken();

    try {
        const headers = {
            'Accept': 'application/json',
        };

        // Add CSRF token to headers if available
        if (csrfToken) {
            headers['X-CSRF-TOKEN'] = csrfToken;
        }

        const response = await fetch(route('products.import'), {
            method: 'POST',
            body: formData,
            headers: headers,
            credentials: 'same-origin',
        });

        const data = await response.json();

        if (response.ok) {
            importResults.value = {
                success: true,
                message: data.message || 'Products imported successfully!',
                details: {
                    imported: data.imported || 0,
                    failed: data.failed || 0,
                    errors: data.errors || [],
                },
            };

            // Refresh the products list after successful import
            setTimeout(() => {
                router.reload({ only: ['products'] });
            }, 2000);
        } else {
            importResults.value = {
                success: false,
                message: data.message || 'Import failed. Please check your CSV file.',
                details: {
                    errors: data.errors || [],
                },
            };
        }
    } catch (error) {
        console.error('Import error:', error);
        importResults.value = {
            success: false,
            message: 'An error occurred during import. Please try again.',
            details: {
                errors: [error.message],
            },
        };
    } finally {
        importing.value = false;
    }
};

const closeImportModal = () => {
    showImportModal.value = false;
    selectedFile.value = null;
    importResults.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

// Multi-select methods
const toggleProductSelection = (productId) => {
    const index = selectedProducts.value.indexOf(productId);
    if (index > -1) {
        selectedProducts.value.splice(index, 1);
    } else {
        selectedProducts.value.push(productId);
    }
};

const toggleSelectAll = () => {
    if (selectedProducts.value.length === props.products.data.length) {
        selectedProducts.value = [];
    } else {
        selectedProducts.value = props.products.data.map(p => p.id);
    }
};

const isProductSelected = (productId) => {
    return selectedProducts.value.includes(productId);
};

const bulkDelete = () => {
    if (selectedProducts.value.length === 0) return;

    const count = selectedProducts.value.length;
    if (confirm(`Are you sure you want to delete ${count} product${count > 1 ? 's' : ''}?`)) {
        router.delete(route('products.bulk-delete'), {
            data: { product_ids: selectedProducts.value },
            onSuccess: () => {
                selectedProducts.value = [];
            },
        });
    }
};

const openBulkEdit = () => {
    bulkEditForm.value = {
        is_active: '',
        category_id: '',
    };
    showBulkEditModal.value = true;
};

const closeBulkEditModal = () => {
    showBulkEditModal.value = false;
    bulkEditForm.value = {
        is_active: '',
        category_id: '',
    };
};

const submitBulkEdit = () => {
    if (selectedProducts.value.length === 0) return;

    router.put(route('products.bulk-update'), {
        product_ids: selectedProducts.value,
        ...bulkEditForm.value,
    }, {
        onSuccess: () => {
            closeBulkEditModal();
            selectedProducts.value = [];
        },
    });
};
</script>

<style scoped>
@keyframes progress {
    0% {
        width: 0%;
    }
    50% {
        width: 70%;
    }
    100% {
        width: 100%;
    }
}

.animate-progress {
    animation: progress 2s ease-in-out infinite;
}
</style>
