<template>
    <div class="max-w-2xl">
            <div class="mb-6">
                <Link :href="route('categories.index')" class="text-blue-600 hover:text-blue-800">
                    ← Back to Categories
                </Link>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <h1 class="text-2xl font-bold text-gray-800 mb-6">Create Category</h1>

                <form @submit.prevent="submitForm" class="space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Description
                        </label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                        />
                        <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
                    </div>

                    <!-- Sort Order -->
                    <div>
                        <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">
                            Sort Order
                        </label>
                        <input
                            id="sort_order"
                            v-model.number="form.sort_order"
                            type="number"
                            min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                        />
                        <p v-if="form.errors.sort_order" class="mt-1 text-sm text-red-600">{{ form.errors.sort_order }}</p>
                    </div>

                    <!-- Active Status -->
                    <div class="flex items-center">
                        <input
                            id="is_active"
                            v-model="form.is_active"
                            type="checkbox"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        />
                        <label for="is_active" class="ml-2 block text-sm text-gray-700">
                            Active
                        </label>
                    </div>

                    <!-- Product Assignment -->
                    <div v-if="products && products.length > 0" class="border-t pt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Assign Products to Category</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Select products to assign to this category. Products showing their current category (if any).
                        </p>

                        <div class="max-h-96 overflow-y-auto border border-gray-200 rounded-md">
                            <div class="divide-y divide-gray-200">
                                <div
                                    v-for="product in products"
                                    :key="product.id"
                                    class="flex items-center p-3 hover:bg-gray-50"
                                >
                                    <input
                                        :id="`product-${product.id}`"
                                        v-model="form.product_ids"
                                        type="checkbox"
                                        :value="product.id"
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded flex-shrink-0"
                                    />
                                    <label :for="`product-${product.id}`" class="ml-3 flex-1 flex items-center gap-3 cursor-pointer">
                                        <!-- Product Image -->
                                        <div class="w-12 h-12 flex-shrink-0 bg-gray-100 rounded overflow-hidden">
                                            <img
                                                v-if="product.images && product.images.length > 0"
                                                :src="`/storage/${product.images[0].image_path}`"
                                                :alt="product.name"
                                                class="w-full h-full object-cover"
                                            />
                                            <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        </div>

                                        <!-- Product Info -->
                                        <div class="flex-1 flex items-center justify-between min-w-0">
                                            <span class="text-sm font-medium text-gray-900 truncate">{{ product.name }}</span>
                                            <span v-if="product.category" class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded flex-shrink-0 ml-2">
                                                Current: {{ product.category.name }}
                                            </span>
                                            <span v-else class="text-xs text-gray-400 italic flex-shrink-0 ml-2">
                                                No category
                                            </span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <p v-if="form.product_ids.length > 0" class="mt-2 text-sm text-gray-600">
                            {{ form.product_ids.length }} product(s) selected
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end space-x-3">
                        <Link
                            :href="route('categories.index')"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Creating...' : 'Create Category' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';

defineProps({
    store: Object,
    user: Object,
    products: Array,
});

const form = useForm({
    name: '',
    description: '',
    sort_order: 0,
    is_active: true,
    product_ids: [],
});

const submitForm = () => {
    form.post(route('categories.store'));
};
</script>
