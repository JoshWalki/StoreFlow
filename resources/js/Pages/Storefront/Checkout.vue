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

                    <!-- Account Creation Option -->
                    <div class="rounded-lg shadow-md p-6" :class="themeConfig.cardBackground">
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
                            <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition-colors" :class="[
                                form.fulfilment_type === 'pickup'
                                    ? (store.theme === 'bold' ? 'border-orange-500 bg-orange-500/10' : store.theme === 'modern' ? 'border-purple-600 bg-purple-50' : 'border-blue-600 bg-blue-50')
                                    : (store.theme === 'bold' ? 'border-gray-700 hover:bg-gray-800' : 'border-gray-300 hover:bg-gray-50')
                            ]">
                                <input
                                    v-model="form.fulfilment_type"
                                    type="radio"
                                    value="pickup"
                                    :class="store.theme === 'bold' ? 'text-orange-500 focus:ring-orange-500' : store.theme === 'modern' ? 'text-purple-600 focus:ring-purple-500' : 'text-blue-600 focus:ring-blue-500'"
                                    class="h-4 w-4"
                                />
                                <span class="ml-3 text-sm font-medium" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">Pickup</span>
                            </label>
                            <label
                                v-if="store.shipping_enabled"
                                class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition-colors"
                                :class="[
                                    form.fulfilment_type === 'shipping'
                                        ? (store.theme === 'bold' ? 'border-orange-500 bg-orange-500/10' : store.theme === 'modern' ? 'border-purple-600 bg-purple-50' : 'border-blue-600 bg-blue-50')
                                        : (store.theme === 'bold' ? 'border-gray-700 hover:bg-gray-800' : 'border-gray-300 hover:bg-gray-50'),
                                    hasPickupOnlyItems ? 'opacity-50 cursor-not-allowed' : ''
                                ]"
                            >
                                <input
                                    v-model="form.fulfilment_type"
                                    type="radio"
                                    value="shipping"
                                    :disabled="hasPickupOnlyItems"
                                    :class="store.theme === 'bold' ? 'text-orange-500 focus:ring-orange-500' : store.theme === 'modern' ? 'text-purple-600 focus:ring-purple-500' : 'text-blue-600 focus:ring-blue-500'"
                                    class="h-4 w-4 disabled:opacity-50 disabled:cursor-not-allowed"
                                />
                                <span class="ml-3 text-sm font-medium" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">Delivery</span>
                            </label>
                        </div>
                    </div>

                    <!-- Shipping Address (if delivery selected) -->
                    <div v-if="form.fulfilment_type === 'shipping'" class="rounded-lg shadow-md p-6" :class="themeConfig.cardBackground">
                        <h2 class="text-xl font-semibold mb-4" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">Shipping Address</h2>
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

                            <!-- Shipping Methods -->
                            <div v-if="shippingOptions.length > 0" class="mt-4">
                                <label class="block text-sm font-medium mb-2" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">Shipping Method *</label>
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

                    <!-- Simulated Payment Notice -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">Simulated Payment</h3>
                                <p class="mt-1 text-sm text-blue-700">This is a demo store. All orders will be automatically paid using simulated payment processing.</p>
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
                                    <p class="text-sm font-medium" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">{{ formatPrice(getItemTotal(item)) }}</p>
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

                        <!-- Price Breakdown -->
                        <div class="pt-4 space-y-2 border-t" :class="store.theme === 'bold' ? 'border-gray-700' : 'border-gray-200'">
                            <div class="flex justify-between text-sm">
                                <span :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">Subtotal</span>
                                <span :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">{{ formatPrice(cartSubtotal) }}</span>
                            </div>
                            <div v-if="form.fulfilment_type === 'shipping' && selectedShippingCost > 0" class="flex justify-between text-sm">
                                <span :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">Shipping</span>
                                <span :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">{{ formatPrice(selectedShippingCost) }}</span>
                            </div>
                            <div class="flex justify-between text-base font-semibold pt-2 border-t" :class="store.theme === 'bold' ? 'border-gray-700' : 'border-gray-200'">
                                <span :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">Total</span>
                                <span :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">{{ formatPrice(orderTotal) }}</span>
                            </div>
                        </div>

                        <!-- Place Order Button -->
                        <button
                            type="submit"
                            :disabled="processing || !isFormValid"
                            class="w-full mt-6 font-semibold py-3 px-6 rounded-lg transition-all shadow-lg hover:shadow-xl disabled:bg-gray-400 disabled:cursor-not-allowed disabled:opacity-50"
                            :class="!processing && isFormValid ? themeConfig.buttonPrimary : ''"
                        >
                            {{ processing ? 'Processing...' : 'Place Order' }}
                        </button>
                    </div>
                </div>
            </form>
        </main>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useCart } from '@/Composables/useCart';
import { useTheme } from '@/Composables/useTheme';
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
});

const { cartItems, cartSubtotal, getItemTotal, formatPrice, clearCart, removeFromCart } = useCart();

// Initialize theme
const { config: themeConfig } = useTheme(props.store.theme);

const form = ref({
    contact: {
        first_name: '',
        last_name: '',
        email: '',
        mobile: '',
    },
    fulfilment_type: 'pickup',
    shipping_address: {
        name: '',
        line1: '',
        line2: '',
        city: '',
        state: '',
        postcode: '',
        country: 'Australia',
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

// Calculate selected shipping cost
const selectedShippingCost = computed(() => {
    if (form.value.fulfilment_type !== 'shipping' || !form.value.shipping_method_id) {
        return 0;
    }
    const selected = shippingOptions.value.find(opt => opt.id === form.value.shipping_method_id);
    return selected ? selected.price_cents : 0;
});

// Calculate order total
const orderTotal = computed(() => {
    return cartSubtotal.value + selectedShippingCost.value;
});

// Check if form is valid
const isFormValid = computed(() => {
    if (!form.value.contact.first_name || !form.value.contact.last_name || !form.value.contact.email) {
        return false;
    }
    if (form.value.fulfilment_type === 'shipping') {
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
    if (form.value.fulfilment_type !== 'shipping') return;
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
    if (newType === 'shipping') {
        calculateShipping();
    } else {
        shippingOptions.value = [];
        form.value.shipping_method_id = null;
    }
});

// Place order
const placeOrder = () => {
    if (!isFormValid.value) return;

    processing.value = true;

    const orderData = {
        contact: form.value.contact,
        fulfilment_type: form.value.fulfilment_type,
        items: cartItems.value.map(item => ({
            product_id: item.product.id,
            quantity: item.quantity,
        })),
        payment_method: form.value.payment_method,
    };

    // Include account data if password is provided (create account mode)
    if (form.value.account.password) {
        orderData.account = form.value.account;
    }

    if (form.value.fulfilment_type === 'shipping') {
        orderData.shipping_address = form.value.shipping_address;
        orderData.shipping_method_id = form.value.shipping_method_id;
    }

    router.post(`/store/${props.store.id}/checkout/process`, orderData, {
        onSuccess: () => {
            clearCart();
            processing.value = false;
        },
        onError: (errors) => {
            console.error('Checkout errors:', errors);
            alert('Failed to place order. Please check your information and try again.');
            processing.value = false;
        },
    });
};
</script>
