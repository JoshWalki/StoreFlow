<template>
    <div class="space-y-6">
        <!-- Header with Start New Migration Button -->
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">
                    Data Migration
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Import menu data from UberEats, DoorDash, and other platforms
                </p>
            </div>
            <button
                @click="showNewMigrationForm = true"
                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 flex items-center gap-2"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Start New Migration
            </button>
        </div>

        <!-- New Migration Form -->
        <div v-if="showNewMigrationForm" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-md font-semibold text-gray-800 dark:text-white mb-4">
                Start New Migration
            </h3>
            <form @submit.prevent="startMigration" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Platform <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="migrationForm.platform"
                        required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    >
                        <option value="">Select Platform</option>
                        <option value="ubereats">UberEats</option>
                        <option value="doordash">DoorDash</option>
                        <option value="menulog">Menulog</option>
                        <option value="deliveroo">Deliveroo</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Restaurant URL <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="migrationForm.url"
                        type="url"
                        required
                        placeholder="https://www.ubereats.com/au/store/..."
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    />
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Enter the full URL to your restaurant's menu page
                    </p>
                </div>
                <div class="flex gap-2">
                    <button
                        type="submit"
                        :disabled="isStarting"
                        class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ isStarting ? 'Starting...' : 'Start Migration' }}
                    </button>
                    <button
                        type="button"
                        @click="showNewMigrationForm = false"
                        class="bg-gray-300 dark:bg-gray-600 text-gray-800 dark:text-white px-6 py-2 rounded-md hover:bg-gray-400 dark:hover:bg-gray-500"
                    >
                        Cancel
                    </button>
                </div>
                <div v-if="startError" class="text-red-600 text-sm">
                    {{ startError }}
                </div>
            </form>
        </div>

        <!-- Migrations List -->
        <div v-if="migrations.length === 0 && !showNewMigrationForm" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                No migrations yet
            </h3>
            <p class="text-gray-600 dark:text-gray-400">
                Start your first migration to import menu data from delivery platforms
            </p>
        </div>

        <div v-else class="space-y-4">
            <div
                v-for="migration in migrations"
                :key="migration.id"
                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700"
            >
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <div class="flex items-center gap-3">
                            <h3 class="text-md font-semibold text-gray-800 dark:text-white">
                                {{ platformName(migration.platform) }}
                            </h3>
                            <span
                                :class="statusClass(migration.status)"
                                class="px-2 py-1 text-xs font-medium rounded-full"
                            >
                                {{ statusText(migration.status) }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            {{ formatDate(migration.created_at) }}
                        </p>
                    </div>
                    <button
                        v-if="migration.status === 'failed' || migration.status === 'completed'"
                        @click="deleteMigration(migration.id)"
                        class="text-red-600 hover:text-red-700 text-sm"
                    >
                        Delete
                    </button>
                </div>

                <div v-if="migration.status === 'pending' || migration.status === 'scraping'" class="mt-4">
                    <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Processing... This may take 1-2 minutes
                    </div>
                </div>

                <div v-if="migration.status === 'preview'" class="mt-4 space-y-4">
                    <div class="grid grid-cols-3 gap-4 text-sm">
                        <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded">
                            <div class="text-gray-600 dark:text-gray-400">Products Found</div>
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ migration.products_found || 0 }}
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded">
                            <div class="text-gray-600 dark:text-gray-400">Categories</div>
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ migration.categories_found || 0 }}
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded">
                            <div class="text-gray-600 dark:text-gray-400">Status</div>
                            <div class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                Ready to Import
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button
                            @click="viewPreview(migration.id)"
                            class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700"
                        >
                            View Products
                        </button>
                        <button
                            @click="importMigration(migration.id)"
                            :disabled="isImporting[migration.id]"
                            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 disabled:opacity-50"
                        >
                            {{ isImporting[migration.id] ? 'Importing...' : 'Import Products' }}
                        </button>
                    </div>
                </div>

                <div v-if="migration.status === 'importing'" class="mt-4">
                    <div class="flex items-center gap-2 text-sm text-green-600 dark:text-green-400">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Importing products into your catalog...
                    </div>
                </div>

                <div v-if="migration.status === 'completed'" class="mt-4">
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <div>
                                <h4 class="font-medium text-green-800 dark:text-green-200">
                                    Import Completed Successfully
                                </h4>
                                <p class="text-sm text-green-700 dark:text-green-300 mt-1">
                                    Imported {{ migration.products_imported || 0 }} products
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="migration.status === 'failed'" class="mt-4">
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                            <div>
                                <h4 class="font-medium text-red-800 dark:text-red-200">
                                    Migration Failed
                                </h4>
                                <p class="text-sm text-red-700 dark:text-red-300 mt-1">
                                    {{ migration.error_message || 'An error occurred during migration' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Modal -->
        <div v-if="showPreview && previewMigration" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-6xl w-full max-h-[90vh] flex flex-col">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Preview Products
                    </h3>
                    <button
                        @click="showPreview = false"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="p-6 overflow-y-auto flex-1">
                    <div v-if="previewProducts.length === 0" class="text-center py-12">
                        <p class="text-gray-600 dark:text-gray-400">
                            No products found in this migration. The scraper may need platform-specific tuning.
                        </p>
                    </div>
                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div
                            v-for="product in previewProducts"
                            :key="product.id"
                            class="border border-gray-200 dark:border-gray-700 rounded-lg p-4"
                        >
                            <img
                                v-if="product.image_url"
                                :src="product.image_url"
                                :alt="product.name"
                                class="w-full h-40 object-cover rounded-lg mb-3"
                            />
                            <div class="h-40 bg-gray-100 dark:bg-gray-700 rounded-lg mb-3 flex items-center justify-center" v-else>
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h4 class="font-medium text-gray-900 dark:text-white mb-1">
                                {{ product.name }}
                            </h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2 line-clamp-2">
                                {{ product.description }}
                            </p>
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-bold text-gray-900 dark:text-white">
                                    {{ product.formatted_price }}
                                </span>
                                <span class="text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                                    {{ product.category }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-6 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2">
                    <button
                        @click="showPreview = false"
                        class="bg-gray-300 dark:bg-gray-600 text-gray-800 dark:text-white px-6 py-2 rounded-md hover:bg-gray-400 dark:hover:bg-gray-500"
                    >
                        Close
                    </button>
                    <button
                        @click="importFromPreview"
                        :disabled="previewProducts.length === 0"
                        class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 disabled:opacity-50"
                    >
                        Import {{ previewProducts.length }} Products
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    store: Object,
});

const migrations = ref([]);
const showNewMigrationForm = ref(false);
const isStarting = ref(false);
const startError = ref(null);
const isImporting = ref({});
const showPreview = ref(false);
const previewMigration = ref(null);
const previewProducts = ref([]);

const migrationForm = ref({
    platform: '',
    url: '',
});

let pollingInterval = null;

onMounted(() => {
    loadMigrations();
    // Poll for updates every 5 seconds
    pollingInterval = setInterval(loadMigrations, 5000);
});

onUnmounted(() => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
});

const loadMigrations = async () => {
    try {
        const response = await axios.get(`/api/stores/${props.store.id}/migrations`);
        migrations.value = response.data.migrations;
    } catch (error) {
        console.error('Failed to load migrations:', error);
    }
};

const startMigration = async () => {
    isStarting.value = true;
    startError.value = null;

    try {
        await axios.post(`/api/stores/${props.store.id}/migrations`, migrationForm.value);
        migrationForm.value = { platform: '', url: '' };
        showNewMigrationForm.value = false;
        await loadMigrations();
    } catch (error) {
        startError.value = error.response?.data?.message || 'Failed to start migration';
    } finally {
        isStarting.value = false;
    }
};

const importMigration = async (migrationId) => {
    isImporting.value[migrationId] = true;

    try {
        await axios.post(`/api/migrations/${migrationId}/import`);
        await loadMigrations();
    } catch (error) {
        alert(error.response?.data?.message || 'Failed to start import');
    } finally {
        isImporting.value[migrationId] = false;
    }
};

const viewPreview = async (migrationId) => {
    try {
        const response = await axios.get(`/api/migrations/${migrationId}/preview`);
        previewMigration.value = response.data.migration;
        previewProducts.value = response.data.products;
        showPreview.value = true;
    } catch (error) {
        alert('Failed to load preview');
    }
};

const importFromPreview = async () => {
    if (previewMigration.value) {
        showPreview.value = false;
        await importMigration(previewMigration.value.id);
    }
};

const deleteMigration = async (migrationId) => {
    if (!confirm('Are you sure you want to delete this migration?')) return;

    try {
        await axios.delete(`/api/migrations/${migrationId}`);
        await loadMigrations();
    } catch (error) {
        alert('Failed to delete migration');
    }
};

const platformName = (platform) => {
    const names = {
        ubereats: 'UberEats',
        doordash: 'DoorDash',
        menulog: 'Menulog',
        deliveroo: 'Deliveroo',
    };
    return names[platform] || platform;
};

const statusText = (status) => {
    const texts = {
        pending: 'Queued',
        scraping: 'Scraping',
        preview: 'Ready',
        importing: 'Importing',
        completed: 'Completed',
        failed: 'Failed',
    };
    return texts[status] || status;
};

const statusClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        scraping: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        preview: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
        importing: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        completed: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        failed: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    };
    return classes[status] || '';
};

const formatDate = (date) => {
    return new Date(date).toLocaleString();
};
</script>
