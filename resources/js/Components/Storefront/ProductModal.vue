<template>
    <TransitionRoot appear :show="isOpen" as="template">
        <Dialog as="div" @close="closeModal" class="relative z-50">
            <TransitionChild
                as="template"
                enter="duration-300 ease-out"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="duration-200 ease-in"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-black bg-opacity-50" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <TransitionChild
                        as="template"
                        enter="duration-300 ease-out"
                        enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100"
                        leave="duration-200 ease-in"
                        leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95"
                    >
                        <DialogPanel
                            class="w-full max-w-5xl transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 shadow-xl transition-all"
                        >
                            <!-- Close Button -->
                            <button
                                @click="closeModal"
                                class="absolute top-4 right-4 z-10 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 bg-white dark:bg-gray-800 rounded-full p-2 shadow-lg"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>

                            <!-- Loading State -->
                            <div v-if="loading" class="flex items-center justify-center py-20">
                                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                            </div>

                            <!-- Product Content -->
                            <div v-else-if="product" class="grid grid-cols-1 lg:grid-cols-2 gap-8 p-8">
                                <!-- Product Images -->
                                <div>
                                    <!-- Main Image -->
                                    <div class="mb-4">
                                        <div class="aspect-square flex items-center justify-center">
                                            <img
                                                v-if="selectedImage"
                                                :src="selectedImage"
                                                :alt="product.name"
                                                class="max-w-full max-h-full object-contain rounded-lg"
                                            />
                                            <div
                                                v-else
                                                class="w-full h-full flex items-center justify-center text-gray-400"
                                            >
                                                <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                    <div v-if="product.images.length > 1" class="grid grid-cols-4 gap-2">
                                        <button
                                            v-for="image in product.images"
                                            :key="image.id"
                                            @click="selectImage(image.path)"
                                            :class="[
                                                'aspect-square rounded-lg border-2 transition-all flex items-center justify-center p-2',
                                                selectedImage === image.path
                                                    ? 'border-blue-600 ring-2 ring-blue-600'
                                                    : 'border-gray-300 hover:border-gray-400',
                                            ]"
                                        >
                                            <img :src="image.path" :alt="product.name" class="max-w-full max-h-full object-contain rounded" />
                                        </button>
                                    </div>
                                </div>

                                <!-- Product Info -->
                                <div class="overflow-y-auto max-h-[calc(100vh-8rem)]">
                                    <!-- Product Name -->
                                    <h2 class="text-3xl font-bold mb-4 text-gray-900 dark:text-white">
                                        {{ product.name }}
                                    </h2>

                                    <!-- Category, Discount and Fulfillment Badges -->
                                    <div class="flex flex-wrap gap-2 mb-4">
                                        <!-- Discount Badge -->
                                        <span
                                            v-if="product.has_active_sale && product.discount_badge"
                                            class="inline-flex items-center bg-red-100 text-red-700 text-sm font-semibold px-3 py-1 rounded-full"
                                        >
                                            {{ product.discount_badge }}
                                        </span>

                                        <span
                                            v-if="product.category"
                                            class="inline-flex items-center bg-blue-100 text-blue-800 text-sm px-3 py-1 rounded-full"
                                        >
                                            {{ product.category }}
                                        </span>

                                        <!-- Pickup Only (when delivery not available) -->
                                        <span
                                            v-if="!store.shipping_enabled || !product.is_shippable"
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800"
                                        >
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                        <!-- Sale Price Display -->
                                        <div v-if="product.has_active_sale" class="flex items-baseline gap-3">
                                            <span class="text-4xl font-bold text-red-600 dark:text-red-400">
                                                {{ formatCurrency(product.sale_price) }}
                                            </span>
                                            <span class="text-2xl line-through text-gray-500 dark:text-gray-400">
                                                {{ formatCurrency(product.price_cents) }}
                                            </span>
                                            <span v-if="product.savings_amount" class="text-sm font-medium text-green-600 dark:text-green-400">
                                                Save {{ formatCurrency(product.savings_amount) }}
                                            </span>
                                        </div>
                                        <!-- Regular Price Display -->
                                        <span v-else class="text-4xl font-bold text-blue-600 dark:text-blue-400">
                                            {{ formatCurrency(product.price_cents) }}
                                        </span>
                                    </div>

                                    <!-- Description -->
                                    <div class="mb-8">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-900 dark:text-white">
                                            Description
                                        </h3>
                                        <p class="text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-wrap">
                                            {{ product.description || "No description available." }}
                                        </p>
                                    </div>

                                    <!-- Product Addons -->
                                    <div v-if="product.addons && product.addons.length > 0" class="mb-8">
                                        <ProductAddonSelector
                                            :addons="product.addons"
                                            :theme="store.theme"
                                            @update:selections="handleAddonUpdate"
                                            ref="addonSelector"
                                        />
                                    </div>

                                    <!-- Shipping Info -->
                                    <div
                                        v-if="store.shipping_enabled && product.is_shippable"
                                        class="mb-8 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg"
                                    >
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"
                                                />
                                            </svg>
                                            Shipping Information
                                        </h3>
                                        <div class="text-sm text-gray-700 dark:text-gray-300">
                                            <p class="mb-1">
                                                <span class="font-medium">Shipping Available:</span> Yes
                                            </p>
                                            <p v-if="product.weight_grams">
                                                <span class="font-medium">Weight:</span> {{ product.weight_grams }}g
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="space-y-3">
                                        <button
                                            @click="addToCart"
                                            class="w-full font-semibold py-4 px-6 rounded-lg transition-all duration-300 flex items-center justify-center shadow-lg hover:shadow-xl bg-blue-600 text-white hover:bg-blue-700"
                                        >
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                            class="w-full font-semibold py-4 px-6 rounded-lg transition-all duration-300 shadow-lg hover:shadow-xl bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white hover:bg-gray-300 dark:hover:bg-gray-600"
                                        >
                                            Buy Now
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup>
import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import { TransitionRoot, TransitionChild, Dialog, DialogPanel } from "@headlessui/vue";
import ProductAddonSelector from "@/Components/Storefront/ProductAddonSelector.vue";
import { useCart } from "@/Composables/useCart";

const props = defineProps({
    isOpen: {
        type: Boolean,
        required: true,
    },
    productId: {
        type: Number,
        default: null,
    },
    store: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["close"]);

const { addToCart: addItemToCart, openCart, formatPrice } = useCart();

const loading = ref(false);
const product = ref(null);
const selectedImage = ref(null);
const addonSelector = ref(null);
const selectedAddons = ref([]);
const specialMessage = ref('');

// Handle addon update from ProductAddonSelector
const handleAddonUpdate = (data) => {
    if (data && typeof data === 'object' && 'addons' in data) {
        selectedAddons.value = data.addons || [];
        specialMessage.value = data.message || '';
    } else {
        // Fallback for backward compatibility
        selectedAddons.value = data || [];
        specialMessage.value = '';
    }
};

const closeModal = () => {
    emit("close");
    // Reset state
    product.value = null;
    selectedImage.value = null;
    selectedAddons.value = [];
    specialMessage.value = '';
};

const selectImage = (imagePath) => {
    selectedImage.value = imagePath;
};

const fetchProductDetails = async (productId) => {
    if (!productId) return;

    loading.value = true;
    try {
        const response = await fetch(`/store/${props.store.id}/products/${productId}`, {
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "Accept": "application/json",
            },
        });

        if (!response.ok) {
            throw new Error('Failed to load product details');
        }

        const data = await response.json();
        product.value = data;

        // Set initial selected image
        selectedImage.value = data.image || (data.images.length > 0 ? data.images[0].path : null);
    } catch (error) {
        console.error("Failed to load product details:", error);
        // Close modal on error
        closeModal();
    } finally {
        loading.value = false;
    }
};

const addToCart = () => {
    // Validate addons if component exists
    if (addonSelector.value && !addonSelector.value.validateAll()) {
        return;
    }

    // Add product to cart with addons and message
    const success = addItemToCart(product.value, 1, [], selectedAddons.value, specialMessage.value);

    if (success) {
        // Open cart drawer to show added item
        openCart();
        closeModal();
    }
};

const buyNow = () => {
    // Validate addons if component exists
    if (addonSelector.value && !addonSelector.value.validateAll()) {
        return;
    }

    // Add to cart and go directly to checkout
    addItemToCart(product.value, 1, [], selectedAddons.value, specialMessage.value);
    closeModal();
    router.visit(`/store/${props.store.id}/checkout`);
};

const formatCurrency = (cents) => {
    return formatPrice(cents);
};

// Watch for productId changes to fetch data
watch(
    () => props.productId,
    (newId) => {
        if (newId && props.isOpen) {
            fetchProductDetails(newId);
        }
    },
    { immediate: true }
);

// Watch for modal open to fetch data
watch(
    () => props.isOpen,
    (isOpen) => {
        if (isOpen && props.productId) {
            fetchProductDetails(props.productId);
        }
    }
);
</script>
