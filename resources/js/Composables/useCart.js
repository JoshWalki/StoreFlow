import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';

// Shared cart state across the application
const cartItems = ref([]);
const isCartOpen = ref(false);

export function useCart() {
    const toast = useToast();

    // Load cart from localStorage on initialization
    if (cartItems.value.length === 0) {
        const stored = localStorage.getItem('storeflow_cart');
        if (stored) {
            try {
                cartItems.value = JSON.parse(stored);
            } catch (e) {
                console.error('Failed to load cart:', e);
                localStorage.removeItem('storeflow_cart');
            }
        }
    }

    // Save cart to localStorage
    const saveCart = () => {
        localStorage.setItem('storeflow_cart', JSON.stringify(cartItems.value));
    };

    // Add item to cart
    const addToCart = (product, quantity = 1, customizations = [], addons = [], specialMessage = '') => {
        console.log('useCart - Adding to cart:', { product: product.name, quantity, customizations, addons, specialMessage });

        // Check if item already exists (same product + same customizations + same addons + same message)
        const existingIndex = cartItems.value.findIndex(item => {
            if (item.product.id !== product.id) return false;
            if (item.customizations.length !== customizations.length) return false;
            if ((item.addons?.length || 0) !== addons.length) return false;
            if ((item.specialMessage || '') !== specialMessage) return false;

            // Check if customizations match
            const itemCustomizationIds = item.customizations.map(c => c.option_id).sort();
            const newCustomizationIds = customizations.map(c => c.option_id).sort();
            if (JSON.stringify(itemCustomizationIds) !== JSON.stringify(newCustomizationIds)) return false;

            // Check if addons match (including quantity)
            const itemAddonIds = (item.addons || []).map(a => `${a.addon_index}-${a.option_index}-${a.quantity || 1}`).sort();
            const newAddonIds = addons.map(a => `${a.addon_index}-${a.option_index}-${a.quantity || 1}`).sort();
            return JSON.stringify(itemAddonIds) === JSON.stringify(newAddonIds);
        });

        if (existingIndex !== -1) {
            // Update quantity of existing item
            cartItems.value[existingIndex].quantity += quantity;
        } else {
            // Add new item
            cartItems.value.push({
                id: Date.now() + Math.random(), // Unique cart item ID
                product: {
                    id: product.id,
                    name: product.name,
                    price_cents: product.price_cents,
                    image: product.image,
                    is_shippable: product.is_shippable,
                    weight_grams: product.weight_grams,
                },
                quantity,
                customizations: customizations.map(c => ({
                    group_id: c.group_id,
                    group_name: c.group_name,
                    option_id: c.option_id,
                    option_name: c.option_name,
                    price_delta_cents: c.price_delta_cents || 0,
                })),
                addons: addons.map(a => ({
                    addon_name: a.addon_name,
                    addon_index: a.addon_index,
                    option_name: a.option_name,
                    option_index: a.option_index,
                    price_adjustment: a.price_adjustment || 0,
                    quantity: a.quantity || 1,
                })),
                specialMessage: specialMessage || '',
            });
        }

        saveCart();
        return true;
    };

    // Remove item from cart
    const removeFromCart = (cartItemId) => {
        cartItems.value = cartItems.value.filter(item => item.id !== cartItemId);
        saveCart();
    };

    // Update item quantity
    const updateQuantity = (cartItemId, quantity) => {
        if (quantity <= 0) {
            removeFromCart(cartItemId);
            return;
        }

        const item = cartItems.value.find(i => i.id === cartItemId);
        if (item) {
            item.quantity = quantity;
            saveCart();
        }
    };

    // Clear entire cart
    const clearCart = () => {
        cartItems.value = [];
        localStorage.removeItem('storeflow_cart');
    };

    // Calculate item price (base + customizations + addons)
    const getItemPrice = (item) => {
        const basePrice = item.product.price_cents;
        const customizationsPrice = item.customizations.reduce(
            (sum, c) => sum + (c.price_delta_cents || 0),
            0
        );
        const addonsPrice = (item.addons || []).reduce(
            (sum, a) => sum + Math.round((a.price_adjustment || 0) * 100) * (a.quantity || 1),
            0
        );
        return basePrice + customizationsPrice + addonsPrice;
    };

    // Calculate item total (price * quantity)
    const getItemTotal = (item) => {
        return getItemPrice(item) * item.quantity;
    };

    // Cart computed values
    const cartCount = computed(() => {
        return cartItems.value.reduce((sum, item) => sum + item.quantity, 0);
    });

    const cartSubtotal = computed(() => {
        return cartItems.value.reduce((sum, item) => sum + getItemTotal(item), 0);
    });

    const hasShippableItems = computed(() => {
        return cartItems.value.some(item => item.product.is_shippable);
    });

    const totalWeight = computed(() => {
        return cartItems.value.reduce((sum, item) => {
            return sum + (item.product.weight_grams || 0) * item.quantity;
        }, 0);
    });

    // Format price in AUD
    const formatPrice = (cents) => {
        return new Intl.NumberFormat('en-AU', {
            style: 'currency',
            currency: 'AUD',
        }).format(cents / 100);
    };

    // Toggle cart drawer
    const toggleCart = () => {
        isCartOpen.value = !isCartOpen.value;
    };

    const openCart = () => {
        isCartOpen.value = true;
    };

    const closeCart = () => {
        isCartOpen.value = false;
    };

    // Go to checkout
    const goToCheckout = (store) => {
        if (cartItems.value.length === 0) {
            toast.warning('Your cart is empty');
            return;
        }
        closeCart();
        router.visit(`/store/${store.id}/checkout`);
    };

    return {
        // State
        cartItems,
        isCartOpen,

        // Actions
        addToCart,
        removeFromCart,
        updateQuantity,
        clearCart,
        toggleCart,
        openCart,
        closeCart,
        goToCheckout,

        // Computed
        cartCount,
        cartSubtotal,
        hasShippableItems,
        totalWeight,

        // Utilities
        getItemPrice,
        getItemTotal,
        formatPrice,
    };
}
