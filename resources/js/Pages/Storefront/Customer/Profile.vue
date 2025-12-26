<template>
    <div class="min-h-screen" :class="themeConfig.background">
        <!-- Toast Notifications -->
        <ToastContainer />

        <!-- Header -->
        <header :class="store.theme === 'bold' ? 'bg-gray-900 border-b border-orange-500/20' : 'bg-white shadow-sm'">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex justify-between items-center">
                    <a :href="`/store/${store.id}`" class="flex items-center">
                        <h1 class="text-2xl font-bold" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                            {{ store.name }}
                        </h1>
                    </a>
                    <div class="flex items-center gap-4">
                        <a :href="`/store/${store.id}`" :class="themeConfig.link">Store</a>
                        <a :href="`/store/${store.id}/orders`" :class="themeConfig.link">Orders</a>
                        <button
                            @click="handleLogout"
                            type="button"
                            :class="themeConfig.link"
                        >
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Welcome Section -->
            <div class="mb-8">
                <h2 class="text-3xl font-bold" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                    Welcome back, {{ customer.first_name }}!
                </h2>
                <p :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">
                    Member since {{ stats.member_since }}
                </p>
            </div>

            <!-- Tabs -->
            <div class="mb-6">
                <div class="border-b" :class="store.theme === 'bold' ? 'border-gray-700' : 'border-gray-200'">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <button
                            @click="activeTab = 'dashboard'"
                            :class="[
                                activeTab === 'dashboard'
                                    ? (store.theme === 'bold'
                                        ? 'border-orange-500 text-orange-500'
                                        : store.theme === 'modern'
                                        ? 'border-purple-600 text-purple-600'
                                        : 'border-blue-600 text-blue-600')
                                    : (store.theme === 'bold'
                                        ? 'border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'),
                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors'
                            ]"
                        >
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                Dashboard
                            </div>
                        </button>
                        <button
                            @click="activeTab = 'profile'"
                            :class="[
                                activeTab === 'profile'
                                    ? (store.theme === 'bold'
                                        ? 'border-orange-500 text-orange-500'
                                        : store.theme === 'modern'
                                        ? 'border-purple-600 text-purple-600'
                                        : 'border-blue-600 text-blue-600')
                                    : (store.theme === 'bold'
                                        ? 'border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'),
                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors'
                            ]"
                        >
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Profile Settings
                            </div>
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Dashboard Tab Content -->
            <div v-show="activeTab === 'dashboard'">
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Total Orders -->
                    <div class="rounded-lg shadow-md p-6" :class="themeConfig.cardBackground">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium" :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">
                                    Total Orders
                                </p>
                                <p class="text-3xl font-bold mt-2" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                                    {{ stats.total_orders }}
                                </p>
                            </div>
                            <div class="p-3 rounded-full" :class="store.theme === 'bold' ? 'bg-orange-500/10' : 'bg-blue-100'">
                                <svg class="w-8 h-8" :class="store.theme === 'bold' ? 'text-orange-500' : 'text-blue-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total Spent -->
                    <div class="rounded-lg shadow-md p-6" :class="themeConfig.cardBackground">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium" :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">
                                    Total Spent
                                </p>
                                <p class="text-3xl font-bold mt-2" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                                    ${{ stats.total_spent.toFixed(2) }}
                                </p>
                            </div>
                            <div class="p-3 rounded-full" :class="store.theme === 'bold' ? 'bg-orange-500/10' : 'bg-green-100'">
                                <svg class="w-8 h-8" :class="store.theme === 'bold' ? 'text-orange-500' : 'text-green-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Loyalty Points -->
                    <div v-if="loyalty" class="rounded-lg shadow-md p-6" :class="themeConfig.cardBackground">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex-1">
                                <p class="text-sm font-medium" :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">
                                    Loyalty Points
                                </p>
                                <p class="text-3xl font-bold mt-2" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                                    {{ stats.loyalty_points }}
                                </p>
                            </div>
                            <div class="p-3 rounded-full" :class="store.theme === 'bold' ? 'bg-orange-500/10' : 'bg-purple-100'">
                                <svg class="w-8 h-8" :class="store.theme === 'bold' ? 'text-orange-500' : 'text-purple-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                </svg>
                            </div>
                        </div>
                        <div class="text-xs space-y-1 pt-3 border-t" :class="store.theme === 'bold' ? 'border-gray-700' : 'border-gray-200'">
                            <div class="flex justify-between" :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">
                                <span>Lifetime Earned:</span>
                                <span class="font-medium">{{ loyalty.lifetime_points }}</span>
                            </div>
                            <div class="flex justify-between" :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">
                                <span>Total Redeemed:</span>
                                <span class="font-medium">{{ loyalty.points_redeemed }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="rounded-lg shadow-md p-6" :class="themeConfig.cardBackground">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                            Recent Orders
                        </h3>
                        <a :href="`/store/${store.id}/orders`" :class="themeConfig.link">
                            View All →
                        </a>
                    </div>

                    <!-- Orders List -->
                    <div v-if="recent_orders.length > 0" class="space-y-4">
                        <div
                            v-for="order in recent_orders"
                            :key="order.id"
                            @click="openOrderModal(order.id)"
                            class="border rounded-lg p-4 cursor-pointer transition-all hover:shadow-md"
                            :class="[
                                store.theme === 'bold' ? 'border-gray-700 hover:border-orange-500' : 'border-gray-200 hover:border-blue-400',
                            ]"
                        >
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <p class="font-semibold" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                                        Order #{{ order.public_id }}
                                    </p>
                                    <p class="text-sm" :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">
                                        {{ formatDate(order.created_at) }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="getStatusClass(order.status)"
                                    >
                                        {{ formatStatus(order.status) }}
                                    </span>
                                    <p class="font-semibold mt-1" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                                        ${{ order.total.toFixed(2) }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex justify-between items-center">
                                <div class="text-sm" :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">
                                    {{ order.items_count }} item{{ order.items_count !== 1 ? 's' : '' }}
                                </div>
                                <div class="text-sm font-medium" :class="store.theme === 'bold' ? 'text-orange-500' : 'text-blue-600'">
                                    View Details →
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="text-center py-12">
                        <svg class="mx-auto h-12 w-12" :class="store.theme === 'bold' ? 'text-gray-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <p class="mt-2" :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">
                            No orders yet. Start shopping!
                        </p>
                        <a
                            :href="`/store/${store.id}/products`"
                            class="inline-block mt-4 px-6 py-2 rounded-lg font-semibold transition-all"
                            :class="themeConfig.buttonPrimary"
                        >
                            Browse Products
                        </a>
                    </div>
                </div>
            </div>

            <!-- Profile Tab Content -->
            <div v-show="activeTab === 'profile'" class="max-w-7xl">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <!-- Profile Information -->
                    <div class="lg:col-span-2 rounded-lg shadow-md p-6" :class="themeConfig.cardBackground">
                    <h3 class="text-xl font-semibold mb-4" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                        Account Information
                    </h3>
                    <form @submit.prevent="updateProfile">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">
                                    First Name *
                                </label>
                                <input
                                    v-model="profileForm.first_name"
                                    type="text"
                                    required
                                    class="w-full px-3 py-2 border rounded-md"
                                    :class="[
                                        store.theme === 'bold'
                                            ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-400 focus:ring-orange-500 focus:border-orange-500'
                                            : 'bg-white border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500'
                                    ]"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">
                                    Last Name *
                                </label>
                                <input
                                    v-model="profileForm.last_name"
                                    type="text"
                                    required
                                    class="w-full px-3 py-2 border rounded-md"
                                    :class="[
                                        store.theme === 'bold'
                                            ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-400 focus:ring-orange-500 focus:border-orange-500'
                                            : 'bg-white border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500'
                                    ]"
                                />
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium mb-1" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">
                                Email (Cannot be changed)
                            </label>
                            <input
                                :value="customer.email"
                                type="email"
                                disabled
                                class="w-full px-3 py-2 border rounded-md opacity-60 cursor-not-allowed"
                                :class="[
                                    store.theme === 'bold'
                                        ? 'bg-gray-800 border-gray-700 text-white'
                                        : 'bg-gray-100 border-gray-300 text-gray-900'
                                ]"
                            />
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium mb-1" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">
                                Mobile (Optional)
                            </label>
                            <input
                                v-model="profileForm.mobile"
                                type="tel"
                                class="w-full px-3 py-2 border rounded-md"
                                :class="[
                                    store.theme === 'bold'
                                        ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-400 focus:ring-orange-500 focus:border-orange-500'
                                        : 'bg-white border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500'
                                ]"
                            />
                        </div>

                        <!-- Address Section -->
                        <div class="mt-6 pt-6 border-t" :class="store.theme === 'bold' ? 'border-gray-700' : 'border-gray-200'">
                            <h4 class="text-lg font-semibold mb-4" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                                Address (Optional)
                            </h4>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">
                                        Address Line 1
                                    </label>
                                    <input
                                        v-model="profileForm.address_line1"
                                        type="text"
                                        placeholder="Street address, P.O. box, company name"
                                        class="w-full px-3 py-2 border rounded-md"
                                        :class="[
                                            store.theme === 'bold'
                                                ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-400 focus:ring-orange-500 focus:border-orange-500'
                                                : 'bg-white border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500'
                                        ]"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">
                                        Address Line 2
                                    </label>
                                    <input
                                        v-model="profileForm.address_line2"
                                        type="text"
                                        placeholder="Apartment, suite, unit, building, floor, etc."
                                        class="w-full px-3 py-2 border rounded-md"
                                        :class="[
                                            store.theme === 'bold'
                                                ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-400 focus:ring-orange-500 focus:border-orange-500'
                                                : 'bg-white border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500'
                                        ]"
                                    />
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium mb-1" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">
                                            City
                                        </label>
                                        <input
                                            v-model="profileForm.address_city"
                                            type="text"
                                            class="w-full px-3 py-2 border rounded-md"
                                            :class="[
                                                store.theme === 'bold'
                                                    ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-400 focus:ring-orange-500 focus:border-orange-500'
                                                    : 'bg-white border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500'
                                            ]"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">
                                            State/Province
                                        </label>
                                        <input
                                            v-model="profileForm.address_state"
                                            type="text"
                                            class="w-full px-3 py-2 border rounded-md"
                                            :class="[
                                                store.theme === 'bold'
                                                    ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-400 focus:ring-orange-500 focus:border-orange-500'
                                                    : 'bg-white border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500'
                                            ]"
                                        />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium mb-1" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">
                                            Postcode/ZIP
                                        </label>
                                        <input
                                            v-model="profileForm.address_postcode"
                                            type="text"
                                            class="w-full px-3 py-2 border rounded-md"
                                            :class="[
                                                store.theme === 'bold'
                                                    ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-400 focus:ring-orange-500 focus:border-orange-500'
                                                    : 'bg-white border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500'
                                            ]"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">
                                            Country
                                        </label>
                                        <input
                                            v-model="profileForm.address_country"
                                            type="text"
                                            class="w-full px-3 py-2 border rounded-md"
                                            :class="[
                                                store.theme === 'bold'
                                                    ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-400 focus:ring-orange-500 focus:border-orange-500'
                                                    : 'bg-white border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500'
                                            ]"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button
                            type="submit"
                            :disabled="processing"
                            class="mt-6 px-6 py-2 rounded-lg font-semibold transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                            :class="themeConfig.buttonPrimary"
                        >
                            {{ processing ? 'Saving...' : 'Save Changes' }}
                        </button>
                    </form>
                    </div>

                    <!-- Change Password -->
                    <div class="lg:col-span-1 rounded-lg shadow-md p-6" :class="themeConfig.cardBackground">
                    <h3 class="text-xl font-semibold mb-4" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                        Change Password
                    </h3>
                    <form @submit.prevent="updatePassword">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium mb-1" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">
                                    Current Password *
                                </label>
                                <input
                                    v-model="passwordForm.current_password"
                                    type="password"
                                    required
                                    class="w-full px-3 py-2 border rounded-md"
                                    :class="[
                                        store.theme === 'bold'
                                            ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-400 focus:ring-orange-500 focus:border-orange-500'
                                            : 'bg-white border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500',
                                        errors.current_password ? 'border-red-500' : ''
                                    ]"
                                />
                                <p v-if="errors.current_password" class="mt-1 text-sm text-red-600">{{ errors.current_password }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">
                                    New Password * (minimum 8 characters)
                                </label>
                                <input
                                    v-model="passwordForm.password"
                                    type="password"
                                    required
                                    minlength="8"
                                    class="w-full px-3 py-2 border rounded-md"
                                    :class="[
                                        store.theme === 'bold'
                                            ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-400 focus:ring-orange-500 focus:border-orange-500'
                                            : 'bg-white border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500',
                                        errors.password ? 'border-red-500' : ''
                                    ]"
                                />
                                <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">
                                    Confirm New Password *
                                </label>
                                <input
                                    v-model="passwordForm.password_confirmation"
                                    type="password"
                                    required
                                    minlength="8"
                                    class="w-full px-3 py-2 border rounded-md"
                                    :class="[
                                        store.theme === 'bold'
                                            ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-400 focus:ring-orange-500 focus:border-orange-500'
                                            : 'bg-white border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500'
                                    ]"
                                />
                            </div>
                        </div>

                        <button
                            type="submit"
                            :disabled="processingPassword"
                            class="mt-6 px-6 py-2 rounded-lg font-semibold transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                            :class="themeConfig.buttonPrimary"
                        >
                            {{ processingPassword ? 'Updating...' : 'Update Password' }}
                        </button>
                    </form>

                    <!-- Privacy & Legal Links -->
                    <div class="mt-6 pt-6 border-t space-y-2" :class="store.theme === 'bold' ? 'border-gray-700' : 'border-gray-200'">
                        <h4 class="text-sm font-semibold mb-3" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                            Legal & Privacy
                        </h4>
                        <button
                            @click="showPrivacyModal = true"
                            class="flex items-center gap-2 text-sm transition-colors w-full"
                            :class="store.theme === 'bold' ? 'text-gray-400 hover:text-orange-500' : 'text-gray-600 hover:text-blue-600'"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            Privacy & Data Storage
                        </button>
                        <button
                            @click="showPrivacyPolicyModal = true"
                            class="flex items-center gap-2 text-sm transition-colors w-full"
                            :class="store.theme === 'bold' ? 'text-gray-400 hover:text-orange-500' : 'text-gray-600 hover:text-blue-600'"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            Privacy Policy
                        </button>
                        <button
                            @click="showTermsModal = true"
                            class="flex items-center gap-2 text-sm transition-colors w-full"
                            :class="store.theme === 'bold' ? 'text-gray-400 hover:text-orange-500' : 'text-gray-600 hover:text-blue-600'"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Terms of Service
                        </button>
                    </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Order Detail Modal -->
        <div v-if="showOrderModal" class="fixed inset-0 z-50 overflow-y-auto" @click="closeOrderModal">
            <div class="flex items-center justify-center min-h-screen px-4">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black opacity-50"></div>

                <!-- Modal -->
                <div
                    @click.stop
                    class="relative rounded-lg shadow-xl max-w-3xl w-full p-6"
                    :class="store.theme === 'bold' ? 'bg-gray-900' : 'bg-white'"
                >
                    <!-- Loading State -->
                    <div v-if="loadingOrder" class="text-center py-12">
                        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2"
                             :class="store.theme === 'bold' ? 'border-orange-500' : 'border-blue-600'"></div>
                        <p class="mt-4" :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">Loading order details...</p>
                    </div>

                    <!-- Order Details -->
                    <div v-else-if="selectedOrder">
                        <!-- Header -->
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h3 class="text-2xl font-bold" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                                    Order #{{ selectedOrder.public_id }}
                                </h3>
                                <p class="text-sm mt-1" :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">
                                    Placed on {{ formatDate(selectedOrder.created_at) }}
                                </p>
                            </div>
                            <button
                                @click="closeOrderModal"
                                class="p-2 rounded-lg transition-colors"
                                :class="store.theme === 'bold' ? 'hover:bg-gray-800 text-gray-400' : 'hover:bg-gray-100 text-gray-500'"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Status -->
                        <div class="mb-6">
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                :class="getStatusClass(selectedOrder.status)"
                            >
                                {{ formatStatus(selectedOrder.status) }}
                            </span>
                        </div>

                        <!-- Order Items -->
                        <div class="mb-6">
                            <h4 class="font-semibold mb-3" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                                Items Ordered
                            </h4>
                            <div class="border rounded-lg overflow-hidden" :class="store.theme === 'bold' ? 'border-gray-700' : 'border-gray-200'">
                                <table class="min-w-full divide-y" :class="store.theme === 'bold' ? 'divide-gray-700' : 'divide-gray-200'">
                                    <thead :class="store.theme === 'bold' ? 'bg-gray-800' : 'bg-gray-50'">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                                :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-500'">
                                                Product
                                            </th>
                                            <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider"
                                                :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-500'">
                                                Quantity
                                            </th>
                                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider"
                                                :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-500'">
                                                Price
                                            </th>
                                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider"
                                                :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-500'">
                                                Total
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y" :class="store.theme === 'bold' ? 'divide-gray-700' : 'divide-gray-200'">
                                        <tr v-for="item in selectedOrder.items" :key="item.id"
                                            :class="item.is_refunded ? (store.theme === 'bold' ? 'bg-red-900/20' : 'bg-red-50') : ''">
                                            <td class="px-4 py-3" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                                                <div class="flex items-center gap-2">
                                                    <span>{{ item.product_name }}</span>
                                                    <span v-if="item.is_refunded"
                                                        class="px-2 py-0.5 text-xs font-semibold rounded-full"
                                                        :class="store.theme === 'bold' ? 'bg-red-800 text-red-200 border border-red-600' : 'bg-red-100 text-red-800 border border-red-300'">
                                                        REFUNDED
                                                    </span>
                                                </div>
                                                <div v-if="item.is_refunded" class="mt-1 text-xs"
                                                    :class="store.theme === 'bold' ? 'text-red-300' : 'text-red-700'">
                                                    <p><strong>Refunded:</strong> {{ formatDate(item.refund_date) }}</p>
                                                    <p><strong>Reason:</strong> {{ item.refund_reason }}</p>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-center" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">
                                                {{ item.quantity }}
                                            </td>
                                            <td class="px-4 py-3 text-right"
                                                :class="[
                                                    store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700',
                                                    item.is_refunded ? 'line-through opacity-60' : ''
                                                ]">
                                                ${{ (item.price_cents / 100).toFixed(2) }}
                                            </td>
                                            <td class="px-4 py-3 text-right font-semibold"
                                                :class="[
                                                    store.theme === 'bold' ? 'text-white' : 'text-gray-900',
                                                    item.is_refunded ? 'line-through opacity-60' : ''
                                                ]">
                                                ${{ (item.total_cents / 100).toFixed(2) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Order Summary -->
                        <div class="rounded-lg p-4 mb-6" :class="store.theme === 'bold' ? 'bg-gray-800' : 'bg-gray-50'">
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">Subtotal:</span>
                                    <span :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                                        ${{ (selectedOrder.items_total_cents / 100).toFixed(2) }}
                                    </span>
                                </div>
                                <div v-if="selectedOrder.fulfilment_type === 'shipping'" class="flex justify-between text-sm">
                                    <span :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">Shipping:</span>
                                    <span :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                                        {{ selectedOrder.shipping_cost_cents === 0 ? 'FREE' : '$' + (selectedOrder.shipping_cost_cents / 100).toFixed(2) }}
                                    </span>
                                </div>
                                <div class="flex justify-between text-base font-bold pt-2 border-t" :class="store.theme === 'bold' ? 'border-gray-700' : 'border-gray-200'">
                                    <span :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">Total:</span>
                                    <span :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                                        ${{ (selectedOrder.total_cents / 100).toFixed(2) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Fulfilment Details -->
                        <div v-if="selectedOrder.fulfilment_type === 'shipping' && selectedOrder.tracking_code" class="mb-6">
                            <h4 class="font-semibold mb-3" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                                Tracking Information
                            </h4>
                            <div class="rounded-lg p-4" :class="store.theme === 'bold' ? 'bg-gray-800' : 'bg-blue-50'">
                                <p class="text-sm" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">
                                    <span class="font-medium">Tracking Code:</span> {{ selectedOrder.tracking_code }}
                                </p>
                                <a
                                    v-if="selectedOrder.tracking_url"
                                    :href="selectedOrder.tracking_url"
                                    target="_blank"
                                    class="text-sm font-medium mt-2 inline-block"
                                    :class="store.theme === 'bold' ? 'text-orange-500 hover:text-orange-400' : 'text-blue-600 hover:text-blue-800'"
                                >
                                    Track Shipment →
                                </a>
                            </div>
                        </div>

                        <!-- Close Button -->
                        <div class="flex justify-end">
                            <button
                                @click="closeOrderModal"
                                class="px-6 py-2 rounded-lg font-semibold transition-all"
                                :class="themeConfig.buttonSecondary"
                            >
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Privacy Disclosure Modal -->
        <div v-if="showPrivacyModal" class="fixed inset-0 z-50 overflow-y-auto" @click="showPrivacyModal = false">
            <div class="flex items-center justify-center min-h-screen px-4">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black opacity-50"></div>

                <!-- Modal -->
                <div
                    @click.stop
                    class="relative rounded-lg shadow-xl max-w-4xl w-full p-8 max-h-[90vh] overflow-y-auto"
                    :class="store.theme === 'bold' ? 'bg-gray-900' : 'bg-white'"
                >
                    <!-- Header -->
                    <div class="flex justify-between items-start mb-6">
                        <button
                            @click="showPrivacyModal = false"
                            class="p-2 rounded-lg transition-colors"
                            :class="store.theme === 'bold' ? 'hover:bg-gray-800 text-gray-400' : 'hover:bg-gray-100 text-gray-500'"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Content -->
                    <div class="space-y-6" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">
                        <!-- Introduction -->
                        <div class="p-4 rounded-lg" :class="store.theme === 'bold' ? 'bg-gray-800/50 border border-gray-700' : 'bg-blue-50 border border-blue-200'">
                            <p class="text-sm" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">
                                Your privacy is important to us. This disclosure explains what personal information we collect,
                                how we store it, and your rights regarding your data.
                            </p>
                        </div>

                        <!-- What Data We Collect -->
                        <div>
                            <h4 class="text-lg font-semibold mb-3 flex items-center gap-2" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                                <svg class="w-5 h-5" :class="store.theme === 'bold' ? 'text-orange-500' : 'text-blue-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                What Data We Collect
                            </h4>
                            <div class="ml-7 space-y-2 text-sm">
                                <p><strong class="font-medium">Personal Information:</strong> First name, last name, email address, mobile number</p>
                                <p><strong class="font-medium">Account Security:</strong> Encrypted password (stored using bcrypt hashing)</p>
                                <p><strong class="font-medium">Address Information:</strong> Street address, city, state/province, postal code, country</p>
                                <p><strong class="font-medium">Order History:</strong> Purchase records, order details, payment information</p>
                                <p><strong class="font-medium">Loyalty Program:</strong> Points balance, earned points, redemption history (if applicable)</p>
                            </div>
                        </div>

                        <!-- How We Secure Your Data -->
                        <div>
                            <h4 class="text-lg font-semibold mb-3 flex items-center gap-2" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                                <svg class="w-5 h-5" :class="store.theme === 'bold' ? 'text-orange-500' : 'text-blue-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                How We Secure Your Data
                            </h4>
                            <div class="ml-7 space-y-3 text-sm">
                                <div>
                                    <p class="font-medium mb-1"> Password Encryption</p>
                                    <p>All passwords are encrypted using bcrypt hashing with 12 rounds. Passwords are never stored in plain text and cannot be retrieved by anyone, including our staff.</p>
                                </div>
                                <div>
                                    <p class="font-medium mb-1"> Session Security</p>
                                    <p>Your login session uses secure, HttpOnly cookies with CSRF protection to prevent unauthorized access and cross-site attacks.</p>
                                </div>
                                <div>
                                    <p class="font-medium mb-1"> Input Sanitization</p>
                                    <p>All data you provide is sanitized and validated to protect against malicious input and ensure data integrity.</p>
                                </div>
                                <div>
                                    <p class="font-medium mb-1"> Secure Connections</p>
                                    <p>All data transmission is encrypted using industry-standard SSL/TLS protocols.</p>
                                </div>
                                <div>
                                    <p class="font-medium mb-1"> Rate Limiting</p>
                                    <p>Login attempts are limited to 5 per minute, and registrations to 3 per hour to prevent brute force attacks.</p>
                                </div>
                            </div>
                        </div>

                        <!-- How We Use Your Data -->
                        <div>
                            <h4 class="text-lg font-semibold mb-3 flex items-center gap-2" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                                <svg class="w-5 h-5" :class="store.theme === 'bold' ? 'text-orange-500' : 'text-blue-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                How We Use Your Data
                            </h4>
                            <div class="ml-7 space-y-2 text-sm">
                                <p>• <strong>Order Processing:</strong> To fulfill your orders and communicate order status</p>
                                <p>• <strong>Account Management:</strong> To provide access to your order history and profile</p>
                                <p>• <strong>Customer Support:</strong> To respond to your inquiries and resolve issues</p>
                                <p>• <strong>Loyalty Program:</strong> To track and award loyalty points (if enrolled)</p>
                                <p>• <strong>Security:</strong> To protect your account from unauthorized access</p>
                            </div>
                        </div>

                        <!-- Data Retention -->
                        <div>
                            <h4 class="text-lg font-semibold mb-3 flex items-center gap-2" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                                <svg class="w-5 h-5" :class="store.theme === 'bold' ? 'text-orange-500' : 'text-blue-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Data Retention
                            </h4>
                            <div class="ml-7 space-y-2 text-sm">
                                <p>We retain your personal information for as long as your account is active or as needed to provide you services. Order history is retained for legal and accounting purposes as required by law.</p>
                                <p class="mt-2">You may request deletion of your account at any time by contacting customer support, subject to legal retention requirements.</p>
                            </div>
                        </div>

                        <!-- Your Rights -->
                        <div>
                            <h4 class="text-lg font-semibold mb-3 flex items-center gap-2" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                                <svg class="w-5 h-5" :class="store.theme === 'bold' ? 'text-orange-500' : 'text-blue-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Your Rights
                            </h4>
                            <div class="ml-7 space-y-2 text-sm">
                                <p>• <strong>Access:</strong> You can view and update your personal information in your profile</p>
                                <p>• <strong>Correction:</strong> You can update incorrect information at any time</p>
                                <p>• <strong>Deletion:</strong> You can request account deletion through customer support</p>
                                <p>• <strong>Portability:</strong> You can request a copy of your data in a portable format</p>
                                <p>• <strong>Objection:</strong> You can object to certain data processing activities</p>
                            </div>
                        </div>

                        <!-- Multi-Tenant Isolation -->
                        <div>
                            <h4 class="text-lg font-semibold mb-3 flex items-center gap-2" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                                <svg class="w-5 h-5" :class="store.theme === 'bold' ? 'text-orange-500' : 'text-blue-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                Data Isolation
                            </h4>
                            <div class="ml-7 text-sm">
                                <p>Your data is isolated per merchant. This means your information is only accessible within {{ store.name }} and cannot be accessed by other merchants on the platform.</p>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="p-4 rounded-lg" :class="store.theme === 'bold' ? 'bg-gray-800/50 border border-gray-700' : 'bg-gray-50 border border-gray-200'">
                            <h5 class="font-semibold mb-2" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                                Questions or Concerns?
                            </h5>
                            <p class="text-sm">
                                If you have any questions about how we handle your data or want to exercise your rights,
                                please contact customer support. We're committed to protecting your privacy and ensuring
                                the security of your personal information.
                            </p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex justify-end mt-6 pt-6 border-t" :class="store.theme === 'bold' ? 'border-gray-700' : 'border-gray-200'">
                        <button
                            @click="showPrivacyModal = false"
                            class="px-6 py-2 rounded-lg font-semibold transition-all"
                            :class="themeConfig.buttonPrimary"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Privacy Policy Modal -->
        <PrivacyPolicyModal
            :isOpen="showPrivacyPolicyModal"
            @close="showPrivacyPolicyModal = false"
            :contactEmail="store.contact_email || 'privacy@storeflow.com'"
        />

        <!-- Terms of Service Modal -->
        <TermsOfServiceModal
            :isOpen="showTermsModal"
            @close="showTermsModal = false"
            :contactEmail="store.contact_email || 'legal@storeflow.com'"
        />
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useTheme } from '@/Composables/useTheme';
import { useNotifications } from '@/Composables/useNotifications';
import ToastContainer from '@/Components/Notifications/ToastContainer.vue';
import PrivacyPolicyModal from '@/Components/Legal/PrivacyPolicyModal.vue';
import TermsOfServiceModal from '@/Components/Legal/TermsOfServiceModal.vue';

const notifications = useNotifications();
const page = usePage();

// Watch for flash messages
watch(() => page.props.flash, (flash) => {
    if (flash?.success) {
        notifications.success(flash.success);
    }
    if (flash?.error) {
        notifications.error(flash.error);
    }
    if (flash?.warning) {
        notifications.warning(flash.warning);
    }
    if (flash?.info) {
        notifications.info(flash.info);
    }
}, { deep: true, immediate: true });

const props = defineProps({
    store: {
        type: Object,
        required: true,
    },
    customer: {
        type: Object,
        required: true,
    },
    stats: {
        type: Object,
        required: true,
    },
    loyalty: {
        type: Object,
        default: null,
    },
    recent_orders: {
        type: Array,
        default: () => [],
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const { config: themeConfig } = useTheme(props.store.theme);

const activeTab = ref('dashboard');

const profileForm = ref({
    first_name: props.customer.first_name,
    last_name: props.customer.last_name,
    mobile: props.customer.mobile || '',
    address_line1: props.customer.address_line1 || '',
    address_line2: props.customer.address_line2 || '',
    address_city: props.customer.address_city || '',
    address_state: props.customer.address_state || '',
    address_postcode: props.customer.address_postcode || '',
    address_country: props.customer.address_country || '',
});

const passwordForm = ref({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const processing = ref(false);
const processingPassword = ref(false);
const showOrderModal = ref(false);
const selectedOrder = ref(null);
const loadingOrder = ref(false);
const showPrivacyModal = ref(false);
const showPrivacyPolicyModal = ref(false);
const showTermsModal = ref(false);

const handleLogout = () => {
    router.post(`/store/${props.store.id}/logout`);
};

const openOrderModal = async (orderId) => {
    showOrderModal.value = true;
    loadingOrder.value = true;
    selectedOrder.value = null;

    try {
        const response = await fetch(`/store/${props.store.id}/orders/${orderId}/details`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (response.ok) {
            const data = await response.json();
            selectedOrder.value = data;
        } else {
            notifications.error('Failed to load order details');
            closeOrderModal();
        }
    } catch (error) {
        console.error('Error fetching order details:', error);
        notifications.error('Failed to load order details');
        closeOrderModal();
    } finally {
        loadingOrder.value = false;
    }
};

const closeOrderModal = () => {
    showOrderModal.value = false;
    setTimeout(() => {
        selectedOrder.value = null;
    }, 300);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const formatStatus = (status) => {
    const statusLabels = {
        'pending': 'Pending',
        'accepted': 'Accepted',
        'in_progress': 'In Progress',
        'preparing': 'Preparing',
        'ready': 'Ready',
        'ready_for_pickup': 'Ready for Pickup',
        'packing': 'Packing',
        'packed': 'Packed',
        'shipped': 'Shipped',
        'delivered': 'Delivered',
        'picked_up': 'Picked Up',
        'completed': 'Completed',
        'cancelled': 'Cancelled',
        'refunded': 'Refunded',
    };
    return statusLabels[status] || status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const getStatusClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        in_progress: 'bg-purple-100 text-purple-800',
        ready_for_pickup: 'bg-green-100 text-green-800',
        picked_up: 'bg-green-100 text-green-800',
        accepted: 'bg-blue-100 text-blue-800',
        preparing: 'bg-purple-100 text-purple-800',
        ready: 'bg-green-100 text-green-800',
        completed: 'bg-green-100 text-green-800',
        shipped: 'bg-blue-100 text-blue-800',
        delivered: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const updateProfile = () => {
    processing.value = true;

    router.put(`/store/${props.store.id}/profile`, profileForm.value, {
        onFinish: () => {
            processing.value = false;
        },
        onError: (errors) => {
            Object.values(errors).flat().forEach(error => {
                notifications.error('Validation Error', error);
            });
        },
    });
};

const updatePassword = () => {
    processingPassword.value = true;

    router.put(`/store/${props.store.id}/password`, passwordForm.value, {
        onFinish: () => {
            processingPassword.value = false;
        },
        onSuccess: () => {
            passwordForm.value = {
                current_password: '',
                password: '',
                password_confirmation: '',
            };
        },
        onError: (errors) => {
            Object.values(errors).flat().forEach(error => {
                notifications.error('Validation Error', error);
            });
        },
    });
};
</script>
