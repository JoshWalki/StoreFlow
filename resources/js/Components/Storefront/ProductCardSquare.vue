<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useCart } from '@/Composables/useCart';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
    store: {
        type: Object,
        required: true,
    },
});

const { addToCart } = useCart();
const isAdding = ref(false);
const justAdded = ref(false);

const formatCurrency = (cents) => {
    if (cents === null || cents === undefined) return '$0.00';
    return `$${(cents / 100).toFixed(2)}`;
};

const productUrl = `/store/${props.store.id}/products/${props.product.id}`;

const handleAddToCart = () => {
    if (isAdding.value) return;

    isAdding.value = true;
    justAdded.value = true;

    addToCart(props.product);

    setTimeout(() => {
        isAdding.value = false;
    }, 300);

    setTimeout(() => {
        justAdded.value = false;
    }, 2000);
};
</script>

<template>
    <Link
        :href="productUrl"
        :class="[
            'product-card-square group rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-300 border block',
            store.theme === 'bold'
                ? 'bg-gray-900 border-gray-900'
                : store.theme === 'monochrome'
                ? 'bg-white border-gray-100'
                : 'bg-white border-gray-200'
        ]"
    >
        <div class="flex flex-col h-full">
            <!-- Product Image (Square aspect ratio) -->
            <div class="relative w-full pt-[100%]">
                <!-- Image container with absolute positioning to maintain square -->
                <div class="absolute inset-0">
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
                        class="w-full h-full flex items-center justify-center"
                        :class="store.theme === 'bold' ? 'bg-gray-700' : 'bg-gray-200'"
                    >
                        <svg
                            class="w-16 h-16"
                            :class="store.theme === 'bold' ? 'text-gray-600' : 'text-gray-400'"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>

                    <!-- Circular + Button -->
                    <button
                        @click.prevent.stop="handleAddToCart"
                        :class="[
                            'absolute bottom-2 right-2 w-9 h-9 rounded-full shadow-lg flex items-center justify-center transition-all duration-300 border-2 z-10',
                            justAdded
                                ? 'bg-green-500 border-green-600 scale-110'
                                : 'bg-white border-gray-200 hover:bg-gray-50 hover:scale-105'
                        ]"
                        :disabled="isAdding"
                        title="Add to cart"
                    >
                        <!-- Plus Icon (default) -->
                        <svg
                            v-if="!justAdded"
                            class="w-5 h-5 text-gray-900 transition-transform"
                            :class="{ 'scale-0': isAdding }"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>

                        <!-- Checkmark Icon (after adding) -->
                        <svg
                            v-else
                            class="w-5 h-5 text-white animate-scale-in"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Product Info -->
            <div class="p-3 flex flex-col justify-between flex-1">
                <!-- Product Name -->
                <h3
                    class="text-sm font-bold mb-1"
                    :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'"
                >
                    {{ product.name }}
                </h3>

                <!-- Price -->
                <div class="flex items-center justify-between mt-auto">
                    <span
                        class="text-sm font-semibold"
                        :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'"
                    >{{ formatCurrency(product.price_cents) }}</span>
                </div>
            </div>
        </div>
    </Link>
</template>

<style scoped>
@keyframes scale-in {
    0% {
        transform: scale(0) rotate(0deg);
        opacity: 0;
    }
    50% {
        transform: scale(1.2) rotate(180deg);
    }
    100% {
        transform: scale(1) rotate(360deg);
        opacity: 1;
    }
}

.animate-scale-in {
    animation: scale-in 0.3s ease-out;
}

.product-card-square:hover .absolute button:not(:disabled) {
    transform: scale(1.05);
}

.absolute button {
    transition: transform 0.2s ease, background-color 0.3s ease, border-color 0.3s ease;
}

.absolute button:disabled {
    cursor: not-allowed;
}
</style>
