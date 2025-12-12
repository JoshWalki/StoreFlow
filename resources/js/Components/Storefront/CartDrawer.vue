<template>
    <!-- Cart Drawer Overlay -->
    <TransitionRoot as="template" :show="isCartOpen">
        <Dialog as="div" class="relative z-50" @close="closeCart">
            <!-- Background overlay -->
            <TransitionChild
                as="template"
                enter="ease-in-out duration-300"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="ease-in-out duration-300"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                />
            </TransitionChild>

            <!-- Cart drawer -->
            <div class="fixed inset-0 overflow-hidden">
                <div class="absolute inset-0 overflow-hidden">
                    <div
                        class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10"
                    >
                        <TransitionChild
                            as="template"
                            enter="transform transition ease-in-out duration-300"
                            enter-from="translate-x-full"
                            enter-to="translate-x-0"
                            leave="transform transition ease-in-out duration-300"
                            leave-from="translate-x-0"
                            leave-to="translate-x-full"
                        >
                            <DialogPanel
                                class="pointer-events-auto w-screen max-w-md"
                            >
                                <div
                                    class="flex h-full flex-col overflow-y-scroll shadow-xl"
                                    :class="themeConfig.cardBackground"
                                >
                                    <!-- Header -->
                                    <div
                                        class="flex-1 overflow-y-auto px-4 py-6 sm:px-6"
                                    >
                                        <div
                                            class="flex items-start justify-between"
                                        >
                                            <DialogTitle
                                                class="text-lg font-medium"
                                                :class="
                                                    store.theme === 'bold'
                                                        ? 'text-white'
                                                        : 'text-gray-900'
                                                "
                                            >
                                                Shopping Cart
                                            </DialogTitle>
                                            <div
                                                class="ml-3 flex h-7 items-center"
                                            >
                                                <button
                                                    type="button"
                                                    class="relative -m-2 p-2 transition-colors"
                                                    :class="
                                                        store.theme === 'bold'
                                                            ? 'text-gray-400 hover:text-gray-300'
                                                            : 'text-gray-400 hover:text-gray-500'
                                                    "
                                                    @click="closeCart"
                                                >
                                                    <span class="sr-only"
                                                        >Close panel</span
                                                    >
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
                                                            d="M6 18L18 6M6 6l12 12"
                                                        />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Cart Items -->
                                        <div class="mt-8">
                                            <div
                                                v-if="cartItems.length === 0"
                                                class="text-center py-12"
                                            >
                                                <svg
                                                    class="mx-auto h-12 w-12"
                                                    :class="
                                                        store.theme === 'bold'
                                                            ? 'text-gray-600'
                                                            : 'text-gray-400'
                                                    "
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
                                                <h3
                                                    class="mt-2 text-sm font-medium"
                                                    :class="
                                                        store.theme === 'bold'
                                                            ? 'text-white'
                                                            : 'text-gray-900'
                                                    "
                                                >
                                                    Your cart is empty
                                                </h3>
                                                <p
                                                    class="mt-1 text-sm"
                                                    :class="
                                                        store.theme === 'bold'
                                                            ? 'text-gray-400'
                                                            : 'text-gray-500'
                                                    "
                                                >
                                                    Start adding some products!
                                                </p>
                                            </div>

                                            <div v-else class="flow-root">
                                                <ul
                                                    role="list"
                                                    class="-my-6 divide-y"
                                                    :class="
                                                        store.theme === 'bold'
                                                            ? 'divide-gray-700'
                                                            : 'divide-gray-200'
                                                    "
                                                >
                                                    <li
                                                        v-for="item in cartItems"
                                                        :key="item.id"
                                                        class="flex py-6"
                                                    >
                                                        <!-- Product Image -->
                                                        <div
                                                            class="h-24 w-24 flex-shrink-0 overflow-hidden rounded-md border"
                                                            :class="
                                                                store.theme ===
                                                                'bold'
                                                                    ? 'border-gray-700'
                                                                    : 'border-gray-200'
                                                            "
                                                        >
                                                            <img
                                                                v-if="
                                                                    item.product
                                                                        .image
                                                                "
                                                                :src="
                                                                    getImageUrl(
                                                                        item
                                                                            .product
                                                                            .image
                                                                    )
                                                                "
                                                                :alt="
                                                                    item.product
                                                                        .name
                                                                "
                                                                class="h-full w-full object-cover object-center"
                                                            />
                                                            <div
                                                                v-else
                                                                class="h-full w-full flex items-center justify-center"
                                                                :class="
                                                                    store.theme ===
                                                                    'bold'
                                                                        ? 'bg-gray-800'
                                                                        : 'bg-gray-200'
                                                                "
                                                            >
                                                                <svg
                                                                    class="h-12 w-12"
                                                                    :class="
                                                                        store.theme ===
                                                                        'bold'
                                                                            ? 'text-gray-600'
                                                                            : 'text-gray-400'
                                                                    "
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

                                                        <!-- Product Details -->
                                                        <div
                                                            class="ml-4 flex flex-1 flex-col"
                                                        >
                                                            <div>
                                                                <div
                                                                    class="flex justify-between text-base font-medium"
                                                                    :class="
                                                                        store.theme ===
                                                                        'bold'
                                                                            ? 'text-white'
                                                                            : 'text-gray-900'
                                                                    "
                                                                >
                                                                    <h3>
                                                                        {{
                                                                            item
                                                                                .product
                                                                                .name
                                                                        }}
                                                                    </h3>
                                                                    <p
                                                                        class="ml-4"
                                                                    >
                                                                        {{
                                                                            formatPrice(
                                                                                getItemTotal(
                                                                                    item
                                                                                )
                                                                            )
                                                                        }}
                                                                    </p>
                                                                </div>

                                                                <!-- Customizations -->
                                                                <div
                                                                    v-if="
                                                                        item
                                                                            .customizations
                                                                            .length >
                                                                        0
                                                                    "
                                                                    class="mt-1 text-sm"
                                                                    :class="
                                                                        store.theme ===
                                                                        'bold'
                                                                            ? 'text-gray-400'
                                                                            : 'text-gray-500'
                                                                    "
                                                                >
                                                                    <div
                                                                        v-for="custom in item.customizations"
                                                                        :key="
                                                                            custom.option_id
                                                                        "
                                                                        class="flex justify-between"
                                                                    >
                                                                        <span
                                                                            >{{
                                                                                custom.group_name
                                                                            }}:
                                                                            {{
                                                                                custom.option_name
                                                                            }}</span
                                                                        >
                                                                        <span
                                                                            v-if="
                                                                                custom.price_delta_cents >
                                                                                0
                                                                            "
                                                                            class="text-xs"
                                                                        >
                                                                            +{{
                                                                                formatPrice(
                                                                                    custom.price_delta_cents
                                                                                )
                                                                            }}
                                                                        </span>
                                                                    </div>
                                                                </div>

                                                                <!-- Addons -->
                                                                <div
                                                                    v-if="
                                                                        item.addons &&
                                                                        item.addons.length > 0
                                                                    "
                                                                    class="mt-2 text-sm"
                                                                    :class="
                                                                        store.theme ===
                                                                        'bold'
                                                                            ? 'text-gray-400'
                                                                            : 'text-gray-500'
                                                                    "
                                                                >
                                                                    <div class="font-medium text-xs mb-1" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-600'">
                                                                        Addons:
                                                                    </div>
                                                                    <div
                                                                        v-for="(addon, addonIdx) in item.addons"
                                                                        :key="addonIdx"
                                                                        class="flex justify-between pl-2"
                                                                    >
                                                                        <span class="text-xs">
                                                                            + {{ addon.addon_name }}: {{ addon.option_name }}
                                                                        </span>
                                                                        <span
                                                                            v-if="addon.price_adjustment > 0"
                                                                            class="text-xs"
                                                                        >
                                                                            +{{ formatCurrency(addon.price_adjustment) }}
                                                                        </span>
                                                                    </div>
                                                                </div>

                                                                <!-- Unit Price -->
                                                                <p
                                                                    class="mt-1 text-sm"
                                                                    :class="
                                                                        store.theme ===
                                                                        'bold'
                                                                            ? 'text-gray-400'
                                                                            : 'text-gray-500'
                                                                    "
                                                                >
                                                                    {{
                                                                        formatPrice(
                                                                            getItemPrice(
                                                                                item
                                                                            )
                                                                        )
                                                                    }}
                                                                    each
                                                                </p>
                                                            </div>

                                                            <!-- Quantity Controls -->
                                                            <div
                                                                class="flex flex-1 items-end justify-between text-sm"
                                                            >
                                                                <div
                                                                    class="flex items-center space-x-2"
                                                                >
                                                                    <label
                                                                        :class="
                                                                            store.theme ===
                                                                            'bold'
                                                                                ? 'text-gray-400'
                                                                                : 'text-gray-500'
                                                                        "
                                                                        >Qty</label
                                                                    >
                                                                    <div
                                                                        class="flex items-center border rounded"
                                                                        :class="
                                                                            store.theme ===
                                                                            'bold'
                                                                                ? 'border-gray-700'
                                                                                : 'border-gray-300'
                                                                        "
                                                                    >
                                                                        <button
                                                                            @click="
                                                                                updateQuantity(
                                                                                    item.id,
                                                                                    item.quantity -
                                                                                        1
                                                                                )
                                                                            "
                                                                            class="px-2 py-1 transition-colors"
                                                                            :class="
                                                                                store.theme ===
                                                                                'bold'
                                                                                    ? 'hover:bg-gray-800 text-white'
                                                                                    : 'hover:bg-gray-100 text-gray-900'
                                                                            "
                                                                            :disabled="
                                                                                item.quantity <=
                                                                                1
                                                                            "
                                                                        >
                                                                            −
                                                                        </button>
                                                                        <input
                                                                            type="number"
                                                                            :value="
                                                                                item.quantity
                                                                            "
                                                                            @change="
                                                                                updateQuantity(
                                                                                    item.id,
                                                                                    parseInt(
                                                                                        $event
                                                                                            .target
                                                                                            .value
                                                                                    ) ||
                                                                                        1
                                                                                )
                                                                            "
                                                                            min="1"
                                                                            max="100"
                                                                            class="w-12 text-center border-x py-1 focus:outline-none"
                                                                            :class="
                                                                                store.theme ===
                                                                                'bold'
                                                                                    ? 'bg-gray-900 border-gray-700 text-white'
                                                                                    : 'bg-white border-gray-300 text-gray-900'
                                                                            "
                                                                        />
                                                                        <button
                                                                            @click="
                                                                                updateQuantity(
                                                                                    item.id,
                                                                                    item.quantity +
                                                                                        1
                                                                                )
                                                                            "
                                                                            class="px-2 py-1 transition-colors"
                                                                            :class="
                                                                                store.theme ===
                                                                                'bold'
                                                                                    ? 'hover:bg-gray-800 text-white'
                                                                                    : 'hover:bg-gray-100 text-gray-900'
                                                                            "
                                                                        >
                                                                            +
                                                                        </button>
                                                                    </div>
                                                                </div>

                                                                <div
                                                                    class="flex"
                                                                >
                                                                    <button
                                                                        type="button"
                                                                        class="font-medium text-red-600 hover:text-red-500 transition-colors"
                                                                        @click="
                                                                            removeFromCart(
                                                                                item.id
                                                                            )
                                                                        "
                                                                    >
                                                                        Remove
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Footer with Subtotal and Checkout -->
                                    <div
                                        v-if="cartItems.length > 0"
                                        class="border-t px-4 py-6 sm:px-6"
                                        :class="
                                            store.theme === 'bold'
                                                ? 'border-gray-700'
                                                : 'border-gray-200'
                                        "
                                    >
                                        <div
                                            class="flex justify-between text-base font-medium"
                                            :class="
                                                store.theme === 'bold'
                                                    ? 'text-white'
                                                    : 'text-gray-900'
                                            "
                                        >
                                            <p>Subtotal</p>
                                            <p>
                                                {{ formatPrice(cartSubtotal) }}
                                            </p>
                                        </div>
                                        <div class="mt-6">
                                            <button
                                                @click="goToCheckout(store)"
                                                class="w-full flex items-center justify-center rounded-md border border-transparent px-6 py-3 text-base font-medium shadow-sm transition-all"
                                                :class="
                                                    themeConfig.buttonPrimary
                                                "
                                            >
                                                Checkout
                                            </button>
                                        </div>
                                        <div
                                            class="mt-6 flex justify-center text-center text-sm"
                                            :class="
                                                store.theme === 'bold'
                                                    ? 'text-gray-400'
                                                    : 'text-gray-500'
                                            "
                                        >
                                            <p>
                                                or
                                                <button
                                                    type="button"
                                                    class="font-medium transition-colors"
                                                    :class="themeConfig.link"
                                                    @click="closeCart"
                                                >
                                                    Continue Shopping
                                                    <span aria-hidden="true">
                                                        &rarr;</span
                                                    >
                                                </button>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup>
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionChild,
    TransitionRoot,
} from "@headlessui/vue";
import { useCart } from "@/Composables/useCart";
import { useTheme } from "@/Composables/useTheme";

const props = defineProps({
    store: {
        type: Object,
        required: true,
    },
});

// Initialize theme
const { config: themeConfig } = useTheme(props.store.theme);

const {
    cartItems,
    isCartOpen,
    closeCart,
    removeFromCart,
    updateQuantity,
    cartSubtotal,
    getItemPrice,
    getItemTotal,
    formatPrice,
    goToCheckout,
} = useCart();

// Helper to get correct image URL (avoid double /storage/ prefix)
const getImageUrl = (imagePath) => {
    if (!imagePath) return null;
    // If it already starts with /storage/, return as is
    if (imagePath.startsWith("/storage/")) {
        return imagePath;
    }
    // Otherwise, add the /storage/ prefix
    return `/storage/${imagePath}`;
};

// Helper to format currency from dollar amount
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-AU', {
        style: 'currency',
        currency: 'AUD',
    }).format(amount);
};
</script>
