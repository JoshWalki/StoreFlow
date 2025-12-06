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
    showQuickAdd: {
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
        class="product-card-ubereats flex-shrink-0 w-full sm:w-[420px] h-40 flex rounded-xl overflow-hidden shadow-md hover:shadow-2xl transition-all duration-300 group bg-white"
    >
        <!-- Product Image Section (Left Side - 40% width) -->
        <div class="relative w-40 h-full overflow-hidden bg-gray-100 flex-shrink-0">
            <!-- Image -->
            <img
                v-if="product.image"
                :src="product.image"
                :alt="product.name"
                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                loading="lazy"
            />
            <!-- Placeholder if no image -->
            <div
                v-else
                class="w-full h-full flex items-center justify-center text-gray-400"
            >
                <svg
                    class="w-20 h-20"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.5"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                    />
                </svg>
            </div>

        </div>

        <!-- Product Info Section (Right Side - 60% width) -->
        <div class="flex-1 p-4 bg-white flex flex-col justify-between">
            <!-- Top Row: Category and Badges -->
            <div class="flex items-start justify-between mb-2">
                <!-- Category Tag -->
                <span v-if="product.category" class="text-xs text-gray-500 uppercase tracking-wide">
                    {{ product.category }}
                </span>

                <!-- Delivery/Pickup Badges (compact) -->
                <div class="flex gap-1">
                    <span
                        v-if="product.is_shippable"
                        class="inline-flex items-center gap-0.5 bg-green-100 text-green-700 text-xs font-medium px-1.5 py-0.5 rounded"
                        title="Delivery available"
                    >
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                            <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z" />
                        </svg>
                    </span>
                    <span
                        class="inline-flex items-center gap-0.5 bg-orange-100 text-orange-700 text-xs font-medium px-1.5 py-0.5 rounded"
                        title="Pickup available"
                    >
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </div>
            </div>

            <!-- Product Name -->
            <h3 class="font-bold text-base text-gray-900 mb-1 line-clamp-2 leading-tight">
                {{ product.name }}
            </h3>

            <!-- Description (subtle, optional) -->
            <p v-if="product.description" class="text-sm text-gray-600 mb-3 line-clamp-2 flex-1">
                {{ product.description }}
            </p>

            <!-- Bottom Row: Price and Rating -->
            <div class="flex items-center justify-between mt-auto">
                <!-- Price -->
                <span class="text-xl font-bold text-gray-900">
                    {{ formatCurrency(product.price_cents) }}
                </span>

                <!-- Rating Placeholder -->
                <div class="flex items-center gap-1">
                    <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    <span class="text-sm font-semibold text-gray-700">4.5</span>
                </div>
            </div>
        </div>
    </Link>
</template>

<style scoped>
/* Custom smooth transform on hover */
.product-card-ubereats:hover {
    transform: translateY(-4px);
}

/* Ensure smooth scrolling for horizontal sections */
.product-card-ubereats {
    scroll-snap-align: start;
}
</style>
