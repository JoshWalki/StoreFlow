<script setup>
import { Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

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

    const rotateXValue = ((y - centerY) / centerY) * -10;
    const rotateYValue = ((x - centerX) / centerX) * 10;

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
    <Link
        :href="productUrl"
        class="product-card-ubereats flex-shrink-0 w-full sm:w-[420px] h-40 flex rounded-xl overflow-hidden shadow-md hover:shadow-glow transition-all duration-500 group bg-white relative"
        @mouseenter="handleMouseEnter"
        @mousemove="handleMouseMove"
        @mouseleave="handleMouseLeave"
        :style="cardStyle"
    >
        <!-- Product Image Section (Left Side - 40% width) -->
        <div v-if="product.image" class="relative w-40 h-full overflow-hidden bg-gray-100 flex-shrink-0">
            <img
                :src="product.image"
                :alt="product.name"
                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                loading="lazy"
            />
        </div>

        <!-- Product Info Section (Right Side - 60% width) -->
        <div class="flex-1 p-4 bg-white flex flex-col justify-between">
            <!-- Top Row: Category and Badges -->
            <div class="flex items-start justify-between mb-2">
                <!-- Category Tag -->
                <span v-if="product.category" class="category-badge text-xs text-gray-500 uppercase tracking-wide">
                    {{ product.category }}
                </span>

                <!-- Delivery/Pickup Badges (compact) -->
                <div class="flex gap-1">
                    <!-- Show "Pickup Only" if shipping not available -->
                    <span
                        v-if="!store.shipping_enabled || !product.is_shippable"
                        class="inline-flex items-center gap-0.5 bg-orange-100 text-orange-700 text-xs font-medium px-1.5 py-0.5 rounded"
                        title="Pickup only"
                    >
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                        </svg>
                        Pickup Only
                    </span>

                    <!-- Show both badges when delivery is available -->
                    <template v-else>
                        <span
                            class="inline-flex items-center gap-0.5 bg-green-100 text-green-700 text-xs font-medium px-1.5 py-0.5 rounded"
                            title="Delivery available"
                        >
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                                <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z" />
                            </svg>
                            Delivery
                        </span>
                        <span
                            class="inline-flex items-center gap-0.5 bg-blue-100 text-blue-700 text-xs font-medium px-1.5 py-0.5 rounded"
                            title="Pickup available"
                        >
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                            </svg>
                            Pickup
                        </span>
                    </template>
                </div>
            </div>

            <!-- Product Name -->
            <h3 class="font-medium text-base text-gray-900 mb-1 leading-tight">
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
/* 3D Tilt and Glow Effect */
.product-card-ubereats {
    scroll-snap-align: start;
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

.product-card-ubereats:hover {
    animation: glow-pulse 2s ease-in-out infinite;
}

/* Floating Category Badge */
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
        opacity: 0.7;
    }
    50% {
        opacity: 1;
    }
}

.category-badge {
    animation: float-badge 3s ease-in-out infinite, pulse-subtle 2s ease-in-out infinite;
    display: inline-block;
}
</style>
