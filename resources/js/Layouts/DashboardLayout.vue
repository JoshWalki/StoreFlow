<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <!-- Onboarding Modal (Unclosable) -->
        <OnboardingModal v-if="needsOnboarding" />

        <!-- Toast Notifications -->
        <ToastContainer />

        <!-- Top Navigation Bar -->
        <nav
            class="bg-white dark:bg-gray-800 shadow-sm border-gray-200 dark:border-gray-700"
        >
            <div class="mx-auto px-2 sm:px-3 lg:px-6">
                <div class="flex justify-between items-center h-12 sm:h-14">
                    <div class="flex items-center gap-1.5">
                        <!-- Mobile Menu Button (Left of Logo) -->
                        <button
                            @click="toggleMobileSidebar"
                            class="lg:hidden p-1.5 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md"
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
                                    d="M4 6h16M4 12h16M4 18h16"
                                />
                            </svg>
                        </button>

                        <img
                            :src="
                                isDark
                                    ? '/images/logo/logo-banner-white.png'
                                    : '/images/logo/logo-banner.png'
                            "
                            alt="StoreFlow"
                            class="h-8 w-auto transition-opacity duration-300"
                        />
                        <span
                            v-if="currentStore"
                            class="hidden sm:inline ml-1.5 text-xs sm:text-sm text-gray-600 dark:text-gray-300"
                            >{{ currentStore.name }}</span
                        >
                    </div>

                    <div class="flex items-center space-x-1.5 sm:space-x-3">

                        <!-- Store Link with Copy Button -->
                        <div
                            v-if="currentStore"
                            class="flex items-center space-x-1 px-2 py-1 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600"
                        >
                            <a
                                :href="storeUrl"
                                target="_blank"
                                class="text-[10px] sm:text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium truncate max-w-[60px] sm:max-w-none"
                                title="View store in new tab"
                            >
                                /store/{{ currentStore.id }}
                            </a>
                            <button
                                @click="copyStoreLink"
                                class="p-0.5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 rounded transition-colors flex-shrink-0"
                                title="Copy store link"
                            >
                                <svg
                                    v-if="!linkCopied"
                                    class="w-3 h-3"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                                    />
                                </svg>
                                <svg
                                    v-else
                                    class="w-3 h-3 text-green-600 dark:text-green-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M5 13l4 4L19 7"
                                    />
                                </svg>
                            </button>
                        </div>

                        <!-- Bell Notification Icon -->
                        <div class="relative">
                            <button
                                @click="showBellDropdown = !showBellDropdown"
                                class="relative p-2 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors"
                                title="Notification Settings"
                            >
                                <svg
                                    class="w-5 h-5"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"
                                    />
                                </svg>
                                <!-- Notification indicator dot when enabled -->
                                <span
                                    v-if="soundEnabled"
                                    class="absolute top-1 right-1 w-2 h-2 bg-green-500 rounded-full"
                                ></span>
                            </button>

                            <!-- Notification Settings Dropdown -->
                            <Transition name="dropdown">
                                <div
                                    v-if="showBellDropdown"
                                    @click.stop
                                    class="absolute right-0 mt-2 w-72 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 z-50"
                                >
                                    <div class="p-4">
                                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">
                                            Sound Notifications
                                        </h3>

                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex-1">
                                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                                    Order Alerts
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    Play sound for new orders
                                                </p>
                                            </div>

                                            <!-- Toggle Switch -->
                                            <button
                                                @click="toggleSoundNotifications"
                                                class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                                :class="soundEnabled ? 'bg-green-600' : 'bg-gray-300 dark:bg-gray-600'"
                                            >
                                                <span
                                                    class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform"
                                                    :class="soundEnabled ? 'translate-x-6' : 'translate-x-1'"
                                                ></span>
                                            </button>
                                        </div>

                                        <!-- Push Notifications Toggle -->
                                        <div class="flex items-center justify-between mb-3 pb-3 border-b border-gray-200 dark:border-gray-700">
                                            <div class="flex-1">
                                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                                    Push Notifications
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    Alerts when app is closed
                                                </p>
                                            </div>

                                            <!-- Toggle Switch -->
                                            <button
                                                @click="togglePushNotifications"
                                                :disabled="!pushSupported"
                                                class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                                :class="pushEnabled ? 'bg-green-600' : 'bg-gray-300 dark:bg-gray-600'"
                                            >
                                                <span
                                                    class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform"
                                                    :class="pushEnabled ? 'translate-x-6' : 'translate-x-1'"
                                                ></span>
                                            </button>
                                        </div>

                                        <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                <span v-if="soundEnabled" class="text-green-600 dark:text-green-400 font-medium">
                                                    ✓ Sound Enabled
                                                </span>
                                                <span v-else class="text-gray-500">
                                                    Sound Disabled
                                                </span>
                                                •
                                                <span v-if="pushEnabled" class="text-green-600 dark:text-green-400 font-medium">
                                                    ✓ Push Enabled
                                                </span>
                                                <span v-else-if="!pushSupported" class="text-red-500">
                                                    Push Not Supported
                                                </span>
                                                <span v-else class="text-gray-500">
                                                    Push Disabled
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </Transition>
                        </div>

                        <span
                            v-if="currentUser"
                            class="hidden sm:inline text-xs text-gray-600 dark:text-gray-300"
                            >{{ currentUser.username }}</span
                        >
                        <button
                            @click="logout"
                            class="text-[10px] sm:text-xs text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white"
                        >
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- System Notice Banner -->
        <div
            v-if="systemNotice && !isNoticeDismissed"
            class="py-3 px-4 text-center font-medium relative"
            :style="{
                backgroundColor: systemNotice.bg_color,
                color: systemNotice.text_color,
            }"
        >
            <div v-html="systemNotice.message"></div>
            <button
                @click="dismissNotice"
                class="absolute right-4 top-1/2 -translate-y-1/2 p-1 hover:opacity-75 transition-opacity"
                :style="{ color: systemNotice.text_color }"
                title="Dismiss notice"
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
                        d="M6 18L18 6M6 6l12 12"
                    />
                </svg>
            </button>
        </div>

        <!-- Stripe Onboarding Banner -->
        <div
            v-if="$page.props.needsStripeOnboarding"
            class="bg-blue-600 dark:bg-blue-700 text-white py-3 px-4 text-center font-medium shadow-md"
        >
            <div
                class="mx-auto flex items-center justify-center gap-3 flex-wrap"
            >
                <div class="flex items-center gap-2">
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
                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                        />
                    </svg>
                    <span
                        >Complete your Stripe Connect onboarding to accept
                        payments</span
                    >
                </div>
                <Link
                    :href="route('store.settings') + '#stripe'"
                    class="inline-flex items-center px-4 py-1.5 bg-white text-blue-600 text-sm font-semibold rounded-lg hover:bg-blue-50 transition-colors shadow-sm"
                >
                    Complete Setup
                    <svg
                        class="ml-2 w-4 h-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M13 7l5 5m0 0l-5 5m5-5H6"
                        />
                    </svg>
                </Link>
            </div>
        </div>

        <!-- Inactive Store Banner -->
        <div
            v-if="store && !store.is_active"
            class="bg-yellow-600 text-white py-3 px-4 text-center font-medium shadow-md"
        >
            <div class="mx-auto flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        fill-rule="evenodd"
                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd"
                    />
                </svg>
                <span><a href="/store/settings#basic">Your store is currently not accepting orders. Fix now ></a></span>
            </div>
        </div>

        <!-- Sidebar and Content -->
        <div class="flex">
            <!-- Desktop Sidebar (hidden on mobile) -->
            <aside
                class="hidden lg:block relative bg-white dark:bg-gray-800 shadow-sm min-h-screen transition-all pt-3 duration-300"
                :class="sidebarCollapsed ? 'w-20' : 'w-64'"
            >
                <!-- Sidebar Toggle Button - Positioned Outside Middle -->
                <button
                    @click="toggleSidebar"
                    class="absolute -right-3 top-1/2 transform -translate-y-1/2 p-1 bg-white dark:bg-gray-800 text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 rounded-full shadow-md border border-gray-200 dark:border-gray-700 transition-all duration-300 z-10"
                    :title="
                        sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'
                    "
                >
                    <svg
                        v-if="!sidebarCollapsed"
                        class="w-3 h-3"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        stroke-width="3"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M11 19l-7-7 7-7m8 14l-7-7 7-7"
                        />
                    </svg>
                    <svg
                        v-else
                        class="w-3 h-3"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        stroke-width="3"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M13 5l7 7-7 7M5 5l7 7-7 7"
                        />
                    </svg>
                </button>

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
                            <div
                                v-else
                                class="border-t border-gray-200 dark:border-gray-700 my-2"
                            ></div>
                            <div class="space-y-1">
                                <Link
                                    :href="route('dashboard')"
                                    class="flex items-center py-2 text-sm font-medium rounded-md transition-colors"
                                    :class="[
                                        isActive('dashboard')
                                            ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200'
                                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700',
                                        sidebarCollapsed
                                            ? 'justify-center px-2'
                                            : 'px-3',
                                    ]"
                                    :title="
                                        sidebarCollapsed ? 'Active Orders' : ''
                                    "
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
                                    <span v-if="!sidebarCollapsed" class="ml-3"
                                        >Active Orders</span
                                    >
                                </Link>
                                <Link
                                    :href="route('orders.history')"
                                    class="flex items-center py-2 text-sm font-medium rounded-md transition-colors"
                                    :class="[
                                        isActive('orders.history')
                                            ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200'
                                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700',
                                        sidebarCollapsed
                                            ? 'justify-center px-2'
                                            : 'px-3',
                                    ]"
                                    :title="
                                        sidebarCollapsed ? 'Order History' : ''
                                    "
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
                                    <span v-if="!sidebarCollapsed" class="ml-3"
                                        >Order History</span
                                    >
                                </Link>
                            </div>
                        </div>

                        <!-- Catalog Section -->
                        <div class="mb-4">
                            <h3
                                v-if="!sidebarCollapsed"
                                class="px-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2"
                            >
                                Catalog
                            </h3>
                            <div
                                v-else
                                class="border-t border-gray-200 dark:border-gray-700 my-2"
                            ></div>
                            <div class="space-y-1">
                                <Link
                                    :href="route('products.index')"
                                    class="flex items-center py-2 text-sm font-medium rounded-md transition-colors"
                                    :class="[
                                        isActive('products.index')
                                            ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200'
                                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700',
                                        sidebarCollapsed
                                            ? 'justify-center px-2'
                                            : 'px-3',
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
                                    <span v-if="!sidebarCollapsed" class="ml-3"
                                        >Products</span
                                    >
                                </Link>
                                <Link
                                    v-if="
                                        currentUser &&
                                        currentUser.role !== 'staff'
                                    "
                                    :href="route('categories.index')"
                                    class="flex items-center py-2 text-sm font-medium rounded-md transition-colors"
                                    :class="[
                                        isActive('categories.index')
                                            ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200'
                                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700',
                                        sidebarCollapsed
                                            ? 'justify-center px-2'
                                            : 'px-3',
                                    ]"
                                    :title="
                                        sidebarCollapsed ? 'Categories' : ''
                                    "
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
                                    <span v-if="!sidebarCollapsed" class="ml-3"
                                        >Categories</span
                                    >
                                </Link>
                            </div>
                        </div>

                        <!-- Customer Section -->
                        <div class="mb-4">
                            <h3
                                v-if="!sidebarCollapsed"
                                class="px-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2"
                            >
                                Customers
                            </h3>
                            <div
                                v-else
                                class="border-t border-gray-200 dark:border-gray-700 my-2"
                            ></div>
                            <div class="space-y-1">
                                <Link
                                    :href="route('customers.index')"
                                    class="flex items-center py-2 text-sm font-medium rounded-md transition-colors"
                                    :class="[
                                        isActive('customers.index')
                                            ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200'
                                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700',
                                        sidebarCollapsed
                                            ? 'justify-center px-2'
                                            : 'px-3',
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
                                    <span v-if="!sidebarCollapsed" class="ml-3"
                                        >Customers</span
                                    >
                                </Link>
                                <Link
                                    v-if="
                                        currentUser &&
                                        currentUser.role !== 'staff'
                                    "
                                    :href="route('loyalty.index')"
                                    class="flex items-center py-2 text-sm font-medium rounded-md transition-colors"
                                    :class="[
                                        isActive('loyalty.index')
                                            ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200'
                                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700',
                                        sidebarCollapsed
                                            ? 'justify-center px-2'
                                            : 'px-3',
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
                                    <span v-if="!sidebarCollapsed" class="ml-3"
                                        >Loyalty</span
                                    >
                                </Link>
                            </div>
                        </div>

                        <!-- Settings Section -->
                        <div class="mb-4">
                            <h3
                                v-if="!sidebarCollapsed"
                                class="px-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2"
                            >
                                Settings
                            </h3>
                            <div
                                v-else
                                class="border-t border-gray-200 dark:border-gray-700 my-2"
                            ></div>
                            <div class="space-y-1">
                                <!-- Store Settings with Hover Sub-menu -->
                                <div
                                    v-if="
                                        currentUser &&
                                        currentUser.role !== 'staff'
                                    "
                                    class="relative"
                                    @mouseenter="showSettingsSubmenu = true"
                                    @mouseleave="showSettingsSubmenu = false"
                                >
                                    <Link
                                        :href="route('store.settings')"
                                        class="flex items-center py-2 text-sm font-medium rounded-md transition-colors"
                                        :class="[
                                            isActive('store.settings')
                                                ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200'
                                                : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700',
                                            sidebarCollapsed
                                                ? 'justify-center px-2'
                                                : 'px-3',
                                        ]"
                                        :title="
                                            sidebarCollapsed
                                                ? 'Store Settings'
                                                : ''
                                        "
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
                                        <span
                                            v-if="!sidebarCollapsed"
                                            class="ml-3"
                                            >Store Settings</span
                                        >
                                        <svg
                                            v-if="!sidebarCollapsed"
                                            class="ml-auto w-4 h-4 transition-transform"
                                            :class="{
                                                'rotate-90':
                                                    showSettingsSubmenu,
                                            }"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 5l7 7-7 7"
                                            />
                                        </svg>
                                    </Link>

                                    <!-- Submenu -->
                                    <Transition name="submenu">
                                        <div
                                            v-if="
                                                showSettingsSubmenu &&
                                                !sidebarCollapsed
                                            "
                                            class="ml-8 mt-1 space-y-1 overflow-hidden"
                                        >
                                            <a
                                                :href="
                                                    route('store.settings') +
                                                    '#basic'
                                                "
                                                class="block px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-md transition-colors"
                                            >
                                                Store Settings
                                            </a>
                                            <a
                                                :href="
                                                    route('store.settings') +
                                                    '#contact'
                                                "
                                                class="block px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-md transition-colors"
                                            >
                                                Contact Information
                                            </a>
                                            <a
                                                :href="
                                                    route('store.settings') +
                                                    '#business'
                                                "
                                                class="block px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-md transition-colors"
                                            >
                                                Business Settings
                                            </a>
                                            <a
                                                v-if="
                                                    currentUser &&
                                                    currentUser.role === 'owner'
                                                "
                                                :href="
                                                    route('store.settings') +
                                                    '#stripe'
                                                "
                                                class="block px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-md transition-colors"
                                            >
                                                Stripe Connect
                                            </a>
                                            <a
                                                :href="
                                                    route('store.settings') +
                                                    '#data-migration'
                                                "
                                                class="block px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-md transition-colors"
                                            >
                                                Data Migration
                                            </a>
                                            <Link
                                                v-if="
                                                    currentUser &&
                                                    currentUser.role === 'owner'
                                                "
                                                :href="route('subscriptions.index')"
                                                class="block px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-md transition-colors"
                                            >
                                                Subscription
                                            </Link>
                                        </div>
                                    </Transition>
                                </div>
                                <Link
                                    v-if="
                                        currentUser &&
                                        currentUser.role !== 'staff'
                                    "
                                    :href="route('shipping.index')"
                                    class="flex items-center py-2 text-sm font-medium rounded-md transition-colors"
                                    :class="[
                                        isActive('shipping.index')
                                            ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200'
                                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700',
                                        sidebarCollapsed
                                            ? 'justify-center px-2'
                                            : 'px-3',
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
                                    <span v-if="!sidebarCollapsed" class="ml-3"
                                        >Shipping</span
                                    >
                                </Link>
                                <Link
                                    v-if="
                                        currentUser &&
                                        currentUser.role === 'owner'
                                    "
                                    :href="route('staff.index')"
                                    class="flex items-center py-2 text-sm font-medium rounded-md transition-colors"
                                    :class="[
                                        isActive('staff.index')
                                            ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200'
                                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700',
                                        sidebarCollapsed
                                            ? 'justify-center px-2'
                                            : 'px-3',
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
                                    <span v-if="!sidebarCollapsed" class="ml-3"
                                        >Staff</span
                                    >
                                </Link>
                                <Link
                                    v-if="
                                        currentUser &&
                                        currentUser.role !== 'staff'
                                    "
                                    :href="route('audit-logs.index')"
                                    class="flex items-center py-2 text-sm font-medium rounded-md transition-colors"
                                    :class="[
                                        isActive('audit-logs.index')
                                            ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200'
                                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700',
                                        sidebarCollapsed
                                            ? 'justify-center px-2'
                                            : 'px-3',
                                    ]"
                                    :title="
                                        sidebarCollapsed ? 'Audit Logs' : ''
                                    "
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
                                    <span v-if="!sidebarCollapsed" class="ml-3"
                                        >Audit Logs</span
                                    >
                                </Link>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- Dark Mode Toggle - Fixed at Bottom -->
                <div
                    class="fixed bottom-4 transition-all duration-300 flex justify-center"
                    :style="{ width: sidebarCollapsed ? '80px' : '256px' }"
                >
                    <button
                        @click="toggleDarkMode"
                        class="relative inline-flex items-center rounded-full transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        :class="[
                            isDark ? 'bg-gray-600' : 'bg-blue-500',
                            sidebarCollapsed ? 'h-7 w-14' : 'h-8 w-16',
                        ]"
                        :title="
                            isDark
                                ? 'Switch to Light Mode'
                                : 'Switch to Dark Mode'
                        "
                    >
                        <!-- Track -->
                        <span class="sr-only">Toggle theme</span>

                        <!-- Slider with Icon -->
                        <span
                            class="absolute inline-flex items-center justify-center rounded-full bg-white shadow-lg transform transition-all duration-500 ease-in-out"
                            :class="[
                                sidebarCollapsed
                                    ? 'left-0.5 h-5 w-5'
                                    : 'left-0.5 h-6 w-6',
                                isDark
                                    ? sidebarCollapsed
                                        ? 'translate-x-[30px] rotate-[360deg]'
                                        : 'translate-x-[34px] rotate-[360deg]'
                                    : 'translate-x-0 rotate-0',
                            ]"
                        >
                            <!-- Sun Icon (Light Mode) -->
                            <svg
                                v-if="!isDark"
                                class="text-yellow-500 transition-transform duration-500"
                                :class="
                                    sidebarCollapsed ? 'w-3 h-3' : 'w-4 h-4'
                                "
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            <!-- Moon Icon (Dark Mode) -->
                            <svg
                                v-else
                                class="text-gray-700 transition-transform duration-500"
                                :class="
                                    sidebarCollapsed ? 'w-3 h-3' : 'w-4 h-4'
                                "
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"
                                />
                            </svg>
                        </span>

                        <!-- Background Icons -->
                        <div
                            class="absolute inset-0 flex items-center pointer-events-none"
                        >
                            <!-- Moon background icon (shows in light mode - right side, where slider will move to) -->
                            <svg
                                class="absolute transition-opacity duration-300"
                                :class="[
                                    !isDark
                                        ? 'opacity-60 text-gray-400'
                                        : 'opacity-0',
                                    sidebarCollapsed
                                        ? 'w-3 h-3 right-1.5'
                                        : 'w-3.5 h-3.5 right-2',
                                ]"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"
                                />
                            </svg>
                            <!-- Sun background icon (shows in dark mode - left side, where slider will move to) -->
                            <svg
                                class="absolute transition-opacity duration-300"
                                :class="[
                                    isDark
                                        ? 'opacity-60 text-yellow-300'
                                        : 'opacity-0',
                                    sidebarCollapsed
                                        ? 'w-3 h-3 left-1.5'
                                        : 'w-3.5 h-3.5 left-2',
                                ]"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                    </button>
                </div>
            </aside>

            <!-- Mobile Sidebar Overlay -->
            <Transition name="sidebar">
                <div
                    v-if="mobileSidebarOpen"
                    class="lg:hidden fixed inset-0 z-50"
                    @click="mobileSidebarOpen = false"
                >
                    <!-- Backdrop -->
                    <div class="absolute inset-0 bg-black bg-opacity-50"></div>

                    <!-- Sidebar Content -->
                    <aside
                        class="absolute left-0 top-0 bottom-0 w-64 bg-white dark:bg-gray-800 shadow-xl overflow-y-auto"
                        @click.stop
                    >
                        <!-- Close Button -->
                        <div
                            class="flex justify-end p-4 border-b border-gray-200 dark:border-gray-700"
                        >
                            <button
                                @click="mobileSidebarOpen = false"
                                class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors"
                            >
                                <svg
                                    class="w-6 h-6"
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

                        <nav class="px-3 pb-20">
                            <div class="space-y-1">
                                <!-- Operations Section -->
                                <div class="mb-4">
                                    <h3
                                        class="px-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2"
                                    >
                                        Operations
                                    </h3>
                                    <div class="space-y-1">
                                        <Link
                                            :href="route('dashboard')"
                                            @click="mobileSidebarOpen = false"
                                            class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors"
                                            :class="
                                                isActive('dashboard')
                                                    ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200'
                                                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                                            "
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
                                            <span class="ml-3"
                                                >Active Orders</span
                                            >
                                        </Link>
                                        <Link
                                            :href="route('orders.history')"
                                            @click="mobileSidebarOpen = false"
                                            class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors"
                                            :class="
                                                isActive('orders.history')
                                                    ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200'
                                                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                                            "
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
                                            <span class="ml-3"
                                                >Order History</span
                                            >
                                        </Link>
                                    </div>
                                </div>

                                <!-- Catalog Section -->
                                <div class="mb-4">
                                    <h3
                                        class="px-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2"
                                    >
                                        Catalog
                                    </h3>
                                    <div class="space-y-1">
                                        <Link
                                            :href="route('products.index')"
                                            @click="mobileSidebarOpen = false"
                                            class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors"
                                            :class="
                                                isActive('products.index')
                                                    ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200'
                                                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                                            "
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
                                            <span class="ml-3">Products</span>
                                        </Link>
                                        <Link
                                            v-if="
                                                currentUser &&
                                                currentUser.role !== 'staff'
                                            "
                                            :href="route('categories.index')"
                                            @click="mobileSidebarOpen = false"
                                            class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors"
                                            :class="
                                                isActive('categories.index')
                                                    ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200'
                                                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                                            "
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
                                            <span class="ml-3">Categories</span>
                                        </Link>
                                    </div>
                                </div>

                                <!-- Customer Section -->
                                <div class="mb-4">
                                    <h3
                                        class="px-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2"
                                    >
                                        Customers
                                    </h3>
                                    <div class="space-y-1">
                                        <Link
                                            :href="route('customers.index')"
                                            @click="mobileSidebarOpen = false"
                                            class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors"
                                            :class="
                                                isActive('customers.index')
                                                    ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200'
                                                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                                            "
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
                                            <span class="ml-3">Customers</span>
                                        </Link>
                                        <Link
                                            v-if="
                                                currentUser &&
                                                currentUser.role !== 'staff'
                                            "
                                            :href="route('loyalty.index')"
                                            @click="mobileSidebarOpen = false"
                                            class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors"
                                            :class="
                                                isActive('loyalty.index')
                                                    ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200'
                                                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                                            "
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
                                            <span class="ml-3">Loyalty</span>
                                        </Link>
                                    </div>
                                </div>

                                <!-- Settings Section -->
                                <div class="mb-4">
                                    <h3
                                        class="px-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2"
                                    >
                                        Settings
                                    </h3>
                                    <div class="space-y-1">
                                        <Link
                                            v-if="
                                                currentUser &&
                                                currentUser.role !== 'staff'
                                            "
                                            :href="route('store.settings')"
                                            @click="mobileSidebarOpen = false"
                                            class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors"
                                            :class="
                                                isActive('store.settings')
                                                    ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200'
                                                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                                            "
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
                                            <span class="ml-3"
                                                >Store Settings</span
                                            >
                                        </Link>
                                        <Link
                                            v-if="
                                                currentUser &&
                                                currentUser.role !== 'staff'
                                            "
                                            :href="route('shipping.index')"
                                            @click="mobileSidebarOpen = false"
                                            class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors"
                                            :class="
                                                isActive('shipping.index')
                                                    ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200'
                                                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                                            "
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
                                            <span class="ml-3">Shipping</span>
                                        </Link>
                                        <Link
                                            v-if="
                                                currentUser &&
                                                currentUser.role === 'owner'
                                            "
                                            :href="route('staff.index')"
                                            @click="mobileSidebarOpen = false"
                                            class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors"
                                            :class="
                                                isActive('staff.index')
                                                    ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200'
                                                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                                            "
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
                                            <span class="ml-3">Staff</span>
                                        </Link>
                                        <Link
                                            v-if="
                                                currentUser &&
                                                currentUser.role !== 'staff'
                                            "
                                            :href="route('audit-logs.index')"
                                            @click="mobileSidebarOpen = false"
                                            class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors"
                                            :class="
                                                isActive('audit-logs.index')
                                                    ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200'
                                                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                                            "
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
                                            <span class="ml-3">Audit Logs</span>
                                        </Link>
                                        <Link
                                            v-if="
                                                currentUser &&
                                                currentUser.role === 'owner'
                                            "
                                            :href="route('subscriptions.index')"
                                            @click="mobileSidebarOpen = false"
                                            class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors"
                                            :class="
                                                isActive('subscriptions.index')
                                                    ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200'
                                                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                                            "
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
                                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                                                />
                                            </svg>
                                            <span class="ml-3">Subscription</span>
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </nav>
                    </aside>
                </div>
            </Transition>

            <!-- Main Content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 bg-gray-50 dark:bg-gray-900">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { Link, router, usePage } from "@inertiajs/vue3";
import { ref, computed, onMounted, onBeforeUnmount, watch } from "vue";
import ToastContainer from "@/Components/Notifications/ToastContainer.vue";
import OnboardingModal from "@/Components/OnboardingModal.vue";
import { useNotifications } from "@/Composables/useNotifications";
import { useDarkMode } from "@/Composables/useDarkMode";
import { usePushNotifications } from "@/Composables/usePushNotifications";

const props = defineProps({
    store: Object,
    user: Object,
});

const page = usePage();
const needsOnboarding = computed(() => page.props.needsOnboarding || false);
const systemNotice = computed(() => page.props.systemNotice || null);

// Notice dismissal state
const isNoticeDismissed = ref(false);

// Check if notice has been dismissed
const checkNoticeDismissed = () => {
    if (!systemNotice.value) return;

    const dismissedNoticeId = localStorage.getItem("dismissedNoticeId");
    if (
        dismissedNoticeId &&
        dismissedNoticeId === String(systemNotice.value.id)
    ) {
        isNoticeDismissed.value = true;
    } else {
        isNoticeDismissed.value = false;
    }
};

// Dismiss the notice
const dismissNotice = () => {
    if (!systemNotice.value) return;

    localStorage.setItem("dismissedNoticeId", String(systemNotice.value.id));
    isNoticeDismissed.value = true;
};

// Use auth user from Inertia shared props if not passed as prop
const currentUser = computed(() => props.user || page.props.auth?.user);
const currentStore = computed(() => props.store || page.props.store);
const { success, error } = useNotifications();

// Store URL and copy functionality
const storeUrl = computed(() => {
    if (!currentStore.value?.id) return "";
    return `${window.location.origin}/store/${currentStore.value.id}`;
});

const linkCopied = ref(false);

// Sound notification permission (managed by bell icon)
const soundEnabled = ref(false);

// Push notification management
const {
    isSupported: pushSupported,
    isSubscribed: pushEnabled,
    subscribe: subscribePush,
    unsubscribe: unsubscribePush,
    checkSubscription
} = usePushNotifications();

// Check if sound permission has been set
onMounted(() => {
    const soundPref = localStorage.getItem("soundNotificationsEnabled");
    soundEnabled.value = soundPref === "true";

    // Check push notification subscription status
    checkSubscription();

    // Check if system notice has been dismissed
    checkNoticeDismissed();
});

const enableSoundNotifications = async () => {
    try {
        // Play a silent sound to unlock audio context
        const audio = new Audio("/sounds/notification.wav");
        audio.volume = 0.01;
        await audio.play();
        audio.pause();

        soundEnabled.value = true;
        localStorage.setItem("soundNotificationsEnabled", "true");
        success(
            "Sound Enabled!",
            "You will now hear audio alerts for new orders"
        );
    } catch (error) {
        console.error("Failed to enable sound:", error);
    }
};

const toggleSoundNotifications = async () => {
    if (!soundEnabled.value) {
        // Enabling sound
        await enableSoundNotifications();
    } else {
        // Disabling sound
        soundEnabled.value = false;
        localStorage.setItem("soundNotificationsEnabled", "false");
        success("Sound Disabled", "Audio alerts are now turned off");
    }
};

const togglePushNotifications = async () => {
    try {
        if (pushEnabled.value) {
            // Disabling push notifications
            await unsubscribePush();
            success('Push Notifications Disabled', 'You will no longer receive notifications when the app is closed');
        } else {
            // Enabling push notifications
            await subscribePush();
            success('Push Notifications Enabled!', 'You will now receive notifications even when the app is closed');
        }
    } catch (err) {
        console.error('Push notification error:', err);

        // Provide user-friendly error messages
        let errorMessage = 'Failed to update push notifications';
        if (err.message && err.message.includes('permission denied')) {
            errorMessage = 'Notification permission was denied. Please enable notifications in your browser settings.';
        } else if (err.message && err.message.includes('not supported')) {
            errorMessage = 'Push notifications are not supported on this device or browser.';
        } else if (err.message) {
            errorMessage = err.message;
        }

        error('Push Notifications', errorMessage);
    }
};

const copyStoreLink = async () => {
    try {
        await navigator.clipboard.writeText(storeUrl.value);
        linkCopied.value = true;
        success("Link Copied!", "Store link copied to clipboard");

        // Reset checkmark after 2 seconds
        setTimeout(() => {
            linkCopied.value = false;
        }, 2000);
    } catch (err) {
        console.error("Failed to copy link:", err);
    }
};

// Dark mode
const { isDark, toggleDarkMode, initializeDarkMode } = useDarkMode();

// Sidebar collapse state (persisted in localStorage)
const sidebarCollapsed = ref(false);

// Mobile sidebar state
const mobileSidebarOpen = ref(false);

// Settings submenu state
const showSettingsSubmenu = ref(false);

// Bell notification dropdown state
const showBellDropdown = ref(false);

// Toggle sidebar collapsed state
const toggleSidebar = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value;
    localStorage.setItem(
        "sidebarCollapsed",
        JSON.stringify(sidebarCollapsed.value)
    );
};

// Toggle mobile sidebar
const toggleMobileSidebar = () => {
    mobileSidebarOpen.value = !mobileSidebarOpen.value;
};

// Close bell dropdown when clicking outside
const handleClickOutside = (event) => {
    const bellButton = event.target.closest('button[title="Notification Settings"]');
    const bellDropdown = event.target.closest('.absolute.right-0.mt-2.w-72');

    if (!bellButton && !bellDropdown && showBellDropdown.value) {
        showBellDropdown.value = false;
    }
};

// Watch systemNotice for changes to re-check dismissal state
watch(systemNotice, () => {
    checkNoticeDismissed();
});

// Component lifecycle
onMounted(() => {
    // Initialize dark mode
    initializeDarkMode();

    // Load sidebar state from localStorage
    const savedState = localStorage.getItem("sidebarCollapsed");
    if (savedState !== null) {
        sidebarCollapsed.value = JSON.parse(savedState);
    }

    // Add click outside listener for bell dropdown
    document.addEventListener('click', handleClickOutside);

    // WebSocket setup for real-time order notifications
    if (typeof window.Echo !== "undefined" && props.store?.id) {
        // Listen for new orders on the private store channel
        window.Echo.private(`store.${props.store.id}.orders`).listen(
            ".OrderCreated",
            (e) => {
                // Show notification
                success(
                    "New Order!",
                    `Order ${e.order.public_id} placed for ${e.order.customer_name}`,
                    10000 // Show for 10 seconds
                );

                // Optional: Play notification sound (different sound for shipping vs pickup)
                playNotificationSound(e.order.fulfilment_type);

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

    // Remove click outside listener
    document.removeEventListener('click', handleClickOutside);
});

// Notification sound
const playNotificationSound = (fulfilmentType = 'pickup') => {
    // Only play if user has enabled sound
    if (!soundEnabled.value) return;

    try {
        // Use bell.mp3 for shipping orders, notification.wav for pickup
        const soundFile = fulfilmentType === 'shipping'
            ? "/sounds/bell.mp3"
            : "/sounds/notification.wav";

        const audio = new Audio(soundFile);
        audio.volume = 0.7;
        audio.play().catch((e) => {
            // Browser blocked autoplay - user needs to enable via bell icon
            console.log("Audio autoplay blocked. Please enable via notification bell icon.");
        });
    } catch (error) {
        console.error("Notification sound error:", error);
    }
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

<style scoped>
/* Sidebar slide animation */
.sidebar-enter-active,
.sidebar-leave-active {
    transition: opacity 0.3s ease;
}

.sidebar-enter-active .absolute.left-0,
.sidebar-leave-active .absolute.left-0 {
    transition: transform 0.3s ease;
}

.sidebar-enter-from,
.sidebar-leave-to {
    opacity: 0;
}

.sidebar-enter-from .absolute.left-0 {
    transform: translateX(-100%);
}

.sidebar-leave-to .absolute.left-0 {
    transform: translateX(-100%);
}

/* Submenu slide down animation */
.submenu-enter-active,
.submenu-leave-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.submenu-enter-from {
    opacity: 0;
    transform: translateY(-8px);
    max-height: 0;
}

.submenu-enter-to {
    opacity: 1;
    transform: translateY(0);
    max-height: 500px;
}

.submenu-leave-from {
    opacity: 1;
    transform: translateY(0);
    max-height: 500px;
}

.submenu-leave-to {
    opacity: 0;
    transform: translateY(-8px);
    max-height: 0;
}

/* Dropdown animation for bell notifications */
.dropdown-enter-active,
.dropdown-leave-active {
    transition: all 0.2s ease;
}

.dropdown-enter-from {
    opacity: 0;
    transform: translateY(-8px) scale(0.95);
}

.dropdown-enter-to {
    opacity: 1;
    transform: translateY(0) scale(1);
}

.dropdown-leave-from {
    opacity: 1;
    transform: translateY(0) scale(1);
}

.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-8px) scale(0.95);
}
</style>
