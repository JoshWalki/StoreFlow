<template>
    <button
        @click="toggleCart"
        class="relative inline-flex items-center p-2 text-gray-700 hover:text-indigo-600 transition-colors"
        aria-label="Shopping cart"
    >
        <!-- Cart Icon -->
        <svg
            class="h-6 w-6"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"
            />
        </svg>

        <!-- Cart Count Badge -->
        <span
            v-if="cartCount > 0"
            class="absolute -top-1 -right-1 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full"
        >
            {{ cartCount > 99 ? '99+' : cartCount }}
        </span>
    </button>
</template>

<script setup>
import { useCart } from '@/Composables/useCart';

const { cartCount, toggleCart } = useCart();
</script>
