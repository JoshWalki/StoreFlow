<template>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">
                    Sales & Discounts
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Create and manage product discounts and promotional sales
                </p>
            </div>
            <button
                @click="showForm = !showForm"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
            >
                {{ showForm ? 'Cancel' : '+ New Sale' }}
            </button>
        </div>

        <!-- Sale Form -->
        <div
            v-if="showForm"
            class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6 border border-gray-200 dark:border-gray-600"
        >
            <h3 class="text-md font-semibold text-gray-800 dark:text-white mb-4">
                {{ editingSale ? 'Edit Sale' : 'Create New Sale' }}
            </h3>

            <form @submit.prevent="submitForm" class="space-y-4">
                <!-- Sale Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Sale Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="saleForm.name"
                        type="text"
                        required
                        placeholder="e.g., Summer Sale, BOGO Friday"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    />
                </div>

                <!-- Sale Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Sale Type <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="saleForm.type"
                        required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    >
                        <option value="">Select sale type...</option>
                        <option value="price_discount">Price Discount ($)</option>
                        <option value="percent_discount">Percentage Discount (%)</option>
                        <option value="bogo_same">Buy 1 Get 1 Free (Same Product)</option>
                        <option value="bogo_different">Buy 1 Get 1 Free (Different Product)</option>
                    </select>
                </div>

                <!-- Discount Value -->
                <div v-if="saleForm.type === 'price_discount' || saleForm.type === 'percent_discount'">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Discount Value <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center">
                        <span v-if="saleForm.type === 'price_discount'" class="px-3 py-2 bg-gray-100 dark:bg-gray-600 border border-r-0 border-gray-300 dark:border-gray-600 rounded-l-md text-gray-700 dark:text-gray-300">
                            $
                        </span>
                        <input
                            v-model.number="saleForm.discount_value"
                            type="number"
                            :min="saleForm.type === 'percent_discount' ? 1 : 0.01"
                            :max="saleForm.type === 'percent_discount' ? 100 : undefined"
                            :step="saleForm.type === 'price_discount' ? '0.01' : '1'"
                            required
                            :placeholder="saleForm.type === 'percent_discount' ? 'Enter percentage (1-100)' : 'Enter amount'"
                            :class="[
                                'px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white',
                                saleForm.type === 'price_discount' ? 'rounded-r-md' : 'rounded-md w-full'
                            ]"
                        />
                        <span v-if="saleForm.type === 'percent_discount'" class="px-3 py-2 bg-gray-100 dark:bg-gray-600 border border-l-0 border-gray-300 dark:border-gray-600 rounded-r-md text-gray-700 dark:text-gray-300">
                            %
                        </span>
                    </div>
                    <p v-if="saleForm.type === 'price_discount'" class="mt-1 text-xs text-gray-500">
                        This amount will be converted to cents for storage
                    </p>
                </div>

                <!-- BOGO Different Product -->
                <div v-if="saleForm.type === 'bogo_different'">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Free Product <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="saleForm.bogo_product_id"
                        required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    >
                        <option value="">Select product to give free...</option>
                        <option
                            v-for="product in availableProducts"
                            :key="product.id"
                            :value="product.id"
                        >
                            {{ product.name }} ({{ product.price }})
                        </option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">
                        This product will be given free when a customer buys any product in this sale
                    </p>
                </div>

                <!-- Products Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Apply to Products <span class="text-red-500">*</span>
                    </label>
                    <div class="border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 max-h-48 overflow-y-auto p-3 space-y-2">
                        <div v-if="loading" class="text-center py-4 text-gray-500">
                            Loading products...
                        </div>
                        <div v-else-if="availableProducts.length === 0" class="text-center py-4 text-gray-500">
                            No available products. All products are already in sales.
                        </div>
                        <label
                            v-else
                            v-for="product in availableProducts"
                            :key="product.id"
                            class="flex items-center p-2 hover:bg-gray-100 dark:hover:bg-gray-600 rounded cursor-pointer"
                        >
                            <input
                                type="checkbox"
                                :value="product.id"
                                v-model="saleForm.product_ids"
                                class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                            />
                            <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">
                                {{ product.name }} <span class="text-gray-500">({{ product.price }})</span>
                            </span>
                        </label>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        Select one or more products. Each product can only be in one sale at a time.
                    </p>
                </div>

                <!-- Date Range -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Start Date (Optional)
                        </label>
                        <input
                            v-model="saleForm.starts_at"
                            type="datetime-local"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            End Date (Optional)
                        </label>
                        <input
                            v-model="saleForm.ends_at"
                            type="datetime-local"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                        />
                    </div>
                </div>

                <!-- Active Toggle -->
                <div class="flex items-center">
                    <input
                        type="checkbox"
                        v-model="saleForm.is_active"
                        id="sale-active"
                        class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                    />
                    <label for="sale-active" class="ml-3 text-sm text-gray-700 dark:text-gray-300">
                        Sale is active
                    </label>
                </div>

                <!-- Error Display -->
                <div v-if="formError" class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md">
                    <p class="text-sm text-red-600 dark:text-red-400">{{ formError }}</p>
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-600">
                    <button
                        type="button"
                        @click="cancelForm"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="submitting"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors disabled:opacity-50"
                    >
                        {{ submitting ? 'Saving...' : (editingSale ? 'Update Sale' : 'Create Sale') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Sales List -->
        <div class="space-y-4">
            <div v-if="loading && sales.length === 0" class="text-center py-8 text-gray-500">
                Loading sales...
            </div>
            <div v-else-if="sales.length === 0" class="text-center py-8 text-gray-500">
                <p>No sales created yet. Click "New Sale" to create your first discount!</p>
            </div>
            <div
                v-else
                v-for="sale in sales"
                :key="sale.id"
                class="bg-white dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600 hover:shadow-md transition-shadow"
            >
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                                {{ sale.name }}
                            </h3>
                            <span
                                :class="[
                                    'px-2 py-1 text-xs rounded-full',
                                    sale.is_active
                                        ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400'
                                        : 'bg-gray-100 dark:bg-gray-600 text-gray-700 dark:text-gray-300'
                                ]"
                            >
                                {{ sale.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ sale.discount_display }}
                        </p>
                        <div class="mt-2 flex items-center text-xs text-gray-500 dark:text-gray-400 space-x-4">
                            <span>
                                {{ sale.products_count }} {{ sale.products_count === 1 ? 'product' : 'products' }}
                            </span>
                            <span v-if="sale.starts_at">
                                Starts: {{ formatDate(sale.starts_at) }}
                            </span>
                            <span v-if="sale.ends_at">
                                Ends: {{ formatDate(sale.ends_at) }}
                            </span>
                        </div>
                    </div>
                    <div class="flex space-x-2 ml-4">
                        <button
                            @click="editSale(sale)"
                            class="px-3 py-1 text-sm text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded transition-colors"
                        >
                            Edit
                        </button>
                        <button
                            @click="deleteSale(sale)"
                            class="px-3 py-1 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded transition-colors"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const sales = ref([]);
const availableProducts = ref([]);
const showForm = ref(false);
const loading = ref(false);
const submitting = ref(false);
const formError = ref('');
const editingSale = ref(null);

const saleForm = ref({
    name: '',
    type: '',
    discount_value: null,
    bogo_product_id: null,
    product_ids: [],
    is_active: true,
    starts_at: '',
    ends_at: ''
});

const loadSales = async () => {
    try {
        loading.value = true;
        const response = await axios.get(route('sales.index'));
        sales.value = response.data.sales;
    } catch (error) {
        console.error('Failed to load sales:', error);
    } finally {
        loading.value = false;
    }
};

const loadAvailableProducts = async () => {
    try {
        loading.value = true;
        const params = editingSale.value ? { exclude_sale_id: editingSale.value.id } : {};
        const response = await axios.get(route('sales.products.available'), { params });
        availableProducts.value = response.data.products;
    } catch (error) {
        console.error('Failed to load products:', error);
    } finally {
        loading.value = false;
    }
};

const submitForm = async () => {
    try {
        submitting.value = true;
        formError.value = '';

        // Prepare data
        const data = {
            ...saleForm.value,
        };

        // Convert price discount to cents
        if (data.type === 'price_discount' && data.discount_value) {
            data.discount_value = Math.round(data.discount_value * 100);
        }

        if (editingSale.value) {
            await axios.put(route('sales.update', editingSale.value.id), data);
        } else {
            await axios.post(route('sales.store'), data);
        }

        await loadSales();
        cancelForm();
    } catch (error) {
        if (error.response?.data?.message) {
            formError.value = error.response.data.message;
        } else {
            formError.value = 'An error occurred while saving the sale.';
        }
        console.error('Failed to save sale:', error);
    } finally {
        submitting.value = false;
    }
};

const editSale = async (sale) => {
    editingSale.value = sale;
    showForm.value = true;

    // Load available products for this sale
    await loadAvailableProducts();

    // Convert cents to dollars for price discount
    const discountValue = sale.type === 'price_discount' && sale.discount_value
        ? sale.discount_value / 100
        : sale.discount_value;

    // Map existing products to their IDs
    const productIds = sale.products ? sale.products.map(product => product.id) : [];

    saleForm.value = {
        name: sale.name,
        type: sale.type,
        discount_value: discountValue,
        bogo_product_id: sale.bogo_product_id,
        product_ids: productIds,
        is_active: sale.is_active,
        starts_at: sale.starts_at ? new Date(sale.starts_at).toISOString().slice(0, 16) : '',
        ends_at: sale.ends_at ? new Date(sale.ends_at).toISOString().slice(0, 16) : ''
    };
};

const deleteSale = async (sale) => {
    if (!confirm(`Are you sure you want to delete "${sale.name}"?`)) {
        return;
    }

    try {
        await axios.delete(route('sales.destroy', sale.id));
        await loadSales();
    } catch (error) {
        console.error('Failed to delete sale:', error);
        alert('Failed to delete sale. Please try again.');
    }
};

const cancelForm = () => {
    showForm.value = false;
    editingSale.value = null;
    formError.value = '';
    saleForm.value = {
        name: '',
        type: '',
        discount_value: null,
        bogo_product_id: null,
        product_ids: [],
        is_active: true,
        starts_at: '',
        ends_at: ''
    };
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Watch form visibility to load products
watch(showForm, async (newValue) => {
    if (newValue && !editingSale.value) {
        await loadAvailableProducts();
    }
});

onMounted(() => {
    loadSales();
});
</script>
