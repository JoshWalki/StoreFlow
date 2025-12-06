<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
    store: {
        type: Object,
        required: true,
    },
    showPopular: {
        type: Boolean,
        default: false,
    },
});

const formatCurrency = (cents) => {
    if (cents === null || cents === undefined) return '$0.00';
    return `$${(cents / 100).toFixed(2)}`;
};

const productUrl = computed(() => {
    return `/store/${props.store.id}/products/${props.product.id}`;
});
</script>

<template>
    <Link
        :href="productUrl"
        class="product-card-grid bg-white rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-300 border border-gray-200 block"
    >
        <div class="flex h-full">
            <!-- Left Side: Product Info (60%) -->
            <div class="flex-1 p-5 flex flex-col">
                <!-- Product Name -->
                <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors">
                    {{ product.name }}
                </h3>

                <!-- Price Row -->
                <div class="flex items-center gap-2 mb-2 text-sm">
                    <span class="font-semibold text-gray-900">{{ formatCurrency(product.price_cents) }}</span>
                </div>

                <!-- Description -->
                <p class="text-sm text-gray-600 line-clamp-2 mb-3 flex-1">
                    {{ product.description || 'Delicious menu item prepared fresh.' }}
                </p>

                <!-- Popular Badge -->
                <div v-if="showPopular" class="mt-auto">
                    <span class="inline-block bg-orange-100 text-orange-700 text-xs font-semibold px-2 py-1 rounded">
                        Popular
                    </span>
                </div>
            </div>

            <!-- Right Side: Product Image (40%) -->
            <div class="w-40 sm:w-48 relative flex-shrink-0">
                <!-- Image -->
                <img
                    v-if="product.image"
                    :src="product.image"
                    :alt="product.name"
                    class="w-full h-full object-cover"
                    loading="lazy"
                />
                <!-- Placeholder -->
                <div
                    v-else
                    class="w-full h-full bg-gray-200 flex items-center justify-center"
                >
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>

                <!-- Circular + Button -->
                <button
                    @click.prevent.stop
                    class="absolute bottom-3 right-3 w-10 h-10 bg-white rounded-full shadow-lg flex items-center justify-center hover:bg-gray-50 transition-colors border-2 border-gray-200 z-10"
                    title="Add to cart"
                >
                    <svg class="w-5 h-5 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </button>
            </div>
        </div>
    </Link>
</template>

<style scoped>
.product-card-grid:hover .absolute button {
    transform: scale(1.1);
}

.absolute button {
    transition: transform 0.2s ease;
}
</style>
