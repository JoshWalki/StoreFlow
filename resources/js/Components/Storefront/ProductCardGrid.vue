<script setup>
import { computed, ref } from 'vue';
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
    showPopular: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['productClick']);

const { addToCart } = useCart();
const isAdding = ref(false);
const justAdded = ref(false);

const formatCurrency = (cents) => {
    if (cents === null || cents === undefined) return '$0.00';
    return `$${(cents / 100).toFixed(2)}`;
};

const handleAddToCart = () => {
    if (isAdding.value) return;

    isAdding.value = true;
    justAdded.value = true;

    // Add to cart - pass the full product object
    addToCart(props.product);

    // Reset animation after delay
    setTimeout(() => {
        isAdding.value = false;
    }, 300);

    setTimeout(() => {
        justAdded.value = false;
    }, 2000);
};

const handleProductClick = () => {
    emit('productClick', props.product.id);
};

// Tilt effect state
const rotateX = ref(0);
const rotateY = ref(0);
const isHovered = ref(false);

const cardStyle = computed(() => {
    if (!isHovered.value) return {};
    return {
        transform: `perspective(1000px) rotateX(${rotateX.value}deg) rotateY(${rotateY.value}deg) scale(1.02)`,
    };
});

const handleMouseEnter = () => {
    isHovered.value = true;
};

const handleMouseMove = (e) => {
    if (!isHovered.value) return;

    const card = e.currentTarget;
    const rect = card.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    const centerX = rect.width / 2;
    const centerY = rect.height / 2;

    const rotateXValue = ((y - centerY) / centerY) * -8;
    const rotateYValue = ((x - centerX) / centerX) * 8;

    rotateX.value = rotateXValue;
    rotateY.value = rotateYValue;
};

const handleMouseLeave = () => {
    isHovered.value = false;
    rotateX.value = 0;
    rotateY.value = 0;
};
</script>

<template>
    <button
        @click="handleProductClick"
        :class="[
            'product-card-grid rounded-lg overflow-hidden hover:shadow-glow transition-all duration-500 border block relative w-full text-left cursor-pointer',
            showPopular ? 'h-auto md:h-[146px]' : 'h-[146px]',
            store.theme === 'bold'
                ? 'bg-gray-900 border-gray-900'
                : store.theme === 'monochrome'
                ? 'bg-white border-gray-100'
                : 'bg-white border-gray-200'
        ]"
        @mouseenter="handleMouseEnter"
        @mousemove="handleMouseMove"
        @mouseleave="handleMouseLeave"
        :style="cardStyle"
    >
        <!-- Mobile Layout (Vertical) - Only for Popular items -->
        <div v-if="showPopular" class="flex flex-col md:hidden">
            <!-- Product Image (Top on mobile) -->
            <div v-if="product.image" class="relative w-full h-52">
                <img
                    :src="product.image"
                    :alt="product.name"
                    class="w-full h-full object-cover"
                    loading="lazy"
                />

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

            <!-- Product Info (Bottom on mobile) -->
            <div class="p-3 flex-1 flex flex-col justify-between">
                <!-- Product Name -->
                <h3
                    class="text-base font-medium mb-1"
                    :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'"
                >
                    {{ product.name }}
                </h3>

                <!-- Price and Badge Row -->
                <div class="flex items-center justify-between">
                    <div class="flex flex-col">
                        <span
                            class="text-sm font-semibold"
                            :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'"
                        >{{ formatCurrency(product.price_cents) }}</span>
                        <div class="mt-1">
                            <span class="floating-badge inline-block bg-orange-100 text-orange-700 text-xs font-semibold px-1.5 py-0.5 rounded">
                                ⭐ Popular
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Horizontal Layout (For regular items on mobile AND all items on desktop) -->
        <div :class="showPopular ? 'hidden md:flex h-full' : 'flex h-full'">
            <!-- Left Side: Product Info (65%) -->
            <div class="flex-1 p-3 flex flex-col justify-between">
                <!-- Product Name and Price Row -->
                <div class="flex items-center justify-between gap-2 mb-1">
                    <h3
                        class="text-base font-medium transition-colors flex-1"
                        :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900 group-hover:text-blue-600'"
                    >
                        {{ product.name }}
                    </h3>
                    <span
                        class="text-sm font-semibold flex-shrink-0"
                        :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'"
                    >{{ formatCurrency(product.price_cents) }}</span>
                </div>

                <!-- Description -->
                <p
                    class="text-xs line-clamp-2 flex-1"
                    :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-600'"
                >
                    {{ product.description || 'Delicious menu item prepared fresh.' }}
                </p>

                <!-- Popular Badge -->
                <div v-if="showPopular" class="mt-1">
                    <span class="floating-badge inline-block bg-orange-100 text-orange-700 text-xs font-semibold px-1.5 py-0.5 rounded">
                        ⭐ Popular
                    </span>
                </div>
            </div>

            <!-- Right Side: Product Image (35%) -->
            <div v-if="product.image" class="w-[146px] sm:w-[166px] relative flex-shrink-0">
                <img
                    :src="product.image"
                    :alt="product.name"
                    class="w-full h-full object-cover"
                    loading="lazy"
                />

                <!-- Circular + Button -->
                <button
                    @click.prevent.stop="handleAddToCart"
                    :class="[
                        'absolute bottom-2 right-2 w-8 h-8 rounded-full shadow-md flex items-center justify-center transition-all duration-300 border-2 z-10',
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
                        class="w-4 h-4 text-gray-900 transition-transform"
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
                        class="w-4 h-4 text-white animate-scale-in"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </button>
            </div>
        </div>
    </button>
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

/* 3D Tilt and Glow Effect */
.product-card-grid {
    transition: transform 0.5s cubic-bezier(0.23, 1, 0.32, 1), box-shadow 0.5s ease;
    transform-style: preserve-3d;
    will-change: transform;
}

/* Animated Glow Shadow */
.shadow-glow {
    box-shadow:
        0 10px 30px -10px rgba(99, 102, 241, 0.3),
        0 20px 60px -15px rgba(139, 92, 246, 0.2),
        0 0 0 1px rgba(99, 102, 241, 0.1);
}

/* Pulsing glow animation */
@keyframes glow-pulse {
    0%, 100% {
        box-shadow:
            0 10px 30px -10px rgba(99, 102, 241, 0.3),
            0 20px 60px -15px rgba(139, 92, 246, 0.2),
            0 0 0 1px rgba(99, 102, 241, 0.1);
    }
    50% {
        box-shadow:
            0 15px 40px -10px rgba(99, 102, 241, 0.5),
            0 25px 70px -15px rgba(139, 92, 246, 0.4),
            0 0 0 1px rgba(99, 102, 241, 0.2);
    }
}

.product-card-grid:hover {
    animation: glow-pulse 2s ease-in-out infinite;
}

.product-card-grid:hover .absolute button:not(:disabled) {
    transform: scale(1.05);
}

.absolute button {
    transition: transform 0.2s ease, background-color 0.3s ease, border-color 0.3s ease;
}

.absolute button:disabled {
    cursor: not-allowed;
}

/* Floating Badge Animation */
@keyframes float-badge {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-4px);
    }
}

@keyframes pulse-subtle {
    0%, 100% {
        opacity: 0.9;
    }
    50% {
        opacity: 1;
    }
}

.floating-badge {
    animation: float-badge 3s ease-in-out infinite, pulse-subtle 2s ease-in-out infinite;
    display: inline-block;
}
</style>
