<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Inactive Store Banner -->
        <div
            v-if="!store.is_active"
            class="bg-yellow-500 text-white py-3 px-4 text-center font-medium"
        >
            <div class="max-w-7xl mx-auto flex items-center justify-center gap-2">
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
        <header class="bg-white shadow-sm sticky top-0 z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex justify-between items-center">
                    <!-- Store Logo/Name -->
                    <div class="flex items-center">
                        <img
                            v-if="store.logo_url"
                            :src="store.logo_url"
                            :alt="store.name"
                            class="max-h-12 max-w-xs object-contain"
                        />
                        <h1 v-else class="text-2xl font-bold text-gray-900">
                            {{ store.name }}
                        </h1>
                    </div>

                    <!-- Header Actions -->
                    <div class="flex items-center gap-3">
                        <a
                            v-if="!customer"
                            :href="`/store/${store.id}/login`"
                            class="text-sm font-medium text-gray-700 hover:text-gray-900"
                        >
                            Login
                        </a>
                        <a
                            v-else
                            :href="`/store/${store.id}/profile`"
                            class="text-sm font-medium text-blue-600 hover:text-blue-700"
                        >
                            {{ customer.first_name }}'s Profile
                        </a>
                        <CartButton />
                    </div>
                </div>
            </div>
        </header>

        <!-- Cart Drawer -->
        <CartDrawer :store="store" />

        <!-- Main Layout: Sidebar + Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex gap-8">
                <!-- Left Sidebar Navigation -->
                <aside class="w-64 flex-shrink-0 hidden lg:block">
                    <div class="bg-white rounded-lg shadow-sm p-6 sticky top-24">
                        <h2 class="text-xl font-bold text-gray-900 mb-2">Menu</h2>

                        <!-- Operating Hours -->
                        <div v-if="store.open_time && store.close_time" class="mb-6 pb-4 border-b border-gray-200">
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="font-medium">{{ formatTime(store.open_time) }} - {{ formatTime(store.close_time) }}</span>
                            </div>
                        </div>

                        <nav class="space-y-1">
                            <!-- Categories -->
                            <div v-for="category in categories" :key="category.id">
                                <a
                                    :href="`#${category.slug}`"
                                    @click.prevent="scrollToCategory(category.slug)"
                                    class="block px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded transition-colors"
                                    :class="{ 'bg-gray-200 font-semibold': activeCategory === category.slug }"
                                >
                                    {{ category.name }}
                                </a>
                            </div>
                        </nav>
                    </div>
                </aside>

                <!-- Main Content Area -->
                <main class="flex-1 min-w-0">
                    <!-- Picked for You Section -->
                    <section
                        v-if="frequent_products && frequent_products.length > 0"
                        id="picked"
                        class="mb-12"
                    >
                        <h2 class="text-3xl font-bold text-gray-900 mb-6">Picked for you</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <ProductCardGrid
                                v-for="product in frequent_products.slice(0, 4)"
                                :key="'picked-' + product.id"
                                :product="product"
                                :store="store"
                                :show-popular="true"
                            />
                        </div>
                    </section>

                    <!-- Category Sections -->
                    <div v-if="categories && categories.length > 0">
                        <section
                            v-for="category in categories"
                            :key="category.id"
                            :id="category.slug"
                            class="mb-12 scroll-mt-24"
                        >
                            <h2 class="text-3xl font-bold text-gray-900 mb-6">
                                {{ category.name }}
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <ProductCardGrid
                                    v-for="product in category.products"
                                    :key="'cat-' + category.id + '-prod-' + product.id"
                                    :product="product"
                                    :store="store"
                                />
                            </div>
                        </section>
                    </div>

                    <!-- Empty State -->
                    <div
                        v-if="(!frequent_products || frequent_products.length === 0) && (!categories || categories.length === 0)"
                        class="text-center py-12"
                    >
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
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import CartButton from "@/Components/Storefront/CartButton.vue";
import CartDrawer from "@/Components/Storefront/CartDrawer.vue";
import ProductCardGrid from "@/Components/Storefront/ProductCardGrid.vue";

const props = defineProps({
    store: {
        type: Object,
        required: true,
    },
    frequent_products: {
        type: Array,
        default: () => [],
    },
    categories: {
        type: Array,
        default: () => [],
    },
    customer: {
        type: Object,
        default: null,
    },
});

const activeCategory = ref('');

const scrollToCategory = (slug) => {
    const element = document.getElementById(slug);
    if (element) {
        const offset = 100; // Account for sticky header
        const elementPosition = element.getBoundingClientRect().top;
        const offsetPosition = elementPosition + window.pageYOffset - offset;

        window.scrollTo({
            top: offsetPosition,
            behavior: 'smooth'
        });
        activeCategory.value = slug;
    }
};

const formatTime = (time) => {
    if (!time) return '';
    const [hours, minutes] = time.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour % 12 || 12;
    return `${displayHour}:${minutes} ${ampm}`;
};

// Update active category on scroll
const handleScroll = () => {
    if (!props.categories || props.categories.length === 0) return;

    const scrollPosition = window.scrollY + 150;

    for (let i = props.categories.length - 1; i >= 0; i--) {
        const category = props.categories[i];
        const element = document.getElementById(category.slug);
        if (element && element.offsetTop <= scrollPosition) {
            activeCategory.value = category.slug;
            break;
        }
    }
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    handleScroll(); // Set initial active category
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>
