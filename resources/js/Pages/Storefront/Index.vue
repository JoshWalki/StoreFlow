<template>
    <div class="min-h-screen" :class="themeConfig.background">
        <!-- Inactive Store Banner -->
        <div
            v-if="!store.is_active"
            class="bg-yellow-500 text-white py-3 px-4 text-center font-medium"
        >
            <div
                class="max-w-7xl mx-auto flex items-center justify-center gap-2"
            >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        fill-rule="evenodd"
                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd"
                    />
                </svg>
                <span>This store is currently not accepting orders.</span>
            </div>
        </div>

        <!-- Header -->
        <header
            :class="
                store.theme === 'bold'
                    ? 'bg-gray-900 border-b border-orange-500/20'
                    : 'bg-white shadow-sm'
            "
        >
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex justify-between items-center">
                    <!-- Store Logo or Name -->
                    <div class="flex items-center">
                        <img
                            v-if="store.logo_url"
                            :src="store.logo_url"
                            :alt="store.name + ' logo'"
                            class="max-h-16 max-w-xs object-contain"
                            style="max-height: 64px; max-width: 300px"
                        />
                        <h1
                            v-else
                            class="text-3xl font-bold"
                            :class="
                                store.theme === 'bold'
                                    ? 'text-white'
                                    : 'text-gray-900'
                            "
                        >
                            {{ store.name }}
                        </h1>
                    </div>
                    <div class="flex items-center gap-2">
                        <!-- Track Order Button -->
                        <a
                            href="/track"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-all duration-200"
                            :class="[
                                store.theme === 'bold'
                                    ? 'text-white hover:bg-gray-800 border border-gray-700'
                                    : store.theme === 'modern'
                                    ? 'text-purple-700 hover:bg-purple-50 border border-purple-200'
                                    : 'text-blue-700 hover:bg-blue-50 border border-blue-200'
                            ]"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            <span>Track Order</span>
                        </a>

                        <!-- Login/Profile Button -->
                        <a
                            v-if="!customer"
                            :href="`/store/${store.id}/login`"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-all duration-200"
                            :class="[
                                store.theme === 'bold'
                                    ? 'text-white hover:bg-gray-800 border border-gray-700'
                                    : store.theme === 'modern'
                                    ? 'text-purple-700 hover:bg-purple-50 border border-purple-200'
                                    : 'text-blue-700 hover:bg-blue-50 border border-blue-200'
                            ]"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            <span>Login</span>
                        </a>
                        <a
                            v-else
                            :href="`/store/${store.id}/profile`"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-all duration-200"
                            :class="[
                                store.theme === 'bold'
                                    ? 'bg-gradient-to-r from-orange-500 to-yellow-500 text-white hover:from-orange-600 hover:to-yellow-600 shadow-lg'
                                    : store.theme === 'modern'
                                    ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white hover:from-purple-700 hover:to-pink-700 shadow-lg'
                                    : 'bg-blue-600 text-white hover:bg-blue-700 shadow-lg'
                            ]"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span>{{ customer.first_name }}'s Profile</span>
                        </a>

                        <!-- Cart Button -->
                        <CartButton />
                    </div>
                </div>
            </div>
        </header>

        <!-- Cart Drawer -->
        <CartDrawer :store="store" />

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Welcome Section -->
            <div class="mb-12 text-center">
                <h2
                    class="text-4xl font-bold mb-4"
                    :class="
                        store.theme === 'bold' ? 'text-white' : 'text-gray-900'
                    "
                >
                    Welcome to {{ store.name }}
                </h2>
                <p
                    class="text-lg"
                    :class="
                        store.theme === 'bold'
                            ? 'text-gray-400'
                            : 'text-gray-600'
                    "
                >
                    Browse our latest products
                </p>
            </div>

            <!-- Products Grid -->
            <div v-if="products.length > 0">
                <h3 class="text-2xl font-bold text-gray-900 mb-6">
                    Featured Products
                </h3>
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
                >
                    <a
                        v-for="product in products"
                        :key="product.id"
                        :href="`/store/${store.id}/products/${product.id}`"
                        class="rounded-lg shadow-md transition-all duration-300 overflow-hidden group"
                        :class="[
                            themeConfig.productCard,
                            store.theme === 'modern' ? 'rounded-2xl' : '',
                        ]"
                    >
                        <!-- Product Image -->
                        <div class="aspect-square bg-gray-200 overflow-hidden">
                            <img
                                v-if="product.image"
                                :src="product.image"
                                :alt="product.name"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            />
                            <div
                                v-else
                                class="w-full h-full flex items-center justify-center text-gray-400"
                            >
                                <svg
                                    class="w-16 h-16"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                    />
                                </svg>
                            </div>
                        </div>

                        <!-- Product Info -->
                        <div class="p-4">
                            <h3
                                class="text-lg font-semibold text-gray-900 line-clamp-2 mb-2"
                            >
                                {{ product.name }}
                            </h3>

                            <!-- Fulfillment Badges -->
                            <div class="flex flex-wrap gap-1.5 mb-2">
                                <!-- Pickup Only (when delivery not available) -->
                                <span
                                    v-if="!product.is_shippable"
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800"
                                >
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Pickup Only
                                </span>

                                <!-- Both badges when delivery is available -->
                                <template v-else>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        Pickup Available
                                    </span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                        </svg>
                                        Delivery Available
                                    </span>
                                </template>
                            </div>

                            <p
                                class="text-sm mb-3 line-clamp-2"
                                :class="
                                    store.theme === 'bold'
                                        ? 'text-gray-400'
                                        : 'text-gray-600'
                                "
                            >
                                {{ product.description }}
                            </p>
                            <div class="flex justify-between items-center">
                                <span
                                    class="text-2xl font-bold"
                                    :class="themeConfig.productPrice"
                                    >{{
                                        formatCurrency(product.price_cents)
                                    }}</span
                                >
                                <span
                                    v-if="product.category"
                                    class="text-xs px-2 py-1 rounded"
                                    :class="
                                        store.theme === 'bold'
                                            ? 'text-gray-400 bg-gray-800'
                                            : 'text-gray-500 bg-gray-100'
                                    "
                                    >{{ product.category }}</span
                                >
                            </div>
                        </div>
                    </a>
                </div>

                <!-- View All Products Link -->
                <div class="mt-12 text-center">
                    <a
                        :href="`/store/${store.id}/products`"
                        class="inline-block px-8 py-3 font-semibold rounded-lg transition-all duration-300 shadow-lg hover:shadow-xl"
                        :class="themeConfig.buttonPrimary"
                    >
                        View All Products
                    </a>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12">
                <svg
                    class="mx-auto h-24 w-24 text-gray-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
                    />
                </svg>
                <h3 class="mt-4 text-xl font-medium text-gray-900">
                    No products available
                </h3>
                <p class="mt-2 text-gray-500">
                    Check back soon for new products!
                </p>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t mt-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <p class="text-center text-gray-500">
                    &copy; {{ new Date().getFullYear() }} {{ store.name }}. All
                    rights reserved.
                </p>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { useTheme } from "@/Composables/useTheme";
import CartButton from "@/Components/Storefront/CartButton.vue";
import CartDrawer from "@/Components/Storefront/CartDrawer.vue";

const props = defineProps({
    store: {
        type: Object,
        required: true,
    },
    products: {
        type: Array,
        required: true,
    },
    customer: {
        type: Object,
        default: null,
    },
});

// Initialize theme
const { config: themeConfig } = useTheme(props.store.theme);

const formatCurrency = (cents) => {
    return new Intl.NumberFormat("en-AU", {
        style: "currency",
        currency: "AUD",
    }).format(cents / 100);
};
</script>
