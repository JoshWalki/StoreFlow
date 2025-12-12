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
                    <div>
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
                    </div>
                    <div class="flex items-center gap-1.5 sm:gap-2">
                        <!-- Track Order Button -->
                        <a
                            href="/track"
                            class="inline-flex items-center gap-1.5 px-2.5 py-2 sm:px-4 sm:py-2 rounded-lg font-medium transition-all duration-200"
                            :class="[
                                store.theme === 'bold'
                                    ? 'text-white hover:bg-gray-800 border border-gray-700'
                                    : store.theme === 'modern'
                                    ? 'text-purple-700 hover:bg-purple-50 border border-purple-200'
                                    : store.theme === 'monochrome'
                                    ? 'text-gray-900 hover:bg-gray-50 border border-gray-200'
                                    : 'text-blue-700 hover:bg-blue-50 border border-blue-200',
                            ]"
                            title="Track Order"
                        >
                            <svg
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"
                                />
                            </svg>
                            <span class="hidden md:inline">Track Order</span>
                        </a>

                        <!-- Login/Profile Button -->
                        <a
                            v-if="!customer"
                            :href="`/store/${store.id}/login`"
                            class="inline-flex items-center gap-1.5 px-2.5 py-2 sm:px-4 sm:py-2 rounded-lg font-medium transition-all duration-200"
                            :class="[
                                store.theme === 'bold'
                                    ? 'text-white hover:bg-gray-800 border border-gray-700'
                                    : store.theme === 'modern'
                                    ? 'text-purple-700 hover:bg-purple-50 border border-purple-200'
                                    : store.theme === 'monochrome'
                                    ? 'text-gray-900 hover:bg-gray-50 border border-gray-200'
                                    : 'text-blue-700 hover:bg-blue-50 border border-blue-200',
                            ]"
                            title="Login"
                        >
                            <svg
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"
                                />
                            </svg>
                            <span class="hidden md:inline">Login</span>
                        </a>
                        <a
                            v-else
                            :href="`/store/${store.id}/profile`"
                            class="inline-flex items-center gap-1.5 px-2.5 py-2 sm:px-4 sm:py-2 rounded-lg font-medium transition-all duration-200"
                            :class="[
                                store.theme === 'bold'
                                    ? 'bg-gradient-to-r from-orange-500 to-yellow-500 text-white hover:from-orange-600 hover:to-yellow-600 shadow-lg'
                                    : store.theme === 'modern'
                                    ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white hover:from-purple-700 hover:to-pink-700 shadow-lg'
                                    : store.theme === 'monochrome'
                                    ? 'bg-gray-900/90 backdrop-blur-xl text-white hover:bg-gray-900 shadow-lg'
                                    : 'bg-blue-600 text-white hover:bg-blue-700 shadow-lg',
                            ]"
                            :title="`${customer.first_name}'s Profile`"
                        >
                            <svg
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                />
                            </svg>
                            <span class="hidden sm:inline">{{ customer.first_name }}</span>
                        </a>

                        <!-- Cart Button -->
                        <CartButton :theme="store.theme" />
                    </div>
                </div>
            </div>
        </header>

        <!-- Cart Drawer -->
        <CartDrawer :store="store" />

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Back Button -->
            <a
                :href="`/store/${store.id}`"
                :class="themeConfig.link"
                class="inline-flex items-center text-sm font-medium mb-6 hover:underline"
            >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Products
            </a>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- Product Images -->
                <div>
                    <!-- Main Image -->
                    <div
                        class="bg-white rounded-lg shadow-md overflow-hidden mb-4"
                    >
                        <div class="aspect-square bg-gray-200">
                            <img
                                v-if="selectedImage"
                                :src="selectedImage"
                                :alt="product.name"
                                class="w-full h-full object-cover"
                            />
                            <div
                                v-else
                                class="w-full h-full flex items-center justify-center text-gray-400"
                            >
                                <svg
                                    class="w-24 h-24"
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
                    </div>

                    <!-- Thumbnail Gallery -->
                    <div
                        v-if="product.images.length > 1"
                        class="grid grid-cols-4 gap-2"
                    >
                        <button
                            v-for="image in product.images"
                            :key="image.id"
                            @click="selectImage(image.path)"
                            :class="[
                                'aspect-square bg-gray-200 rounded-lg overflow-hidden border-2 transition-all',
                                selectedImage === image.path
                                    ? 'border-blue-600 ring-2 ring-blue-600'
                                    : 'border-transparent hover:border-gray-400',
                            ]"
                        >
                            <img
                                :src="image.path"
                                :alt="product.name"
                                class="w-full h-full object-cover"
                            />
                        </button>
                    </div>
                </div>

                <!-- Product Info -->
                <div>
                    <div
                        class="rounded-lg shadow-md p-8"
                        :class="[
                            themeConfig.cardBackground,
                            store.theme === 'modern' ? 'rounded-2xl' : '',
                        ]"
                    >
                        <!-- Product Name -->
                        <h2
                            class="text-3xl font-bold mb-4"
                            :class="
                                store.theme === 'bold'
                                    ? 'text-white'
                                    : 'text-gray-900'
                            "
                        >
                            {{ product.name }}
                        </h2>

                        <!-- Category and Fulfillment Badges -->
                        <div class="flex flex-wrap gap-2 mb-4">
                            <span
                                v-if="product.category"
                                class="inline-flex items-center bg-blue-100 text-blue-800 text-sm px-3 py-1 rounded-full"
                            >
                                {{ product.category }}
                            </span>

                            <!-- Fulfillment Badges -->
                            <!-- Pickup Only (when delivery not available) -->
                            <span
                                v-if="!store.shipping_enabled || !product.is_shippable"
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800"
                            >
                                <svg
                                    class="w-3.5 h-3.5 mr-1"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                    />
                                </svg>
                                Pickup Only
                            </span>

                            <!-- Both badges when delivery is available -->
                            <template v-else>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                >
                                    <svg
                                        class="w-3.5 h-3.5 mr-1"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                        />
                                    </svg>
                                    Pickup Available
                                </span>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                                >
                                    <svg
                                        class="w-3.5 h-3.5 mr-1"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"
                                        />
                                    </svg>
                                    Delivery Available
                                </span>
                            </template>
                        </div>

                        <!-- Price -->
                        <div class="mb-6">
                            <span
                                class="text-4xl font-bold"
                                :class="themeConfig.productPrice"
                                >{{ formatCurrency(product.price_cents) }}</span
                            >
                        </div>

                        <!-- Description -->
                        <div class="mb-8">
                            <h3
                                class="text-lg font-semibold mb-3"
                                :class="
                                    store.theme === 'bold'
                                        ? 'text-white'
                                        : 'text-gray-900'
                                "
                            >
                                Description
                            </h3>
                            <p
                                class="leading-relaxed whitespace-pre-wrap"
                                :class="
                                    store.theme === 'bold'
                                        ? 'text-gray-300'
                                        : 'text-gray-700'
                                "
                            >
                                {{
                                    product.description ||
                                    "No description available."
                                }}
                            </p>
                        </div>

                        <!-- Product Addons -->
                        <div v-if="product.addons && product.addons.length > 0" class="mb-8">
                            <ProductAddonSelector
                                :addons="product.addons"
                                :theme="store.theme"
                                @update:selections="selectedAddons = $event"
                                ref="addonSelector"
                            />
                        </div>

                        <!-- Shipping Info -->
                        <div
                            v-if="store.shipping_enabled && product.is_shippable"
                            class="mb-8 p-4 bg-gray-50 rounded-lg"
                        >
                            <h3
                                class="text-lg font-semibold text-gray-900 mb-3 flex items-center"
                            >
                                <svg
                                    class="w-5 h-5 mr-2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"
                                    />
                                </svg>
                                Shipping Information
                            </h3>
                            <div class="text-sm text-gray-700">
                                <p class="mb-1">
                                    <span class="font-medium"
                                        >Shipping Available:</span
                                    >
                                    Yes
                                </p>
                                <p v-if="product.weight_grams">
                                    <span class="font-medium">Weight:</span>
                                    {{ product.weight_grams }}g
                                </p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="space-y-3">
                            <button
                                @click="addToCart"
                                class="w-full font-semibold py-4 px-6 rounded-lg transition-all duration-300 flex items-center justify-center shadow-lg hover:shadow-xl"
                                :class="themeConfig.buttonPrimary"
                            >
                                <svg
                                    class="w-5 h-5 mr-2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"
                                    />
                                </svg>
                                Add to Cart
                            </button>

                            <button
                                @click="buyNow"
                                class="w-full font-semibold py-4 px-6 rounded-lg transition-all duration-300 shadow-lg hover:shadow-xl"
                                :class="themeConfig.buttonSecondary"
                            >
                                Buy Now
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Products -->
            <div v-if="relatedProducts.length > 0" class="mt-16">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">
                    Related Products
                </h2>
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6"
                >
                    <a
                        v-for="related in relatedProducts"
                        :key="related.id"
                        :href="`/store/${store.id}/products/${related.id}`"
                        class="bg-white rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 overflow-hidden group"
                    >
                        <!-- Related Product Image -->
                        <div class="aspect-square bg-gray-200 overflow-hidden">
                            <img
                                v-if="related.image"
                                :src="related.image"
                                :alt="related.name"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            />
                            <div
                                v-else
                                class="w-full h-full flex items-center justify-center text-gray-400"
                            >
                                <svg
                                    class="w-12 h-12"
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

                        <!-- Related Product Info -->
                        <div class="p-4">
                            <h3
                                class="text-base font-semibold text-gray-900 line-clamp-2 mb-2"
                            >
                                {{ related.name }}
                            </h3>

                            <!-- Fulfillment Badges -->
                            <div class="flex flex-wrap gap-1.5 mb-2">
                                <!-- Pickup Only (when delivery not available) -->
                                <span
                                    v-if="!store.shipping_enabled || !related.is_shippable"
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800"
                                >
                                    <svg
                                        class="w-3 h-3 mr-1"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                        />
                                    </svg>
                                    Pickup Only
                                </span>

                                <!-- Both badges when delivery is available -->
                                <template v-else>
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                    >
                                        <svg
                                            class="w-3 h-3 mr-1"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                            />
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                            />
                                        </svg>
                                        Pickup
                                    </span>
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                                    >
                                        <svg
                                            class="w-3 h-3 mr-1"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"
                                            />
                                        </svg>
                                        Delivery
                                    </span>
                                </template>
                            </div>

                            <span class="text-xl font-bold text-blue-600">{{
                                formatCurrency(related.price_cents)
                            }}</span>
                        </div>
                    </a>
                </div>
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
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import CartButton from "@/Components/Storefront/CartButton.vue";
import CartDrawer from "@/Components/Storefront/CartDrawer.vue";
import ProductAddonSelector from "@/Components/Storefront/ProductAddonSelector.vue";
import { useCart } from "@/Composables/useCart";
import { useTheme } from "@/Composables/useTheme";

const props = defineProps({
    store: {
        type: Object,
        required: true,
    },
    product: {
        type: Object,
        required: true,
    },
    customer: {
        type: Object,
        default: null,
    },
    relatedProducts: {
        type: Array,
        default: () => [],
    },
});

const { addToCart: addItemToCart, openCart, formatPrice } = useCart();

// Initialize theme
const { config: themeConfig } = useTheme(props.store.theme);

// Set initial selected image
const selectedImage = ref(
    props.product.image ||
        (props.product.images.length > 0 ? props.product.images[0].path : null)
);

// Addon selection
const addonSelector = ref(null);
const selectedAddons = ref([]);

const selectImage = (imagePath) => {
    selectedImage.value = imagePath;
};

const addToCart = () => {
    // Validate addons if component exists
    if (addonSelector.value && !addonSelector.value.validateAll()) {
        return;
    }

    // Debug: Log selected addons
    console.log('Selected addons:', selectedAddons.value);

    // Add product to cart with addons
    const success = addItemToCart(props.product, 1, [], selectedAddons.value);

    if (success) {
        // Open cart drawer to show added item
        openCart();
    }
};

const buyNow = () => {
    // Validate addons if component exists
    if (addonSelector.value && !addonSelector.value.validateAll()) {
        return;
    }

    // Add to cart and go directly to checkout
    addItemToCart(props.product, 1, [], selectedAddons.value);
    router.visit(`/store/${props.store.id}/checkout`);
};

const formatCurrency = (cents) => {
    return formatPrice(cents);
};
</script>
