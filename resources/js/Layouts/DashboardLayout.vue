<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <!-- Toast Notifications -->
        <ToastContainer />

        <!-- Top Navigation Bar -->
        <nav class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
            <div class="mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <span class="text-xl font-bold text-gray-800 dark:text-white"
                            >StoreFlow</span
                        >
                        <span v-if="currentStore" class="ml-4 text-gray-600 dark:text-gray-300">{{
                            currentStore.name
                        }}</span>
                    </div>

                    <div class="flex items-center space-x-4">
                        <span v-if="currentUser" class="text-sm text-gray-600 dark:text-gray-300">{{
                            currentUser.username
                        }}</span>
                        <button
                            @click="logout"
                            class="text-sm text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white"
                        >
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Inactive Store Banner -->
        <div
            v-if="store && !store.is_active"
            class="bg-yellow-500 text-white py-3 px-4 text-center font-medium shadow-md"
        >
            <div class="mx-auto flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        fill-rule="evenodd"
                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd"
                    />
                </svg>
                <span>Your store is currently not accepting orders.</span>
            </div>
        </div>

        <!-- Sidebar and Content -->
        <div class="flex">
            <!-- Sidebar -->
            <aside
                class="relative bg-white dark:bg-gray-800 shadow-sm min-h-screen transition-all duration-300"
                :class="sidebarCollapsed ? 'w-20' : 'w-64'"
            >
                <!-- Sidebar Toggle Button -->
                <div class="flex justify-end p-4">
                    <button
                        @click="toggleSidebar"
                        class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors"
                        :title="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                    >
                        <svg
                            v-if="!sidebarCollapsed"
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M11 19l-7-7 7-7m8 14l-7-7 7-7"
                            />
                        </svg>
                        <svg
                            v-else
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13 5l7 7-7 7M5 5l7 7-7 7"
                            />
                        </svg>
                    </button>
                </div>

                <nav class="px-3">
                    <div class="space-y-1">
                        <!-- Operations Section -->
                        <div class="mb-4">
                            <h3
                                v-if="!sidebarCollapsed"
                                class="px-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2"
                            >
                                Operations
                            </h3>
                            <div v-else class="border-t border-gray-200 dark:border-gray-700 my-2"></div>
                            <Link
                                :href="route('dashboard')"
                                class="flex items-center py-2 text-sm font-medium rounded-md transition-colors"
                                :class="[
                                    isActive('dashboard')
                                        ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200'
                                        : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700',
                                    sidebarCollapsed ? 'justify-center px-2' : 'px-3'
                                ]"
                                :title="sidebarCollapsed ? 'Active Orders' : ''"
                            >
                                <svg
                                    class="flex-shrink-0 w-5 h-5"
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
                                <span v-if="!sidebarCollapsed" class="ml-3">Active Orders</span>
                            </Link>
                        </div>

                        <!-- Management Section -->
                        <div class="mb-4">
                            <h3
                                v-if="!sidebarCollapsed"
                                class="px-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2"
                            >
                                Management
                            </h3>
                            <div v-else class="border-t border-gray-200 dark:border-gray-700 my-2"></div>
                            <div class="space-y-1">
                                <Link
                                    v-if="currentUser && currentUser.role !== 'staff'"
                                    :href="route('store.settings')"
                                    class="flex items-center py-2 text-sm font-medium rounded-md transition-colors"
                                    :class="[
                                        isActive('store.settings')
                                            ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200'
                                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700',
                                        sidebarCollapsed ? 'justify-center px-2' : 'px-3'
                                    ]"
                                    :title="sidebarCollapsed ? 'Store Settings' : ''"
                                >
                                    <svg
                                        class="flex-shrink-0 w-5 h-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                        />
                                    </svg>
                                    <span v-if="!sidebarCollapsed" class="ml-3">Store Settings</span>
                                </Link>
                                <Link
                                    v-if="currentUser && currentUser.role !== 'staff'"
                                    :href="route('categories.index')"
                                    class="flex items-center py-2 text-sm font-medium rounded-md transition-colors"
                                    :class="[
                                        isActive('categories.index')
                                            ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200'
                                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700',
                                        sidebarCollapsed ? 'justify-center px-2' : 'px-3'
                                    ]"
                                    :title="sidebarCollapsed ? 'Categories' : ''"
                                >
                                    <svg
                                        class="flex-shrink-0 w-5 h-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
                                        />
                                    </svg>
                                    <span v-if="!sidebarCollapsed" class="ml-3">Categories</span>
                                </Link>
                                <Link
                                    :href="route('products.index')"
                                    class="flex items-center py-2 text-sm font-medium rounded-md transition-colors"
                                    :class="[
                                        isActive('products.index')
                                            ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200'
                                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700',
                                        sidebarCollapsed ? 'justify-center px-2' : 'px-3'
                                    ]"
                                    :title="sidebarCollapsed ? 'Products' : ''"
                                >
                                    <svg
                                        class="flex-shrink-0 w-5 h-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                                        />
                                    </svg>
                                    <span v-if="!sidebarCollapsed" class="ml-3">Products</span>
                                </Link>
                                <Link
                                    :href="route('customers.index')"
                                    class="flex items-center py-2 text-sm font-medium rounded-md transition-colors"
                                    :class="[
                                        isActive('customers.index')
                                            ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200'
                                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700',
                                        sidebarCollapsed ? 'justify-center px-2' : 'px-3'
                                    ]"
                                    :title="sidebarCollapsed ? 'Customers' : ''"
                                >
                                    <svg
                                        class="flex-shrink-0 w-5 h-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                                        />
                                    </svg>
                                    <span v-if="!sidebarCollapsed" class="ml-3">Customers</span>
                                </Link>
                                <Link
                                    v-if="currentUser && currentUser.role === 'owner'"
                                    :href="route('staff.index')"
                                    class="flex items-center py-2 text-sm font-medium rounded-md transition-colors"
                                    :class="[
                                        isActive('staff.index')
                                            ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200'
                                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700',
                                        sidebarCollapsed ? 'justify-center px-2' : 'px-3'
                                    ]"
                                    :title="sidebarCollapsed ? 'Staff' : ''"
                                >
                                    <svg
                                        class="flex-shrink-0 w-5 h-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                        />
                                    </svg>
                                    <span v-if="!sidebarCollapsed" class="ml-3">Staff</span>
                                </Link>
                                <Link
                                    :href="route('orders.history')"
                                    class="flex items-center py-2 text-sm font-medium rounded-md transition-colors"
                                    :class="[
                                        isActive('orders.history')
                                            ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200'
                                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700',
                                        sidebarCollapsed ? 'justify-center px-2' : 'px-3'
                                    ]"
                                    :title="sidebarCollapsed ? 'Order History' : ''"
                                >
                                    <svg
                                        class="flex-shrink-0 w-5 h-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                    <span v-if="!sidebarCollapsed" class="ml-3">Order History</span>
                                </Link>
                                <Link
                                    v-if="currentUser && currentUser.role !== 'staff'"
                                    :href="route('shipping.index')"
                                    class="flex items-center py-2 text-sm font-medium rounded-md transition-colors"
                                    :class="[
                                        isActive('shipping.index')
                                            ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200'
                                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700',
                                        sidebarCollapsed ? 'justify-center px-2' : 'px-3'
                                    ]"
                                    :title="sidebarCollapsed ? 'Shipping' : ''"
                                >
                                    <svg
                                        class="flex-shrink-0 w-5 h-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"
                                        />
                                    </svg>
                                    <span v-if="!sidebarCollapsed" class="ml-3">Shipping</span>
                                </Link>
                                <Link
                                    v-if="currentUser && currentUser.role !== 'staff'"
                                    :href="route('loyalty.index')"
                                    class="flex items-center py-2 text-sm font-medium rounded-md transition-colors"
                                    :class="[
                                        isActive('loyalty.index')
                                            ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200'
                                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700',
                                        sidebarCollapsed ? 'justify-center px-2' : 'px-3'
                                    ]"
                                    :title="sidebarCollapsed ? 'Loyalty' : ''"
                                >
                                    <svg
                                        class="flex-shrink-0 w-5 h-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"
                                        />
                                    </svg>
                                    <span v-if="!sidebarCollapsed" class="ml-3">Loyalty</span>
                                </Link>
                                <Link
                                    v-if="currentUser && currentUser.role !== 'staff'"
                                    :href="route('audit-logs.index')"
                                    class="flex items-center py-2 text-sm font-medium rounded-md transition-colors"
                                    :class="[
                                        isActive('audit-logs.index')
                                            ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200'
                                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700',
                                        sidebarCollapsed ? 'justify-center px-2' : 'px-3'
                                    ]"
                                    :title="sidebarCollapsed ? 'Audit Logs' : ''"
                                >
                                    <svg
                                        class="flex-shrink-0 w-5 h-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                        />
                                    </svg>
                                    <span v-if="!sidebarCollapsed" class="ml-3">Audit Logs</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- Dark Mode Toggle - Fixed at Bottom -->
                <div class="fixed bottom-4 px-3 transition-all duration-300" :style="{ width: sidebarCollapsed ? '80px' : '256px' }">
                    <button
                        @click="toggleDarkMode"
                        class="w-full flex items-center py-2 px-3 text-sm font-medium rounded-md transition-colors bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 shadow-lg"
                        :class="sidebarCollapsed ? 'justify-center' : ''"
                        :title="sidebarCollapsed ? (isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode') : ''"
                    >
                        <!-- Sun Icon (Light Mode) -->
                        <svg
                            v-if="isDark"
                            class="flex-shrink-0 w-5 h-5 text-yellow-500"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
                            />
                        </svg>
                        <!-- Moon Icon (Dark Mode) -->
                        <svg
                            v-else
                            class="flex-shrink-0 w-5 h-5 text-gray-600 dark:text-gray-300"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
                            />
                        </svg>
                        <span v-if="!sidebarCollapsed" class="ml-3 text-gray-700 dark:text-gray-300">
                            {{ isDark ? 'Light Mode' : 'Dark Mode' }}
                        </span>
                    </button>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="flex-1 p-8 bg-gray-50 dark:bg-gray-900">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { Link, router, usePage } from "@inertiajs/vue3";
import { ref, computed, onMounted, onBeforeUnmount } from "vue";
import ToastContainer from "@/Components/Notifications/ToastContainer.vue";
import { useNotifications } from "@/Composables/useNotifications";
import { useDarkMode } from "@/Composables/useDarkMode";

const props = defineProps({
    store: Object,
    user: Object,
});

const page = usePage();

// Use auth user from Inertia shared props if not passed as prop
const currentUser = computed(() => props.user || page.props.auth?.user);
const currentStore = computed(() => props.store || page.props.store);
const { success } = useNotifications();

// Dark mode
const { isDark, toggleDarkMode, initializeDarkMode } = useDarkMode();

// Sidebar collapse state (persisted in localStorage)
const sidebarCollapsed = ref(false);

// Toggle sidebar collapsed state
const toggleSidebar = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value;
    localStorage.setItem("sidebarCollapsed", JSON.stringify(sidebarCollapsed.value));
};

// Component lifecycle
onMounted(() => {
    // Initialize dark mode
    initializeDarkMode();

    // Load sidebar state from localStorage
    const savedState = localStorage.getItem("sidebarCollapsed");
    if (savedState !== null) {
        sidebarCollapsed.value = JSON.parse(savedState);
    }

    // WebSocket setup for real-time order notifications
    // Check if Echo is available (requires Laravel Echo + Pusher setup)
    if (typeof window.Echo !== "undefined" && props.store?.id) {
        console.log("Setting up websocket listener for store:", props.store.id);

        // Listen for new orders on the store channel
        window.Echo.channel(`store.${props.store.id}.orders`).listen(
            ".order.created",
            (e) => {
                console.log("New order received:", e);

                // Show notification
                success(
                    "New Order!",
                    `Order ${e.order.public_id} placed for ${e.order.customer_name}`,
                    10000 // Show for 10 seconds
                );

                // Optional: Play notification sound
                playNotificationSound();

                // Optional: Reload active orders if on dashboard
                if (page.component === "Dashboard/Index") {
                    router.reload({ only: ["activeOrders"] });
                }
            }
        );
    } else {
        console.log(
            "WebSocket not configured. Set up Pusher and Laravel Echo for real-time notifications."
        );
    }
});

onBeforeUnmount(() => {
    // Clean up websocket listeners
    if (typeof window.Echo !== "undefined" && props.store?.id) {
        window.Echo.leave(`store.${props.store.id}.orders`);
    }
});

// Optional notification sound
const playNotificationSound = () => {
    // You can add an audio element or use Web Audio API
    // const audio = new Audio('/sounds/notification.mp3');
    // audio.play().catch(e => console.log('Could not play sound:', e));
};

const isActive = (routeName) => {
    // Get current route name from Inertia component
    const currentRoute = page.component;

    // For index routes, match exact component name
    if (routeName === "dashboard" && currentRoute === "Dashboard/Index") {
        return true;
    }

    // For resource routes, check if the component starts with the resource name
    const resourceMap = {
        "categories.index": "Categories",
        "products.index": "Products",
        "customers.index": "Customers",
        "staff.index": "Staff",
        "orders.history": "Orders/History",
        "shipping.index": "Shipping",
        "loyalty.index": "Loyalty",
        "audit-logs.index": "AuditLogs",
        "store.settings": "Store/Settings",
    };

    if (resourceMap[routeName]) {
        return currentRoute.startsWith(resourceMap[routeName]);
    }

    return false;
};

const logout = () => {
    router.post(route("logout"));
};
</script>
