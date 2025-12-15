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
            class="sticky top-0 z-20"
            :class="
                store.theme === 'bold'
                    ? 'bg-gray-900 border-b border-orange-500/20'
                    : 'bg-white shadow-sm'
            "
        >
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex justify-between items-center">
                    <!-- Mobile: Burger Menu + Search + Logo -->
                    <div class="flex items-center gap-3 lg:hidden">
                        <!-- Burger Menu Button -->
                        <button
                            @click="toggleMobileMenu"
                            class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
                            aria-label="Menu"
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
                                    d="M4 6h16M4 12h16M4 18h16"
                                />
                            </svg>
                        </button>

                        <!-- Search Button -->
                        <button
                            @click="toggleSearch"
                            class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
                            aria-label="Search"
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
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                />
                            </svg>
                        </button>
                    </div>

                    <!-- Store Logo/Name -->
                    <div class="flex items-center">
                        <img
                            v-if="store.logo_url"
                            :src="store.logo_url"
                            :alt="store.name"
                            class="max-h-12 max-w-xs object-contain"
                        />
                        <h1
                            v-else
                            class="text-2xl font-bold"
                            :class="
                                store.theme === 'bold'
                                    ? 'text-white'
                                    : 'text-gray-900'
                            "
                        >
                            {{ store.name }}
                        </h1>
                    </div>

                    <!-- Desktop Search Bar (hidden on mobile) -->
                    <div class="hidden lg:block flex-1 max-w-md mx-4">
                        <div class="relative">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search products..."
                                class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:border-transparent"
                                :class="[
                                    store.theme === 'bold'
                                        ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-400 focus:ring-orange-500'
                                        : store.theme === 'modern'
                                        ? 'bg-white border-purple-200 text-gray-900 placeholder-gray-400 focus:ring-purple-500'
                                        : 'bg-white border-gray-300 text-gray-900 placeholder-gray-400 focus:ring-blue-500',
                                ]"
                            />
                            <svg
                                class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5"
                                :class="
                                    store.theme === 'bold'
                                        ? 'text-gray-400'
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
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                />
                            </svg>
                            <button
                                v-if="searchQuery"
                                @click="searchQuery = ''"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2"
                                :class="[
                                    store.theme === 'bold'
                                        ? 'text-gray-400 hover:text-gray-300'
                                        : 'text-gray-400 hover:text-gray-600',
                                ]"
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
                    </div>

                    <!-- Header Actions -->
                    <div class="flex items-center gap-1.5 sm:gap-2">
                        <!-- Track Order Button -->
                        <a
                            :href="`/store/${store.id}/track-order`"
                            class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-2 sm:px-4 sm:py-2 rounded-lg font-medium transition-all duration-200"
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
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"
                                />
                            </svg>
                            <span class="hidden md:inline">Track Order</span>
                        </a>

                        <!-- Profile/Login Button -->
                        <a
                            v-if="!customer"
                            :href="`/store/${store.id}/login`"
                            class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-2 sm:px-4 sm:py-2 rounded-lg font-medium transition-all duration-200"
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
                            class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-2 sm:px-4 sm:py-2 rounded-lg font-medium transition-all duration-200"
                            :class="[
                                store.theme === 'bold'
                                    ? 'bg-gradient-to-r from-orange-500 to-yellow-500 text-white hover:from-orange-600 hover:to-yellow-600 shadow-lg'
                                    : store.theme === 'modern'
                                    ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white hover:from-purple-700 hover:to-pink-700 shadow-lg'
                                    : store.theme === 'monochrome'
                                    ? 'bg-gray-900/90 backdrop-blur-xl text-white hover:bg-gray-900 shadow-lg'
                                    : 'bg-blue-600 text-white hover:bg-blue-700',
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
                            <span class="hidden md:inline">{{
                                customer.first_name
                            }}</span>
                        </a>

                        <!-- Cart Button -->
                        <CartButton :theme="store.theme" />
                    </div>
                </div>
            </div>

            <!-- Search Bar (Mobile & Desktop) -->
            <Transition
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="max-h-0 opacity-0"
                enter-to-class="max-h-20 opacity-100"
                leave-active-class="transition-all duration-300 ease-in"
                leave-from-class="max-h-20 opacity-100"
                leave-to-class="max-h-0 opacity-0"
            >
                <div
                    v-if="isSearchOpen"
                    class="border-t border-gray-200 px-4 py-3 overflow-hidden"
                >
                    <div class="relative">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search products..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            autofocus
                        />
                        <svg
                            class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                            />
                        </svg>
                        <button
                            v-if="searchQuery"
                            @click="searchQuery = ''"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
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
                </div>
            </Transition>

            <!-- Mobile Horizontal Category Chips -->
            <div
                v-if="categories && categories.length > 0 && !searchQuery"
                class="lg:hidden border-t border-gray-200 overflow-x-auto scrollbar-hide"
            >
                <div class="flex gap-2 px-4 py-3 min-w-max">
                    <button
                        v-for="category in categories"
                        :key="category.id"
                        @click="scrollToCategory(category.slug)"
                        :class="[
                            'px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors flex-shrink-0',
                            activeCategory === category.slug
                                ? 'bg-gray-900 text-white'
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                        ]"
                    >
                        {{ category.name }}
                    </button>
                </div>
            </div>
        </header>

        <!-- Mobile Sidebar Menu Overlay -->
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isMobileMenuOpen"
                class="fixed inset-0 bg-black bg-opacity-50 z-30 lg:hidden"
                @click="toggleMobileMenu"
            ></div>
        </Transition>

        <!-- Mobile Sidebar Menu -->
        <Transition
            enter-active-class="transition-transform duration-300 ease-out"
            enter-from-class="-translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transition-transform duration-300 ease-in"
            leave-from-class="translate-x-0"
            leave-to-class="-translate-x-full"
        >
            <aside
                v-if="isMobileMenuOpen"
                class="fixed top-0 left-0 h-full w-80 shadow-xl z-40 overflow-y-auto lg:hidden"
                :class="store.theme === 'bold' ? 'bg-gray-900' : 'bg-white'"
            >
                <div class="p-6">
                    <!-- Close Button -->
                    <div class="flex justify-between items-center mb-6">
                        <h2
                            class="text-xl font-bold"
                            :class="
                                store.theme === 'bold'
                                    ? 'text-white'
                                    : 'text-gray-900'
                            "
                        >
                            Menu
                        </h2>
                        <button
                            @click="toggleMobileMenu"
                            class="p-2 rounded-lg transition-colors"
                            :class="[
                                store.theme === 'bold'
                                    ? 'text-gray-300 hover:text-white hover:bg-gray-800'
                                    : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100',
                            ]"
                            aria-label="Close menu"
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

                    <!-- Operating Hours -->
                    <div
                        v-if="store.open_time && store.close_time"
                        class="mb-4"
                    >
                        <div
                            class="flex items-center gap-2 text-sm"
                            :class="
                                store.theme === 'bold'
                                    ? 'text-gray-300'
                                    : 'text-gray-600'
                            "
                        >
                            <svg
                                class="w-4 h-4 flex-shrink-0"
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
                            <span class="font-medium"
                                >{{ formatTime(store.open_time) }} -
                                {{ formatTime(store.close_time) }}</span
                            >
                        </div>
                    </div>

                    <!-- Store Address -->
                    <div
                        v-if="store.address_primary && store.address_city"
                        class="mb-6 pb-4 border-b"
                        :class="
                            store.theme === 'bold'
                                ? 'border-gray-700'
                                : 'border-gray-200'
                        "
                    >
                        <div
                            class="flex items-start gap-2 text-sm mb-2"
                            :class="
                                store.theme === 'bold'
                                    ? 'text-gray-300'
                                    : 'text-gray-600'
                            "
                        >
                            <svg
                                class="w-4 h-4 flex-shrink-0 mt-0.5"
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
                            <div class="flex-1">
                                <p
                                    :class="
                                        store.theme === 'bold'
                                            ? 'text-gray-300'
                                            : 'text-gray-700'
                                    "
                                >
                                    {{ store.address_primary }}
                                </p>
                                <p
                                    :class="
                                        store.theme === 'bold'
                                            ? 'text-gray-300'
                                            : 'text-gray-700'
                                    "
                                >
                                    {{ store.address_city }},
                                    {{ store.address_state }}
                                    {{ store.address_postcode }}
                                </p>
                            </div>
                        </div>
                        <a
                            :href="getGoogleMapsUrl()"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 text-sm font-medium transition-colors"
                            :class="[
                                store.theme === 'bold'
                                    ? 'text-orange-400 hover:text-orange-300'
                                    : store.theme === 'modern'
                                    ? 'text-purple-600 hover:text-purple-700'
                                    : 'text-blue-600 hover:text-blue-700',
                            ]"
                        >
                            <svg
                                class="w-4 h-4"
                                fill="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"
                                />
                            </svg>
                            Open in Google Maps
                        </a>
                    </div>

                    <!-- Categories -->
                    <nav class="space-y-1">
                        <div v-for="category in categories" :key="category.id">
                            <a
                                :href="`#${category.slug}`"
                                @click.prevent="
                                    scrollToCategoryAndClose(category.slug)
                                "
                                class="block px-3 py-2 text-sm font-medium rounded transition-colors"
                                :class="[
                                    activeCategory === category.slug
                                        ? store.theme === 'bold'
                                            ? 'bg-gray-700 text-white font-semibold'
                                            : store.theme === 'modern'
                                            ? 'bg-purple-100 text-purple-900 font-semibold'
                                            : store.theme === 'monochrome'
                                            ? 'bg-gray-900 text-white font-semibold'
                                            : 'bg-blue-100 text-blue-900 font-semibold'
                                        : store.theme === 'bold'
                                        ? 'text-gray-300 hover:bg-gray-700 hover:text-white'
                                        : store.theme === 'modern'
                                        ? 'text-gray-700 hover:bg-purple-50'
                                        : store.theme === 'monochrome'
                                        ? 'text-gray-700 hover:bg-gray-100'
                                        : 'text-gray-700 hover:bg-gray-100',
                                ]"
                            >
                                {{ category.name }}
                            </a>
                        </div>
                    </nav>

                    <!-- User Links in Mobile Menu -->
                    <div
                        class="mt-6 pt-6 border-t space-y-2"
                        :class="
                            store.theme === 'bold'
                                ? 'border-gray-700'
                                : 'border-gray-200'
                        "
                    >
                        <a
                            :href="`/store/${store.id}/track-order`"
                            class="flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-colors"
                            :class="[
                                store.theme === 'bold'
                                    ? 'text-white hover:bg-gray-800 border border-gray-700'
                                    : store.theme === 'modern'
                                    ? 'text-purple-700 hover:bg-purple-50 border border-purple-200'
                                    : 'text-blue-700 hover:bg-blue-50 border border-blue-200',
                            ]"
                        >
                            <svg
                                class="w-4 h-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"
                                />
                            </svg>
                            Track Order
                        </a>
                        <a
                            v-if="!customer"
                            :href="`/store/${store.id}/login`"
                            class="flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-colors"
                            :class="[
                                store.theme === 'bold'
                                    ? 'bg-gradient-to-r from-orange-500 to-yellow-500 text-white hover:from-orange-600 hover:to-yellow-600 shadow-lg'
                                    : store.theme === 'modern'
                                    ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white hover:from-purple-700 hover:to-pink-700 shadow-lg'
                                    : 'bg-blue-600 text-white hover:bg-blue-700',
                            ]"
                        >
                            <svg
                                class="w-4 h-4"
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
                            Login
                        </a>
                        <a
                            v-else
                            :href="`/store/${store.id}/profile`"
                            class="flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-colors"
                            :class="[
                                store.theme === 'bold'
                                    ? 'bg-gradient-to-r from-orange-500 to-yellow-500 text-white hover:from-orange-600 hover:to-yellow-600 shadow-lg'
                                    : store.theme === 'modern'
                                    ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white hover:from-purple-700 hover:to-pink-700 shadow-lg'
                                    : 'bg-blue-600 text-white hover:bg-blue-700',
                            ]"
                        >
                            <svg
                                class="w-4 h-4"
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
                            {{ customer.first_name }}
                        </a>
                    </div>
                </div>
            </aside>
        </Transition>

        <!-- Cart Drawer -->
        <CartDrawer :store="store" />

        <!-- Main Layout: Sidebar + Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex gap-8">
                <!-- Left Sidebar Navigation -->
                <aside class="w-64 flex-shrink-0 hidden lg:block">
                    <div
                        :class="[
                            themeConfig.cardBackground,
                            'rounded-lg shadow-sm p-6 sticky top-24',
                        ]"
                    >
                        <h2
                            class="text-xl font-bold mb-2"
                            :class="
                                store.theme === 'bold'
                                    ? 'text-white'
                                    : 'text-gray-900'
                            "
                        >
                            Menu
                        </h2>

                        <!-- Operating Hours -->
                        <div
                            v-if="store.open_time && store.close_time"
                            class="mb-4"
                        >
                            <div
                                class="flex items-center gap-2 text-sm"
                                :class="
                                    store.theme === 'bold'
                                        ? 'text-gray-300'
                                        : 'text-gray-600'
                                "
                            >
                                <svg
                                    class="w-4 h-4 flex-shrink-0"
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
                                <span class="font-medium"
                                    >{{ formatTime(store.open_time) }} -
                                    {{ formatTime(store.close_time) }}</span
                                >
                            </div>
                        </div>

                        <!-- Store Address -->
                        <div
                            v-if="store.address_primary && store.address_city"
                            class="mb-6 pb-4"
                            :class="
                                store.theme === 'bold'
                                    ? 'border-b border-gray-700'
                                    : 'border-b border-gray-200'
                            "
                        >
                            <div
                                class="flex items-start gap-2 text-sm mb-2"
                                :class="
                                    store.theme === 'bold'
                                        ? 'text-gray-300'
                                        : 'text-gray-600'
                                "
                            >
                                <svg
                                    class="w-4 h-4 flex-shrink-0 mt-0.5"
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
                                <div class="flex-1">
                                    <p
                                        :class="
                                            store.theme === 'bold'
                                                ? 'text-gray-300'
                                                : 'text-gray-700'
                                        "
                                    >
                                        {{ store.address_primary }}
                                    </p>
                                    <p
                                        :class="
                                            store.theme === 'bold'
                                                ? 'text-gray-300'
                                                : 'text-gray-700'
                                        "
                                    >
                                        {{ store.address_city }},
                                        {{ store.address_state }}
                                        {{ store.address_postcode }}
                                    </p>
                                </div>
                            </div>
                            <a
                                :href="getGoogleMapsUrl()"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-2 text-sm font-medium transition-colors"
                                :class="[
                                    store.theme === 'bold'
                                        ? 'text-orange-400 hover:text-orange-300'
                                        : store.theme === 'modern'
                                        ? 'text-purple-600 hover:text-purple-700'
                                        : 'text-blue-600 hover:text-blue-700',
                                ]"
                            >
                                <svg
                                    class="w-4 h-4"
                                    fill="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"
                                    />
                                </svg>
                                Open in Google Maps
                            </a>
                        </div>

                        <nav class="space-y-1">
                            <!-- Categories -->
                            <div
                                v-for="category in categories"
                                :key="category.id"
                            >
                                <a
                                    :href="`#${category.slug}`"
                                    @click.prevent="
                                        scrollToCategory(category.slug)
                                    "
                                    class="block px-3 py-2 text-sm font-medium rounded transition-colors"
                                    :class="[
                                        activeCategory === category.slug
                                            ? store.theme === 'bold'
                                                ? 'bg-gray-700 text-white font-semibold'
                                                : store.theme === 'modern'
                                                ? 'bg-purple-100 text-purple-900 font-semibold'
                                                : store.theme === 'monochrome'
                                                ? 'bg-gray-900 text-white font-semibold'
                                                : 'bg-blue-100 text-blue-900 font-semibold'
                                            : store.theme === 'bold'
                                            ? 'text-gray-300 hover:bg-gray-700 hover:text-white'
                                            : store.theme === 'modern'
                                            ? 'text-gray-700 hover:bg-purple-50'
                                            : store.theme === 'monochrome'
                                            ? 'text-gray-700 hover:bg-gray-100'
                                            : 'text-gray-700 hover:bg-gray-100',
                                    ]"
                                >
                                    {{ category.name }}
                                </a>
                            </div>
                        </nav>
                    </div>
                </aside>

                <!-- Main Content Area -->
                <main class="flex-1 min-w-0">
                    <!-- Dynamic Hero Section -->
                    <div
                        v-if="!searchQuery"
                        class="hero-gradient relative overflow-hidden rounded-2xl mb-8 shadow-2xl"
                    >
                        <div class="relative z-10 px-8 py-12 md:px-12 md:py-16">
                            <div class="max-w-3xl">
                                <!-- Time-based greeting -->
                                <div class="flex items-center gap-3 mb-4">
                                    <span class="text-4xl">{{
                                        greetingEmoji
                                    }}</span>
                                    <h2
                                        class="text-3xl md:text-4xl font-bold text-white"
                                    >
                                        {{ timeBasedGreeting }}
                                    </h2>
                                </div>
                                <p
                                    class="text-lg md:text-xl text-white/90 mb-6"
                                >
                                    {{ timeBasedTagline }}
                                </p>
                                <div class="flex flex-wrap gap-3">
                                    <button
                                        v-for="category in categories.slice(
                                            0,
                                            4
                                        )"
                                        :key="category.id"
                                        @click="scrollToCategory(category.slug)"
                                        class="hero-badge px-6 py-2.5 bg-white/20 backdrop-blur-sm text-white rounded-full font-medium hover:bg-white/30 transition-all duration-300 border border-white/30"
                                    >
                                        {{ category.name }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Floating decorative elements -->
                        <div class="floating-circle floating-circle-1"></div>
                        <div class="floating-circle floating-circle-2"></div>
                        <div class="floating-circle floating-circle-3"></div>
                    </div>

                    <!-- Search Results -->
                    <div v-if="searchQuery">
                        <div class="mb-6">
                            <h2
                                class="text-2xl font-bold"
                                :class="
                                    store.theme === 'bold'
                                        ? 'text-white'
                                        : 'text-gray-900'
                                "
                            >
                                Search Results for "{{ searchQuery }}"
                            </h2>
                            <p
                                class="text-sm mt-1"
                                :class="
                                    store.theme === 'bold'
                                        ? 'text-gray-300'
                                        : 'text-gray-600'
                                "
                            >
                                {{ searchResults.length }}
                                {{
                                    searchResults.length === 1
                                        ? "product"
                                        : "products"
                                }}
                                found
                            </p>
                        </div>

                        <div
                            v-if="searchResults.length > 0"
                            class="grid grid-cols-1 md:grid-cols-2 gap-4"
                        >
                            <ProductCardGrid
                                v-for="product in searchResults"
                                :key="'search-' + product.id"
                                :product="product"
                                :store="store"
                                @productClick="openProductModal"
                            />
                        </div>

                        <!-- No Results -->
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
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                />
                            </svg>
                            <h3
                                class="mt-4 text-xl font-medium"
                                :class="
                                    store.theme === 'bold'
                                        ? 'text-white'
                                        : 'text-gray-900'
                                "
                            >
                                No products found
                            </h3>
                            <p
                                class="mt-2"
                                :class="
                                    store.theme === 'bold'
                                        ? 'text-gray-400'
                                        : 'text-gray-500'
                                "
                            >
                                Try searching with different keywords
                            </p>
                            <button
                                @click="searchQuery = ''"
                                class="mt-4 px-4 py-2 text-white rounded-lg transition-colors"
                                :class="[
                                    store.theme === 'bold'
                                        ? 'bg-gradient-to-r from-orange-500 to-yellow-500 hover:from-orange-600 hover:to-yellow-600'
                                        : store.theme === 'modern'
                                        ? 'bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700'
                                        : 'bg-blue-600 hover:bg-blue-700',
                                ]"
                            >
                                Clear Search
                            </button>
                        </div>
                    </div>

                    <!-- Normal Content (when not searching) -->
                    <div v-else>
                        <!-- Picked for You Section (Horizontal Scrollable) -->
                        <section
                            v-if="
                                frequent_products &&
                                frequent_products.length > 0
                            "
                            id="picked"
                            class="mb-12"
                        >
                            <div class="flex items-center justify-between mb-6">
                                <h2
                                    class="text-3xl font-bold"
                                    :class="
                                        store.theme === 'bold'
                                            ? 'text-white'
                                            : 'text-gray-900'
                                    "
                                >
                                    Picked for you
                                </h2>

                                <!-- Scroll Navigation Buttons -->
                                <div class="hidden md:flex items-center gap-2">
                                    <button
                                        @click="scrollPickedProducts('left')"
                                        class="p-2 rounded-full border-2 transition-colors"
                                        :class="[
                                            store.theme === 'bold'
                                                ? 'border-gray-700 text-white hover:bg-gray-800'
                                                : 'border-gray-300 text-gray-700 hover:bg-gray-100',
                                        ]"
                                        aria-label="Scroll left"
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
                                                d="M15 19l-7-7 7-7"
                                            />
                                        </svg>
                                    </button>
                                    <button
                                        @click="scrollPickedProducts('right')"
                                        class="p-2 rounded-full border-2 transition-colors"
                                        :class="[
                                            store.theme === 'bold'
                                                ? 'border-gray-700 text-white hover:bg-gray-800'
                                                : 'border-gray-300 text-gray-700 hover:bg-gray-100',
                                        ]"
                                        aria-label="Scroll right"
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
                                                d="M9 5l7 7-7 7"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Horizontal Scrollable Container -->
                            <div
                                ref="pickedScrollContainer"
                                @mousedown="startDrag"
                                @mousemove="onDrag"
                                @mouseup="stopDrag"
                                @mouseleave="stopDrag"
                                @touchstart="startDrag"
                                @touchmove="onDrag"
                                @touchend="stopDrag"
                                class="flex gap-4 overflow-x-auto scrollbar-hide scroll-smooth select-none"
                                :style="{
                                    cursor: isDragging ? 'grabbing' : 'grab',
                                }"
                            >
                                <div
                                    v-for="product in frequent_products"
                                    :key="'picked-' + product.id"
                                    class="flex-shrink-0 w-[150px]"
                                >
                                    <button
                                        @click="openProductModal(product.id)"
                                        :class="[
                                            'block w-full text-left rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-300 border cursor-pointer',
                                            store.theme === 'bold'
                                                ? 'bg-gray-900 border-gray-900'
                                                : store.theme === 'monochrome'
                                                ? 'bg-white border-gray-100'
                                                : 'bg-white border-gray-200',
                                        ]"
                                    >
                                        <div class="flex flex-col">
                                            <!-- Product Image -->
                                            <div
                                                class="relative w-full h-[120px]"
                                            >
                                                <img
                                                    v-if="product.image"
                                                    :src="product.image"
                                                    :alt="product.name"
                                                    class="w-full h-full object-cover"
                                                    loading="lazy"
                                                />
                                                <div
                                                    v-else
                                                    class="w-full h-full flex items-center justify-center"
                                                    :class="
                                                        store.theme === 'bold'
                                                            ? 'bg-gray-700'
                                                            : 'bg-gray-200'
                                                    "
                                                >
                                                    <svg
                                                        class="w-12 h-12"
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

                                            <!-- Product Info -->
                                            <div class="p-2">
                                                <h3
                                                    class="text-xs font-bold mb-1"
                                                    :class="
                                                        store.theme === 'bold'
                                                            ? 'text-white'
                                                            : 'text-gray-900'
                                                    "
                                                >
                                                    {{ product.name }}
                                                </h3>
                                                <span
                                                    class="text-sm font-semibold"
                                                    :class="
                                                        store.theme === 'bold'
                                                            ? 'text-white'
                                                            : 'text-gray-900'
                                                    "
                                                    >${{
                                                        (
                                                            product.price_cents /
                                                            100
                                                        ).toFixed(2)
                                                    }}</span
                                                >
                                            </div>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </section>

                        <!-- Featured Section (Larger) -->
                        <section
                            v-if="
                                featured_products &&
                                featured_products.length > 0
                            "
                            id="featured"
                            class="mb-12"
                        >
                            <div class="flex items-center justify-between mb-6">
                                <h2
                                    class="text-3xl font-bold"
                                    :class="
                                        store.theme === 'bold'
                                            ? 'text-white'
                                            : 'text-gray-900'
                                    "
                                >
                                    Featured
                                </h2>

                                <!-- Scroll Navigation Buttons -->
                                <div class="hidden md:flex items-center gap-2">
                                    <button
                                        @click="scrollFeaturedProducts('left')"
                                        class="p-2 rounded-full border-2 transition-colors"
                                        :class="[
                                            store.theme === 'bold'
                                                ? 'border-gray-700 text-white hover:bg-gray-800'
                                                : 'border-gray-300 text-gray-700 hover:bg-gray-100',
                                        ]"
                                        aria-label="Scroll left"
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
                                                d="M15 19l-7-7 7-7"
                                            />
                                        </svg>
                                    </button>
                                    <button
                                        @click="scrollFeaturedProducts('right')"
                                        class="p-2 rounded-full border-2 transition-colors"
                                        :class="[
                                            store.theme === 'bold'
                                                ? 'border-gray-700 text-white hover:bg-gray-800'
                                                : 'border-gray-300 text-gray-700 hover:bg-gray-100',
                                        ]"
                                        aria-label="Scroll right"
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
                                                d="M9 5l7 7-7 7"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Horizontal Scrollable Container -->
                            <div
                                ref="featuredScrollContainer"
                                @mousedown="startDrag"
                                @mousemove="onDrag"
                                @mouseup="stopDrag"
                                @mouseleave="stopDrag"
                                @touchstart="startDrag"
                                @touchmove="onDrag"
                                @touchend="stopDrag"
                                class="flex gap-4 overflow-x-auto scrollbar-hide scroll-smooth pb-2 select-none"
                                :style="{
                                    cursor: isDragging ? 'grabbing' : 'grab',
                                }"
                            >
                                <div
                                    v-for="product in featured_products"
                                    :key="'featured-' + product.id"
                                    class="flex-shrink-0 w-[180px]"
                                >
                                    <button
                                        @click="openProductModal(product.id)"
                                        :class="[
                                            'block w-full text-left rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-300 border cursor-pointer',
                                            store.theme === 'bold'
                                                ? 'bg-gray-900 border-gray-900'
                                                : store.theme === 'monochrome'
                                                ? 'bg-white border-gray-100'
                                                : 'bg-white border-gray-200',
                                        ]"
                                    >
                                        <div class="flex flex-col">
                                            <!-- Product Image -->
                                            <div
                                                class="relative w-full h-[150px]"
                                            >
                                                <img
                                                    v-if="product.image"
                                                    :src="product.image"
                                                    :alt="product.name"
                                                    class="w-full h-full object-cover"
                                                    loading="lazy"
                                                />
                                                <div
                                                    v-else
                                                    class="w-full h-full flex items-center justify-center"
                                                    :class="
                                                        store.theme === 'bold'
                                                            ? 'bg-gray-700'
                                                            : 'bg-gray-200'
                                                    "
                                                >
                                                    <svg
                                                        class="w-10 h-10"
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

                                            <!-- Product Info -->
                                            <div
                                                class="p-2 flex flex-col justify-between flex-1"
                                            >
                                                <h3
                                                    class="text-xs font-bold mb-1"
                                                    :class="
                                                        store.theme === 'bold'
                                                            ? 'text-white'
                                                            : 'text-gray-900'
                                                    "
                                                >
                                                    {{ product.name }}
                                                </h3>
                                                <span
                                                    class="text-sm font-semibold"
                                                    :class="
                                                        store.theme === 'bold'
                                                            ? 'text-white'
                                                            : 'text-gray-900'
                                                    "
                                                    >${{
                                                        (
                                                            product.price_cents /
                                                            100
                                                        ).toFixed(2)
                                                    }}</span
                                                >
                                            </div>
                                        </div>
                                    </button>
                                </div>
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
                                <h2
                                    class="text-3xl font-bold mb-6"
                                    :class="
                                        store.theme === 'bold'
                                            ? 'text-white'
                                            : 'text-gray-900'
                                    "
                                >
                                    {{ category.name }}
                                </h2>
                                <div
                                    class="grid grid-cols-1 md:grid-cols-2 gap-4"
                                >
                                    <ProductCardGrid
                                        v-for="product in category.products"
                                        :key="
                                            'cat-' +
                                            category.id +
                                            '-prod-' +
                                            product.id
                                        "
                                        :product="product"
                                        :store="store"
                                        @productClick="openProductModal"
                                    />
                                </div>
                            </section>
                        </div>

                        <!-- Empty State -->
                        <div
                            v-if="
                                (!frequent_products ||
                                    frequent_products.length === 0) &&
                                (!categories || categories.length === 0)
                            "
                            class="text-center py-12"
                        >
                            <svg
                                class="mx-auto h-24 w-24"
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
                                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
                                />
                            </svg>
                            <h3
                                class="mt-4 text-xl font-medium"
                                :class="
                                    store.theme === 'bold'
                                        ? 'text-white'
                                        : 'text-gray-900'
                                "
                            >
                                No products available
                            </h3>
                            <p
                                class="mt-2"
                                :class="
                                    store.theme === 'bold'
                                        ? 'text-gray-400'
                                        : 'text-gray-500'
                                "
                            >
                                Check back soon for new products!
                            </p>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <!-- Product Modal -->
    <ProductModal
        :isOpen="isModalOpen"
        :productId="selectedProductId"
        :store="store"
        @close="closeProductModal"
    />
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { Link } from "@inertiajs/vue3";
import CartButton from "@/Components/Storefront/CartButton.vue";
import CartDrawer from "@/Components/Storefront/CartDrawer.vue";
import ProductCardGrid from "@/Components/Storefront/ProductCardGrid.vue";
import ProductModal from "@/Components/Storefront/ProductModal.vue";
import { useTheme } from "@/Composables/useTheme";

const props = defineProps({
    store: {
        type: Object,
        required: true,
    },
    featured_products: {
        type: Array,
        default: () => [],
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

// Initialize theme
const { config: themeConfig } = useTheme(props.store.theme);

const activeCategory = ref("");
const isMobileMenuOpen = ref(false);
const isSearchOpen = ref(false);
const searchQuery = ref("");

// Product modal state
const isModalOpen = ref(false);
const selectedProductId = ref(null);

const openProductModal = (productId) => {
    selectedProductId.value = productId;
    isModalOpen.value = true;
};

const closeProductModal = () => {
    isModalOpen.value = false;
    selectedProductId.value = null;
};

// Random greeting lists
const morningGreetings = [
    "Rise and shine! ☕",
    "Good morning, sunshine! 🌅",
    "Wakey wakey, eggs and bakey! 🍳",
    "Something fresh would be awesome! 🐦",
    "Morning vibes incoming! ✨",
];

const morningTaglines = [""];

const afternoonGreetings = [
    "Good afternoon! ☀️",
    "Midday break? Perfect timing! 🎯",
    "Afternoon pick-me-up time! 🌟",
    "Ready for an afternoon adventure? 🚀",
    "Lunch break browsing approved! 😎",
];

const afternoonTaglines = [""];

const eveningGreetings = [
    "Good evening! 🌆",
    "Evening plans? We got you! 🎉",
    "Time to unwind and browse! 🍷",
    "Your evening entertainment is here! 🌃",
    "Sunset shopping spree! 🌇",
];

const eveningTaglines = [""];

const nightGreetings = [
    "Good night! 🌙",
    "Burning the midnight oil? 🕯️",
    "Night owl? Me too 🦉",
    "Something sweet sounds good 🍦",
    "You won't regret it 🌌",
];

const nightTaglines = [""];

// Time-based greeting with random selection
const getRandomItem = (array) =>
    array[Math.floor(Math.random() * array.length)];

const timeBasedGreeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return getRandomItem(morningGreetings);
    if (hour < 17) return getRandomItem(afternoonGreetings);
    if (hour < 21) return getRandomItem(eveningGreetings);
    return getRandomItem(nightGreetings);
});

const timeBasedTagline = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return getRandomItem(morningTaglines);
    if (hour < 17) return getRandomItem(afternoonTaglines);
    if (hour < 21) return getRandomItem(eveningTaglines);
    return getRandomItem(nightTaglines);
});

const greetingEmoji = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return "☀️";
    if (hour < 17) return "🌤️";
    if (hour < 21) return "🌆";
    return "🌙";
});

// Horizontal scroll for "Picked for You" and "Featured"
const pickedScrollContainer = ref(null);
const featuredScrollContainer = ref(null);
const isDragging = ref(false);
const startX = ref(0);
const scrollLeft = ref(0);
const draggedElement = ref(null);

const scrollPickedProducts = (direction) => {
    if (!pickedScrollContainer.value) return;

    // Scroll 3 items at a time (180px per item + gap)
    const scrollAmount = 560; // 3 items * 180px + gaps
    const container = pickedScrollContainer.value;

    if (direction === "left") {
        container.scrollBy({ left: -scrollAmount, behavior: "smooth" });
    } else {
        container.scrollBy({ left: scrollAmount, behavior: "smooth" });
    }
};

const scrollFeaturedProducts = (direction) => {
    if (!featuredScrollContainer.value) return;

    // Scroll 3 items at a time (180px per item + gap)
    const scrollAmount = 560; // 3 items * 180px + gaps
    const container = featuredScrollContainer.value;

    if (direction === "left") {
        container.scrollBy({ left: -scrollAmount, behavior: "smooth" });
    } else {
        container.scrollBy({ left: scrollAmount, behavior: "smooth" });
    }
};

const startDrag = (e) => {
    const container = e.currentTarget;
    if (!container) return;
    isDragging.value = true;
    draggedElement.value = container;
    startX.value = e.pageX || e.touches[0].pageX;
    scrollLeft.value = container.scrollLeft;
    container.style.cursor = "grabbing";
};

const stopDrag = () => {
    isDragging.value = false;
    if (draggedElement.value) {
        draggedElement.value.style.cursor = "grab";
        draggedElement.value = null;
    }
};

const onDrag = (e) => {
    if (!isDragging.value || !draggedElement.value) return;
    e.preventDefault();
    const x = e.pageX || e.touches[0].pageX;
    const walk = (x - startX.value) * 2; // Multiply for faster scroll
    draggedElement.value.scrollLeft = scrollLeft.value - walk;
};

// Computed property to filter products based on search query
const searchResults = computed(() => {
    if (!searchQuery.value) return [];

    const query = searchQuery.value.toLowerCase();
    const results = [];

    // Search through all products in all categories
    if (props.categories && props.categories.length > 0) {
        props.categories.forEach((category) => {
            if (category.products && category.products.length > 0) {
                category.products.forEach((product) => {
                    const matchesName = product.name
                        ?.toLowerCase()
                        .includes(query);
                    const matchesDescription = product.description
                        ?.toLowerCase()
                        .includes(query);

                    if (matchesName || matchesDescription) {
                        results.push(product);
                    }
                });
            }
        });
    }

    // Also search through frequent products if they exist
    if (props.frequent_products && props.frequent_products.length > 0) {
        props.frequent_products.forEach((product) => {
            // Check if product is already in results (avoid duplicates)
            const alreadyAdded = results.some((p) => p.id === product.id);
            if (!alreadyAdded) {
                const matchesName = product.name?.toLowerCase().includes(query);
                const matchesDescription = product.description
                    ?.toLowerCase()
                    .includes(query);

                if (matchesName || matchesDescription) {
                    results.push(product);
                }
            }
        });
    }

    return results;
});

const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
    // Prevent body scroll when menu is open
    if (isMobileMenuOpen.value) {
        document.body.style.overflow = "hidden";
    } else {
        document.body.style.overflow = "";
    }
};

const toggleSearch = () => {
    isSearchOpen.value = !isSearchOpen.value;
    // Clear search when closing
    if (!isSearchOpen.value) {
        searchQuery.value = "";
    }
};

const scrollToCategory = (slug) => {
    const element = document.getElementById(slug);
    if (element) {
        const offset = 100; // Account for sticky header
        const elementPosition = element.getBoundingClientRect().top;
        const offsetPosition = elementPosition + window.pageYOffset - offset;

        window.scrollTo({
            top: offsetPosition,
            behavior: "smooth",
        });
        activeCategory.value = slug;
    }
};

const scrollToCategoryAndClose = (slug) => {
    scrollToCategory(slug);
    toggleMobileMenu();
};

const getGoogleMapsUrl = () => {
    const parts = [
        props.store.address_primary,
        props.store.address_city,
        props.store.address_state,
        props.store.address_postcode,
    ].filter(Boolean);
    const fullAddress = parts.join(", ");
    return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(
        fullAddress
    )}`;
};

const formatTime = (time) => {
    if (!time) return "";
    const [hours, minutes] = time.split(":");
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? "PM" : "AM";
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
    window.addEventListener("scroll", handleScroll);
    handleScroll(); // Set initial active category
});

onUnmounted(() => {
    window.removeEventListener("scroll", handleScroll);
    // Clean up body overflow on unmount
    document.body.style.overflow = "";
});
</script>

<style scoped>
/* Hide scrollbar for horizontal category chips */
.scrollbar-hide {
    -ms-overflow-style: none; /* IE and Edge */
    scrollbar-width: none; /* Firefox */
}

.scrollbar-hide::-webkit-scrollbar {
    display: none; /* Chrome, Safari, Opera */
}

/* Smooth scroll behavior */
html {
    scroll-behavior: smooth;
}

/* Hero Animated Gradient */
.hero-gradient {
    background: linear-gradient(
        135deg,
        #667eea 0%,
        #764ba2 25%,
        #f093fb 50%,
        #4facfe 75%,
        #00f2fe 100%
    );
    background-size: 400% 400%;
    animation: gradient-flow 15s ease infinite;
}

@keyframes gradient-flow {
    0% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0% 50%;
    }
}

/* Floating Decorative Circles */
.floating-circle {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    animation: float-random 20s ease-in-out infinite;
}

.floating-circle-1 {
    width: 300px;
    height: 300px;
    top: -100px;
    right: -50px;
    animation-duration: 25s;
}

.floating-circle-2 {
    width: 200px;
    height: 200px;
    bottom: -50px;
    left: -30px;
    animation-duration: 20s;
    animation-delay: -5s;
}

.floating-circle-3 {
    width: 150px;
    height: 150px;
    top: 50%;
    right: 10%;
    animation-duration: 30s;
    animation-delay: -10s;
}

@keyframes float-random {
    0%,
    100% {
        transform: translate(0, 0) rotate(0deg);
    }
    25% {
        transform: translate(30px, -30px) rotate(90deg);
    }
    50% {
        transform: translate(-20px, 20px) rotate(180deg);
    }
    75% {
        transform: translate(40px, 10px) rotate(270deg);
    }
}

/* Hero Badge Hover Effect */
.hero-badge:hover {
    transform: translateY(-2px) scale(1.05);
    box-shadow: 0 10px 25px rgba(255, 255, 255, 0.3);
}
</style>
