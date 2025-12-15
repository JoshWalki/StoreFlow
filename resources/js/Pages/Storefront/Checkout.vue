<template>
    <div class="min-h-screen" :class="themeConfig.background">
        <!-- Header -->
        <header :class="store.theme === 'bold' ? 'bg-gray-900 border-b border-orange-500/20' : 'bg-white shadow-sm'">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex justify-between items-center">
                    <h1 class="text-3xl font-bold" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">{{ store.name }} - Checkout</h1>
                    <a :href="`/store/${store.id}/products`" :class="themeConfig.link">
                        ← Continue Shopping
                    </a>
                </div>
            </div>
        </header>

        <!-- Store Closed Alert -->
        <div v-if="!store.is_active" class="bg-red-500 text-white py-4 px-4">
            <div class="max-w-7xl mx-auto flex items-center justify-center gap-3">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <span class="text-lg font-semibold">This store is currently closed and not accepting orders. Please check back during operating hours.</span>
            </div>
        </div>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div v-if="cartItems.length === 0" class="text-center py-12 rounded-lg shadow-md p-8" :class="themeConfig.cardBackground">
                <svg class="mx-auto h-12 w-12" :class="store.theme === 'bold' ? 'text-gray-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">Your cart is empty</h3>
                <p class="mt-1 text-sm" :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-500'">Add some products before checking out</p>
                <div class="mt-6">
                    <a
                        :href="`/store/${store.id}/products`"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium transition-all"
                        :class="themeConfig.buttonPrimary"
                    >
                        Browse Products
                    </a>
                </div>
            </div>

            <form v-else @submit.prevent="placeOrder" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column - Forms -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Contact Information -->
                    <div class="rounded-lg shadow-md p-6" :class="themeConfig.cardBackground">
                        <h2 class="text-xl font-semibold mb-4" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">Contact Information</h2>

                        <!-- Logged in message -->
                        <div v-if="customer" class="mb-4 p-3 rounded-md flex items-center gap-2" :class="store.theme === 'bold' ? 'bg-green-900/20 border border-green-500/30' : 'bg-green-50 border border-green-200'">
                            <svg class="w-5 h-5" :class="store.theme === 'bold' ? 'text-green-400' : 'text-green-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-sm" :class="store.theme === 'bold' ? 'text-green-300' : 'text-green-800'">
                                Logged in as <strong>{{ customer.full_name }}</strong> - Your information has been pre-filled
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">First Name *</label>
                                <input
                                    v-model="form.contact.first_name"
                                    type="text"
                                    required
                                    class="w-full px-3 py-2 border rounded-md"
                                    :class="store.theme === 'bold' ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-400 focus:ring-orange-500 focus:border-orange-500' : 'bg-white border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500'"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">Last Name *</label>
                                <input
                                    v-model="form.contact.last_name"
                                    type="text"
                                    required
                                    class="w-full px-3 py-2 border rounded-md"
                                    :class="store.theme === 'bold' ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-400 focus:ring-orange-500 focus:border-orange-500' : 'bg-white border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500'"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">Email *</label>
                                <input
                                    v-model="form.contact.email"
                                    type="email"
                                    required
                                    class="w-full px-3 py-2 border rounded-md"
                                    :class="store.theme === 'bold' ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-400 focus:ring-orange-500 focus:border-orange-500' : 'bg-white border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500'"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">Mobile</label>
                                <input
                                    v-model="form.contact.mobile"
                                    type="tel"
                                    class="w-full px-3 py-2 border rounded-md"
                                    :class="store.theme === 'bold' ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-400 focus:ring-orange-500 focus:border-orange-500' : 'bg-white border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500'"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Account Creation Option (Hidden when logged in) -->
                    <div v-if="!customer" class="rounded-lg shadow-md p-6" :class="themeConfig.cardBackground">
                        <h2 class="text-xl font-semibold mb-4" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">Customer Account</h2>

                        <div class="space-y-3 mb-4">
                            <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition-colors" :class="[
                                accountMode === 'guest'
                                    ? (store.theme === 'bold' ? 'border-orange-500 bg-orange-500/10' : store.theme === 'modern' ? 'border-purple-600 bg-purple-50' : 'border-blue-600 bg-blue-50')
                                    : (store.theme === 'bold' ? 'border-gray-700 hover:bg-gray-800' : 'border-gray-300 hover:bg-gray-50')
                            ]">
                                <input
                                    v-model="accountMode"
                                    type="radio"
                                    value="guest"
                                    :class="store.theme === 'bold' ? 'text-orange-500 focus:ring-orange-500' : store.theme === 'modern' ? 'text-purple-600 focus:ring-purple-500' : 'text-blue-600 focus:ring-blue-500'"
                                    class="h-4 w-4"
                                />
                                <div class="ml-3">
                                    <span class="text-sm font-medium" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">Guest Checkout</span>
                                    <p class="text-xs mt-1" :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">Continue without creating an account</p>
                                </div>
                            </label>

                            <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition-colors" :class="[
                                accountMode === 'create'
                                    ? (store.theme === 'bold' ? 'border-orange-500 bg-orange-500/10' : store.theme === 'modern' ? 'border-purple-600 bg-purple-50' : 'border-blue-600 bg-blue-50')
                                    : (store.theme === 'bold' ? 'border-gray-700 hover:bg-gray-800' : 'border-gray-300 hover:bg-gray-50')
                            ]">
                                <input
                                    v-model="accountMode"
                                    type="radio"
                                    value="create"
                                    :class="store.theme === 'bold' ? 'text-orange-500 focus:ring-orange-500' : store.theme === 'modern' ? 'text-purple-600 focus:ring-purple-500' : 'text-blue-600 focus:ring-blue-500'"
                                    class="h-4 w-4"
                                />
                                <div class="ml-3">
                                    <span class="text-sm font-medium" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">Create Account</span>
                                    <p class="text-xs mt-1" :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">Save your information for faster checkout</p>
                                </div>
                            </label>
                        </div>

                        <!-- Password fields (shown when Create Account is selected) -->
                        <transition
                            enter-active-class="transition duration-300 ease-out"
                            enter-from-class="opacity-0 -translate-y-2"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition duration-200 ease-in"
                            leave-from-class="opacity-100 translate-y-0"
                            leave-to-class="opacity-0 -translate-y-2"
                        >
                            <div v-if="accountMode === 'create'" class="space-y-4 pt-4 border-t" :class="store.theme === 'bold' ? 'border-gray-700' : 'border-gray-200'">
                                <div>
                                    <label class="block text-sm font-medium mb-1" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">
                                        Password * (minimum 8 characters)
                                    </label>
                                    <div class="relative">
                                        <input
                                            v-model="form.account.password"
                                            :type="showPassword ? 'text' : 'password'"
                                            :required="accountMode === 'create'"
                                            minlength="8"
                                            class="w-full px-3 py-2 border rounded-md pr-10"
                                            :class="store.theme === 'bold' ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-400 focus:ring-orange-500 focus:border-orange-500' : 'bg-white border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500'"
                                        />
                                        <button
                                            type="button"
                                            @click="showPassword = !showPassword"
                                            class="absolute right-3 top-1/2 -translate-y-1/2"
                                            :class="store.theme === 'bold' ? 'text-gray-400 hover:text-gray-300' : 'text-gray-500 hover:text-gray-700'"
                                        >
                                            <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">
                                        Confirm Password *
                                    </label>
                                    <div class="relative">
                                        <input
                                            v-model="form.account.password_confirmation"
                                            :type="showPasswordConfirm ? 'text' : 'password'"
                                            :required="accountMode === 'create'"
                                            minlength="8"
                                            class="w-full px-3 py-2 border rounded-md pr-10"
                                            :class="store.theme === 'bold' ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-400 focus:ring-orange-500 focus:border-orange-500' : 'bg-white border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500'"
                                        />
                                        <button
                                            type="button"
                                            @click="showPasswordConfirm = !showPasswordConfirm"
                                            class="absolute right-3 top-1/2 -translate-y-1/2"
                                            :class="store.theme === 'bold' ? 'text-gray-400 hover:text-gray-300' : 'text-gray-500 hover:text-gray-700'"
                                        >
                                            <svg v-if="!showPasswordConfirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="flex items-start p-3 rounded-lg" :class="store.theme === 'bold' ? 'bg-gray-800' : 'bg-blue-50'">
                                    <svg class="w-5 h-5 mt-0.5 flex-shrink-0" :class="store.theme === 'bold' ? 'text-orange-500' : 'text-blue-600'" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                    <p class="ml-3 text-sm" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-blue-800'">
                                        Your account will be created after order completion. You'll be able to track orders and manage your profile.
                                    </p>
                                </div>
                            </div>
                        </transition>

                        <!-- Already have account link -->
                        <div class="mt-4 pt-4 border-t text-center" :class="store.theme === 'bold' ? 'border-gray-700' : 'border-gray-200'">
                            <p class="text-sm" :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">
                                Already have an account?
                                <a :href="`/store/${store.id}/login`" :class="themeConfig.link" class="font-medium">
                                    Login
                                </a>
                            </p>
                        </div>
                    </div>

                    <!-- Fulfillment Type -->
                    <div class="rounded-lg shadow-md p-6" :class="themeConfig.cardBackground">
                        <h2 class="text-xl font-semibold mb-4" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">Delivery Method</h2>

                        <!-- Pickup Only Items Error -->
                        <div v-if="hasPickupOnlyItems" class="mb-4 bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex">
                                <svg class="h-5 w-5 text-red-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                                <div class="ml-3 flex-1">
                                    <h3 class="text-sm font-medium text-red-800">Your cart contains pickup-only items</h3>
                                    <p class="mt-1 text-sm text-red-700">The following items are only available for pickup and cannot be shipped:</p>
                                    <ul class="mt-2 space-y-1">
                                        <li v-for="item in pickupOnlyItems" :key="item.id" class="text-sm text-red-700 flex items-center">
                                            <svg class="h-4 w-4 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <span class="font-medium">{{ item.product.name }}</span>
                                            <span v-if="item.quantity > 1" class="ml-1">(Qty: {{ item.quantity }})</span>
                                        </li>
                                    </ul>
                                    <p class="mt-2 text-sm text-red-700">Please select pickup or remove these items to enable delivery.</p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <label class="flex items-start p-4 border-2 rounded-lg cursor-pointer transition-colors" :class="[
                                form.fulfilment_type === 'pickup'
                                    ? (store.theme === 'bold' ? 'border-orange-500 bg-orange-500/10' : store.theme === 'modern' ? 'border-purple-600 bg-purple-50' : 'border-blue-600 bg-blue-50')
                                    : (store.theme === 'bold' ? 'border-gray-700 hover:bg-gray-800' : 'border-gray-300 hover:bg-gray-50')
                            ]">
                                <input
                                    v-model="form.fulfilment_type"
                                    type="radio"
                                    value="pickup"
                                    :class="store.theme === 'bold' ? 'text-orange-500 focus:ring-orange-500' : store.theme === 'modern' ? 'text-purple-600 focus:ring-purple-500' : 'text-blue-600 focus:ring-blue-500'"
                                    class="h-4 w-4 mt-0.5"
                                />
                                <div class="ml-3 flex-1">
                                    <span class="block text-sm font-medium mb-1" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">Pickup</span>
                                    <!-- Store Address -->
                                    <div v-if="store.address_primary || store.address_city" class="flex items-start mt-2">
                                        <svg class="w-4 h-4 text-gray-400 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                        </svg>
                                        <div class="text-xs" :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">
                                            <p v-if="store.address_primary">{{ store.address_primary }}</p>
                                            <p v-if="store.address_city || store.address_state || store.address_postcode">
                                                <span v-if="store.address_city">{{ store.address_city }}</span><span v-if="store.address_city && store.address_state">, </span><span v-if="store.address_state">{{ store.address_state }}</span><span v-if="store.address_postcode"> {{ store.address_postcode }}</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </label>
                            <label
                                v-if="store.shipping_enabled"
                                class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition-colors"
                                :class="[
                                    form.fulfilment_type === 'delivery'
                                        ? (store.theme === 'bold' ? 'border-orange-500 bg-orange-500/10' : store.theme === 'modern' ? 'border-purple-600 bg-purple-50' : 'border-blue-600 bg-blue-50')
                                        : (store.theme === 'bold' ? 'border-gray-700 hover:bg-gray-800' : 'border-gray-300 hover:bg-gray-50'),
                                    hasPickupOnlyItems ? 'opacity-50 cursor-not-allowed' : ''
                                ]"
                            >
                                <input
                                    v-model="form.fulfilment_type"
                                    type="radio"
                                    value="delivery"
                                    :disabled="hasPickupOnlyItems"
                                    :class="store.theme === 'bold' ? 'text-orange-500 focus:ring-orange-500' : store.theme === 'modern' ? 'text-purple-600 focus:ring-purple-500' : 'text-blue-600 focus:ring-blue-500'"
                                    class="h-4 w-4 disabled:opacity-50 disabled:cursor-not-allowed"
                                />
                                <span class="ml-3 text-sm font-medium" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">Delivery</span>
                            </label>
                        </div>
                    </div>

                    <!-- Delivery Address (if delivery selected) -->
                    <div v-if="form.fulfilment_type === 'delivery'" class="rounded-lg shadow-md p-6" :class="themeConfig.cardBackground">
                        <h2 class="text-xl font-semibold mb-4" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">Delivery Address</h2>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium mb-1" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">Full Name *</label>
                                <input
                                    v-model="form.shipping_address.name"
                                    type="text"
                                    required
                                    class="w-full px-3 py-2 border rounded-md"
                                    :class="store.theme === 'bold' ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-400 focus:ring-orange-500 focus:border-orange-500' : 'bg-white border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500'"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">Address Line 1 *</label>
                                <input
                                    v-model="form.shipping_address.line1"
                                    type="text"
                                    required
                                    class="w-full px-3 py-2 border rounded-md"
                                    :class="store.theme === 'bold' ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-400 focus:ring-orange-500 focus:border-orange-500' : 'bg-white border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500'"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">Address Line 2</label>
                                <input
                                    v-model="form.shipping_address.line2"
                                    type="text"
                                    class="w-full px-3 py-2 border rounded-md"
                                    :class="store.theme === 'bold' ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-400 focus:ring-orange-500 focus:border-orange-500' : 'bg-white border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500'"
                                />
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">City *</label>
                                    <input
                                        v-model="form.shipping_address.city"
                                        type="text"
                                        required
                                        class="w-full px-3 py-2 border rounded-md"
                                        :class="store.theme === 'bold' ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-400 focus:ring-orange-500 focus:border-orange-500' : 'bg-white border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500'"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">State *</label>
                                    <input
                                        v-model="form.shipping_address.state"
                                        type="text"
                                        required
                                        class="w-full px-3 py-2 border rounded-md"
                                        :class="store.theme === 'bold' ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-400 focus:ring-orange-500 focus:border-orange-500' : 'bg-white border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500'"
                                    />
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">Postcode *</label>
                                    <input
                                        v-model="form.shipping_address.postcode"
                                        type="text"
                                        required
                                        @input="calculateShipping"
                                        class="w-full px-3 py-2 border rounded-md"
                                        :class="store.theme === 'bold' ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-400 focus:ring-orange-500 focus:border-orange-500' : 'bg-white border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500'"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">Country *</label>
                                    <select
                                        v-model="form.shipping_address.country"
                                        required
                                        @change="calculateShipping"
                                        class="w-full px-3 py-2 border rounded-md"
                                        :class="store.theme === 'bold' ? 'bg-gray-800 border-gray-700 text-white focus:ring-orange-500 focus:border-orange-500' : 'bg-white border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500'"
                                    >
                                        <option value="">Select a country</option>
                                        <option value="Australia">Australia</option>
                                        <option value="New Zealand">New Zealand</option>
                                        <option value="United States">United States</option>
                                        <option value="United Kingdom">United Kingdom</option>
                                        <option value="Canada">Canada</option>
                                        <option value="Singapore">Singapore</option>
                                        <option value="Malaysia">Malaysia</option>
                                        <option value="Indonesia">Indonesia</option>
                                        <option value="Thailand">Thailand</option>
                                        <option value="Philippines">Philippines</option>
                                        <option value="Vietnam">Vietnam</option>
                                        <option value="Japan">Japan</option>
                                        <option value="China">China</option>
                                        <option value="South Korea">South Korea</option>
                                        <option value="Hong Kong">Hong Kong</option>
                                        <option value="Taiwan">Taiwan</option>
                                        <option value="India">India</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Delivery Methods -->
                            <div v-if="shippingOptions.length > 0" class="mt-4">
                                <label class="block text-sm font-medium mb-2" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">Delivery Method *</label>
                                <div class="space-y-2">
                                    <label
                                        v-for="option in shippingOptions"
                                        :key="option.id"
                                        class="flex items-center justify-between p-3 border-2 rounded-lg cursor-pointer transition-colors"
                                        :class="[
                                            form.shipping_method_id === option.id
                                                ? (store.theme === 'bold' ? 'border-orange-500 bg-orange-500/10' : store.theme === 'modern' ? 'border-purple-600 bg-purple-50' : 'border-blue-600 bg-blue-50')
                                                : (store.theme === 'bold' ? 'border-gray-700 hover:bg-gray-800' : 'border-gray-300 hover:bg-gray-50')
                                        ]"
                                    >
                                        <div class="flex items-center">
                                            <input
                                                v-model="form.shipping_method_id"
                                                type="radio"
                                                :value="option.id"
                                                :class="store.theme === 'bold' ? 'text-orange-500 focus:ring-orange-500' : store.theme === 'modern' ? 'text-purple-600 focus:ring-purple-500' : 'text-blue-600 focus:ring-blue-500'"
                                                class="h-4 w-4"
                                            />
                                            <div class="ml-3">
                                                <p class="text-sm font-medium" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">{{ option.name }}</p>
                                                <p v-if="option.description" class="text-xs" :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-500'">{{ option.description }}</p>
                                            </div>
                                        </div>
                                        <span class="text-sm font-medium" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">{{ formatPrice(option.price_cents) }}</span>
                                    </label>
                                </div>
                            </div>
                            <div v-else-if="loadingShipping" class="text-sm" :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-500'">
                                Calculating shipping options...
                            </div>
                            <div v-else-if="form.shipping_address.postcode && form.shipping_address.country" class="text-sm text-red-600">
                                No shipping options available for this address
                            </div>
                        </div>
                    </div>

                    <!-- Payment Section -->
                    <div class="rounded-lg shadow-md p-6" :class="themeConfig.cardBackground">
                        <h2 class="text-xl font-semibold mb-4" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">Payment</h2>

                        <!-- Show payment form only if form is valid and payment intent is ready -->
                        <div v-if="showPaymentForm">
                            <StripePaymentForm
                                v-if="paymentClientSecret"
                                :publishable-key="$page.props.stripe.publishableKey"
                                :client-secret="paymentClientSecret"
                                :return-url="paymentReturnUrl"
                                @payment-success="handlePaymentSuccess"
                                @payment-error="handlePaymentError"
                            />
                        </div>

                        <!-- Prepare payment button -->
                        <div v-else>
                            <p class="text-sm mb-4" :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">
                                Click below to proceed to secure payment.
                            </p>
                            <button
                                @click="preparePayment"
                                :disabled="!isFormValid || preparingPayment"
                                class="w-full py-3 px-6 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
                            >
                                <svg v-if="preparingPayment" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span v-if="preparingPayment">Preparing payment...</span>
                                <span v-else>Proceed to Payment</span>
                            </button>
                        </div>

                        <!-- Payment Error -->
                        <div v-if="paymentError" class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div class="flex-1">
                                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Payment Error</h3>
                                    <p class="mt-1 text-sm text-red-700 dark:text-red-300">{{ paymentError }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Order Summary -->
                <div class="lg:col-span-1">
                    <div class="rounded-lg shadow-md p-6 sticky top-4" :class="themeConfig.cardBackground">
                        <h2 class="text-xl font-semibold mb-4" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">Order Summary</h2>

                        <!-- Cart Items -->
                        <div class="space-y-4 mb-4 max-h-64 overflow-y-auto">
                            <div v-for="item in cartItems" :key="item.id" class="flex items-start space-x-3 group relative">
                                <div class="h-16 w-16 flex-shrink-0 overflow-hidden rounded border" :class="store.theme === 'bold' ? 'border-gray-700' : 'border-gray-200'">
                                    <img
                                        v-if="item.product.image"
                                        :src="getImageUrl(item.product.image)"
                                        :alt="item.product.name"
                                        class="h-full w-full object-cover"
                                    />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium truncate" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">{{ item.product.name }}</p>
                                    <p class="text-sm" :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-500'">Qty: {{ item.quantity }}</p>

                                    <!-- Addons -->
                                    <div v-if="item.addons && item.addons.length > 0" class="mt-1 text-xs" :class="store.theme === 'bold' ? 'text-gray-500' : 'text-gray-600'">
                                        <div v-for="(addon, addonIdx) in item.addons" :key="addonIdx" class="flex justify-between">
                                            <span>+ {{ addon.addon_name }}: {{ addon.option_name }}</span>
                                            <span v-if="addon.price_adjustment > 0">+{{ formatCurrency(addon.price_adjustment) }}</span>
                                        </div>
                                    </div>

                                    <p class="text-sm font-medium mt-1" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">{{ formatPrice(getItemTotal(item)) }}</p>
                                </div>
                                <!-- Remove Button -->
                                <button
                                    @click="removeFromCart(item.id)"
                                    class="flex-shrink-0 p-1 rounded-full transition-colors"
                                    :class="store.theme === 'bold' ? 'text-gray-400 hover:text-red-400 hover:bg-gray-800' : 'text-gray-400 hover:text-red-600 hover:bg-red-50'"
                                    title="Remove item"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Loyalty Reward Section -->
                        <div v-if="loyaltyReward" class="pt-4 border-t" :class="store.theme === 'bold' ? 'border-gray-700' : 'border-gray-200'">
                            <div class="rounded-lg p-4 mb-4" :class="[
                                loyaltyReward.eligible
                                    ? (store.theme === 'bold' ? 'bg-purple-900/20 border border-purple-500/30' : 'bg-purple-50 border border-purple-200')
                                    : (store.theme === 'bold' ? 'bg-gray-800 border border-gray-700' : 'bg-gray-50 border border-gray-200')
                            ]">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5" :class="loyaltyReward.eligible ? 'text-purple-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                        </svg>
                                        <span class="text-sm font-semibold" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                                            Loyalty Reward
                                        </span>
                                    </div>
                                    <span class="text-xs px-2 py-1 rounded-full" :class="[
                                        loyaltyReward.eligible
                                            ? 'bg-purple-600 text-white'
                                            : (store.theme === 'bold' ? 'bg-gray-700 text-gray-300' : 'bg-gray-200 text-gray-600')
                                    ]">
                                        {{ loyaltyReward.points_balance }} pts
                                    </span>
                                </div>

                                <div v-if="loyaltyReward.eligible" class="space-y-3">
                                    <p class="text-xs" :class="store.theme === 'bold' ? 'text-purple-300' : 'text-purple-700'">
                                        {{ loyaltyReward.reward_config.description }}
                                    </p>
                                    <label class="flex items-center cursor-pointer">
                                        <input
                                            type="checkbox"
                                            v-model="applyLoyaltyReward"
                                            class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded"
                                        />
                                        <span class="ml-2 text-sm font-medium" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                                            Apply reward ({{ loyaltyReward.threshold }} points)
                                        </span>
                                    </label>
                                    <div v-if="applyLoyaltyReward && loyaltyDiscount > 0" class="text-xs p-2 rounded" :class="store.theme === 'bold' ? 'bg-gray-900 text-green-400' : 'bg-green-50 text-green-700'">
                                        <strong>Discount:</strong> -{{ formatPrice(loyaltyDiscount) }}
                                        <br />
                                        <strong>Remaining Points:</strong> {{ loyaltyReward.points_balance - loyaltyReward.threshold }}
                                    </div>
                                </div>

                                <div v-else class="text-xs" :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">
                                    <p>Earn {{ loyaltyReward.threshold - loyaltyReward.points_balance }} more points to unlock your reward!</p>
                                    <div class="mt-2 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div
                                            class="bg-purple-600 h-2 rounded-full transition-all"
                                            :style="{width: Math.min((loyaltyReward.points_balance / loyaltyReward.threshold) * 100, 100) + '%'}"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Price Breakdown -->
                        <div class="pt-4 space-y-2 border-t" :class="store.theme === 'bold' ? 'border-gray-700' : 'border-gray-200'">
                            <div class="flex justify-between text-sm">
                                <span :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">Subtotal</span>
                                <span :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">{{ formatPrice(cartSubtotal) }}</span>
                            </div>
                            <div v-if="form.fulfilment_type === 'delivery' && selectedShippingCost > 0" class="flex justify-between text-sm">
                                <span :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">Delivery</span>
                                <span :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">{{ formatPrice(selectedShippingCost) }}</span>
                            </div>
                            <div v-if="applyLoyaltyReward && loyaltyDiscount > 0" class="flex justify-between text-sm">
                                <span class="text-purple-600 dark:text-purple-400">Loyalty Discount</span>
                                <span class="text-purple-600 dark:text-purple-400">-{{ formatPrice(loyaltyDiscount) }}</span>
                            </div>
                            <div class="flex justify-between text-base font-semibold pt-2 border-t" :class="store.theme === 'bold' ? 'border-gray-700' : 'border-gray-200'">
                                <span :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">Total</span>
                                <span :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">{{ formatPrice(finalTotal) }}</span>
                            </div>
                        </div>

                        <!-- Info Text -->
                        <div v-if="!showPaymentForm" class="mt-6 text-center text-sm" :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">
                            Complete your information on the left to proceed to payment
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useCart } from '@/Composables/useCart';
import { useTheme } from '@/Composables/useTheme';
import StripePaymentForm from '@/Components/Storefront/StripePaymentForm.vue';
import axios from 'axios';

const props = defineProps({
    store: {
        type: Object,
        required: true,
    },
    zones: {
        type: Object,
        default: () => ({}),
    },
    customer: {
        type: Object,
        default: null,
    },
    loyaltyReward: {
        type: Object,
        default: null,
    },
});

const { cartItems, cartSubtotal, getItemTotal, formatPrice, clearCart, removeFromCart } = useCart();

// Initialize theme
const { config: themeConfig } = useTheme(props.store.theme);

// Auto-fill form with customer data if logged in
const form = ref({
    contact: {
        first_name: props.customer?.first_name || '',
        last_name: props.customer?.last_name || '',
        email: props.customer?.email || '',
        mobile: props.customer?.mobile || '',
    },
    fulfilment_type: 'pickup',
    shipping_address: {
        name: props.customer ? `${props.customer.first_name} ${props.customer.last_name}` : '',
        line1: props.customer?.address_line1 || '',
        line2: props.customer?.address_line2 || '',
        city: props.customer?.address_city || '',
        state: props.customer?.address_state || '',
        postcode: props.customer?.address_postcode || '',
        country: props.customer?.address_country || 'Australia',
    },
    shipping_method_id: null,
    payment_method: 'simulated',
    account: {
        password: '',
        password_confirmation: '',
    },
});

const accountMode = ref('guest');
const showPassword = ref(false);
const showPasswordConfirm = ref(false);
const shippingOptions = ref([]);
const loadingShipping = ref(false);
const processing = ref(false);
const applyLoyaltyReward = ref(false);

// Payment state
const showPaymentForm = ref(false);
const preparingPayment = ref(false);
const paymentClientSecret = ref(null);
const paymentIntentId = ref(null);
const paymentError = ref(null);

// Check if cart has any pickup-only items
const hasPickupOnlyItems = computed(() => {
    return cartItems.value.some(item => !item.product.is_shippable);
});

// Get list of pickup-only items
const pickupOnlyItems = computed(() => {
    return cartItems.value.filter(item => !item.product.is_shippable);
});

// Check if all items are shippable
const allItemsShippable = computed(() => {
    return cartItems.value.every(item => item.product.is_shippable);
});

// Get image URL helper
const getImageUrl = (imagePath) => {
    if (!imagePath) return null;
    return imagePath.startsWith('/storage/') ? imagePath : `/storage/${imagePath}`;
};

// Format currency helper for dollar amounts
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-AU', {
        style: 'currency',
        currency: 'AUD',
    }).format(amount);
};

// Calculate selected shipping cost
const selectedShippingCost = computed(() => {
    if (form.value.fulfilment_type !== 'delivery' || !form.value.shipping_method_id) {
        return 0;
    }
    const selected = shippingOptions.value.find(opt => opt.id === form.value.shipping_method_id);
    return selected ? selected.price_cents : 0;
});

// Calculate order total (before loyalty discount)
const orderTotal = computed(() => {
    return cartSubtotal.value + selectedShippingCost.value;
});

// Calculate loyalty discount
const loyaltyDiscount = computed(() => {
    if (!applyLoyaltyReward.value || !props.loyaltyReward || !props.loyaltyReward.eligible) {
        return 0;
    }

    const rewardConfig = props.loyaltyReward.reward_config;
    let discountCents = 0;

    if (rewardConfig.type === 'percentage') {
        // Percentage discount
        discountCents = Math.floor((orderTotal.value * rewardConfig.value) / 100);
    } else if (rewardConfig.type === 'fixed_amount') {
        // Fixed amount discount (convert dollars to cents)
        discountCents = Math.floor(rewardConfig.value * 100);
    }

    // Ensure discount doesn't exceed order total
    return Math.min(discountCents, orderTotal.value);
});

// Calculate final total (after loyalty discount)
const finalTotal = computed(() => {
    return Math.max(0, orderTotal.value - loyaltyDiscount.value);
});

// Stripe payment return URL (must be absolute)
const paymentReturnUrl = computed(() => {
    return `${window.location.origin}/store/${props.store.id}/checkout/success`;
});

// Check if form is valid
const isFormValid = computed(() => {
    if (!form.value.contact.first_name || !form.value.contact.last_name || !form.value.contact.email) {
        return false;
    }
    if (form.value.fulfilment_type === 'delivery') {
        // Check if shipping is allowed (no pickup-only items)
        if (hasPickupOnlyItems.value) {
            return false;
        }
        if (!form.value.shipping_address.name || !form.value.shipping_address.line1 ||
            !form.value.shipping_address.city || !form.value.shipping_address.state ||
            !form.value.shipping_address.postcode || !form.value.shipping_method_id) {
            return false;
        }
    }
    return cartItems.value.length > 0;
});

// Calculate shipping options
const calculateShipping = async () => {
    if (form.value.fulfilment_type !== 'delivery') return;
    if (!form.value.shipping_address.postcode || !form.value.shipping_address.country) return;

    loadingShipping.value = true;
    shippingOptions.value = [];
    form.value.shipping_method_id = null;

    try {
        const response = await axios.post(`/api/v1/stores/${props.store.id}/shipping/quote`, {
            country: form.value.shipping_address.country,
            state: form.value.shipping_address.state,
            postcode: form.value.shipping_address.postcode,
            items: cartItems.value.map(item => ({
                product_id: item.product.id,
                qty: item.quantity,
            })),
        });

        if (response.data.shipping_options) {
            shippingOptions.value = response.data.shipping_options;
            // Auto-select first option
            if (shippingOptions.value.length > 0) {
                form.value.shipping_method_id = shippingOptions.value[0].id;
            }
        }
    } catch (error) {
        console.error('Failed to calculate shipping:', error);
    } finally {
        loadingShipping.value = false;
    }
};

// Watch for fulfillment type changes
watch(() => form.value.fulfilment_type, (newType) => {
    if (newType === 'delivery') {
        calculateShipping();
    } else {
        shippingOptions.value = [];
        form.value.shipping_method_id = null;
    }
});

// Prepare payment - create payment intent
const preparePayment = async () => {
    if (!isFormValid.value) return;

    preparingPayment.value = true;
    paymentError.value = null;

    try {
        const orderData = {
            contact: form.value.contact,
            fulfilment_type: form.value.fulfilment_type,
            items: cartItems.value.map(item => ({
                product_id: item.product.id,
                qty: item.quantity,
                customizations: item.customizations || [],
                addons: item.addons || [],
            })),
        };

        if (form.value.fulfilment_type === 'delivery') {
            orderData.shipping_address = form.value.shipping_address;
            orderData.shipping_method_id = form.value.shipping_method_id;
        }

        // Apply loyalty discount if applicable
        if (applyLoyaltyReward.value && props.loyaltyReward?.eligible) {
            orderData.discount_cents = -loyaltyDiscount.value;
        }

        // Create payment intent
        const response = await axios.post(`/api/v1/stores/${props.store.id}/payment-intent`, orderData);

        paymentClientSecret.value = response.data.client_secret;
        paymentIntentId.value = response.data.payment_intent_id;
        showPaymentForm.value = true;
    } catch (error) {
        console.error('Failed to prepare payment:', error);
        console.error('Validation errors:', error.response?.data?.errors);
        paymentError.value = error.response?.data?.message || 'Failed to initialize payment. Please try again.';
    } finally {
        preparingPayment.value = false;
    }
};

// Handle successful payment
const handlePaymentSuccess = (paymentIntent) => {
    console.log('Payment successful:', paymentIntent);
    paymentIntentId.value = paymentIntent.id;
    // Now submit the order with payment_intent_id
    placeOrder();
};

// Handle payment error
const handlePaymentError = (error) => {
    console.error('Payment error:', error);
    paymentError.value = error.message || 'Payment failed. Please try again.';
};

// Place order
const placeOrder = () => {
    if (!isFormValid.value || !paymentIntentId.value) return;

    // Prevent double submission
    if (processing.value) {
        console.warn('Order already being processed, ignoring duplicate request');
        return;
    }

    processing.value = true;

    const orderData = {
        contact: form.value.contact,
        fulfilment_type: form.value.fulfilment_type,
        items: cartItems.value.map(item => ({
            product_id: item.product.id,
            qty: item.quantity,
            customizations: item.customizations || [],
            addons: item.addons || [],
            specialMessage: item.specialMessage || null,
        })),
        payment_method: 'card',
        payment_intent_id: paymentIntentId.value,
        apply_loyalty_reward: applyLoyaltyReward.value && props.loyaltyReward?.eligible,
    };

    // Include account data if password is provided (create account mode)
    if (form.value.account.password) {
        orderData.account = form.value.account;
    }

    if (form.value.fulfilment_type === 'delivery') {
        orderData.shipping_address = form.value.shipping_address;
        orderData.shipping_method_id = form.value.shipping_method_id;
    }

    // Use axios for API call instead of Inertia router
    axios.post(`/api/v1/stores/${props.store.id}/checkout`, orderData)
        .then((response) => {
            // Clear cart and redirect to success page
            clearCart();
            window.location.href = `/store/${props.store.id}/order/success/${response.data.public_id}`;
        })
        .catch((error) => {
            console.error('Checkout error:', error);
            paymentError.value = error.response?.data?.message || 'Failed to complete order. Please contact support.';
            processing.value = false;
        });
};
</script>
