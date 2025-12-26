<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                Store Settings
            </h1>
        </div>

        <!-- Main Navigation Tabs -->
        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700"
        >
            <nav class="flex overflow-x-auto">
                <button
                    @click="navigateToSection('basic')"
                    class="px-6 py-3 text-sm font-medium whitespace-nowrap border-b-2 transition-colors"
                    :class="
                        activeSection === 'basic' || activeSection === 'logo' || activeSection === 'theme' || activeSection === 'sales'
                            ? 'border-blue-600 text-blue-600 dark:text-blue-400'
                            : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-600'
                    "
                >
                    Store Settings
                </button>
                <button
                    @click="navigateToSection('contact')"
                    class="px-6 py-3 text-sm font-medium whitespace-nowrap border-b-2 transition-colors"
                    :class="
                        activeSection === 'contact'
                            ? 'border-blue-600 text-blue-600 dark:text-blue-400'
                            : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-600'
                    "
                >
                    Contact Information
                </button>
                <button
                    @click="navigateToSection('business')"
                    class="px-6 py-3 text-sm font-medium whitespace-nowrap border-b-2 transition-colors"
                    :class="
                        activeSection === 'business'
                            ? 'border-blue-600 text-blue-600 dark:text-blue-400'
                            : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-600'
                    "
                >
                    Business Settings
                </button>
                <button
                    @click="navigateToSection('pickup')"
                    class="px-6 py-3 text-sm font-medium whitespace-nowrap border-b-2 transition-colors"
                    :class="
                        activeSection === 'pickup'
                            ? 'border-blue-600 text-blue-600 dark:text-blue-400'
                            : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-600'
                    "
                >
                    Pickup Settings
                </button>
                <button
                    v-if="user && user.role === 'owner'"
                    @click="navigateToSection('stripe')"
                    class="px-6 py-3 text-sm font-medium whitespace-nowrap border-b-2 transition-colors"
                    :class="
                        activeSection === 'stripe'
                            ? 'border-blue-600 text-blue-600 dark:text-blue-400'
                            : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-600'
                    "
                >
                    Stripe Connect
                </button>
                <button
                    v-if="user && user.role === 'owner'"
                    @click="navigateToSection('subscription')"
                    class="px-6 py-3 text-sm font-medium whitespace-nowrap border-b-2 transition-colors"
                    :class="
                        activeSection === 'subscription'
                            ? 'border-blue-600 text-blue-600 dark:text-blue-400'
                            : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-600'
                    "
                >
                    Subscription
                </button>
                <button
                    @click="navigateToSection('migration')"
                    class="px-6 py-3 text-sm font-medium whitespace-nowrap border-b-2 transition-colors"
                    :class="
                        activeSection === 'migration'
                            ? 'border-blue-600 text-blue-600 dark:text-blue-400'
                            : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-600'
                    "
                >
                    Data Migration
                </button>
            </nav>
        </div>

        <!-- Store Settings Section (with sub-navigation) -->
        <div v-if="activeSection === 'basic' || activeSection === 'logo' || activeSection === 'theme' || activeSection === 'sales'" class="rounded-lg shadow-sm overflow-hidden">
            <!-- Sub-navigation - Underline Style -->
            <nav class="flex bg-gray-50 dark:bg-gray-900/20">
                <button
                    @click="navigateToSubSection('basic')"
                    class="px-5 py-3 text-sm font-medium transition-all duration-200 relative whitespace-nowrap"
                    :class="
                        activeSection === 'basic'
                            ? 'text-blue-600 dark:text-blue-400 bg-white dark:bg-gray-800 rounded-t-lg -mb-0.5 z-10'
                            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white/50 dark:hover:bg-gray-800/50'
                    "
                >
                    Information
                </button>
                <button
                    @click="navigateToSubSection('logo')"
                    class="px-5 py-3 text-sm font-medium transition-all duration-200 relative whitespace-nowrap"
                    :class="
                        activeSection === 'logo'
                            ? 'text-blue-600 dark:text-blue-400 bg-white dark:bg-gray-800 rounded-t-lg -mb-0.5 z-10'
                            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white/50 dark:hover:bg-gray-800/50'
                    "
                >
                    Logo
                </button>
                <button
                    @click="navigateToSubSection('theme')"
                    class="px-5 py-3 text-sm font-medium transition-all duration-200 relative whitespace-nowrap"
                    :class="
                        activeSection === 'theme'
                            ? 'text-blue-600 dark:text-blue-400 bg-white dark:bg-gray-800 rounded-t-lg -mb-0.5 z-10'
                            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white/50 dark:hover:bg-gray-800/50'
                    "
                >
                    Theme
                </button>
                <button
                    v-if="user && user.role === 'owner'"
                    @click="navigateToSubSection('sales')"
                    class="px-5 py-3 text-sm font-medium transition-all duration-200 relative whitespace-nowrap"
                    :class="
                        activeSection === 'sales'
                            ? 'text-blue-600 dark:text-blue-400 bg-white dark:bg-gray-800 rounded-t-lg -mb-0.5 z-10'
                            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white/50 dark:hover:bg-gray-800/50'
                    "
                >
                    Sales & Discounts
                </button>
            </nav>

            <!-- Store Information Content -->
            <div
                v-if="activeSection === 'basic'"
                class="p-6 bg-white dark:bg-gray-800 rounded-b-lg transition-all"
            >
                <h2
                    class="text-lg font-semibold mb-4"
                    :class="!basicForm.is_active
                        ? 'text-red-800 dark:text-red-300'
                        : 'text-gray-800 dark:text-white'"
                >
                    Store Information
                </h2>

            <!-- Store Inactive Warning -->
            <div
                v-if="!basicForm.is_active"
                class="mb-6 p-4 bg-red-100 dark:bg-red-900/40 border-l-4 border-red-500 rounded-r-lg"
            >
                <div class="flex items-start">
                    <svg
                        class="w-6 h-6 text-red-600 dark:text-red-400 mr-3 flex-shrink-0 mt-0.5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                        />
                    </svg>
                    <div>
                        <p class="font-semibold text-red-800 dark:text-red-300">
                            Store is Currently Inactive
                        </p>
                        <p class="mt-1 text-sm text-red-700 dark:text-red-400">
                            Your store is not accepting orders. Customers cannot browse products or place orders.
                            Enable the toggle below to activate your store.
                        </p>
                    </div>
                </div>
            </div>

            <form @submit.prevent="saveBasicInfo" class="space-y-4">
                <!-- Store Active Toggle (Moved to top) -->
                <div class="p-4 rounded-lg" :class="!basicForm.is_active
                    ? 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800'
                    : 'bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600'">

                    <!-- Subscription Warning -->
                    <div
                        v-if="!hasActiveSubscription"
                        class="mb-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg"
                    >
                        <div class="flex items-start">
                            <svg
                                class="w-5 h-5 text-yellow-600 dark:text-yellow-500 mr-3 flex-shrink-0 mt-0.5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                />
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300">
                                    No Active Subscription
                                </p>
                                <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-400">
                                    You need an active subscription to enable your store.
                                    <a
                                        href="/store/settings#subscription"
                                        class="font-semibold underline hover:text-yellow-900 dark:hover:text-yellow-200"
                                    >
                                        Subscribe now
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>

                    <ToggleSwitch
                        v-model="basicForm.is_active"
                        label="Store is active and accepting orders"
                        :disabled="!hasActiveSubscription"
                    />
                    <p class="mt-2 text-xs" :class="!basicForm.is_active
                        ? 'text-red-600 dark:text-red-400'
                        : 'text-gray-500 dark:text-gray-400'">
                        {{ basicForm.is_active
                            ? '✓ Your store is live and customers can place orders'
                            : '✗ Your store is offline - customers cannot browse or order' }}
                    </p>
                </div>

                <div>
                    <label
                        for="name"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                    >
                        Store Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="name"
                        v-model="basicForm.name"
                        type="text"
                        required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <p
                        v-if="basicForm.errors.name"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ basicForm.errors.name }}
                    </p>
                </div>

                <!-- Subdomain field - Hidden for now, may be used in future -->
                <div v-if="false">
                    <label
                        for="subdomain"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                    >
                        Subdomain <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center">
                        <input
                            id="subdomain"
                            v-model="basicForm.subdomain"
                            type="text"
                            required
                            class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-l-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500"
                        />
                        <span
                            class="px-4 py-2 bg-gray-100 dark:bg-gray-600 border border-l-0 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-r-md"
                        >
                            .storeflow.com
                        </span>
                    </div>
                    <p
                        v-if="basicForm.errors.subdomain"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ basicForm.errors.subdomain }}
                    </p>
                    <p class="mt-1 text-sm text-gray-500">
                        Your storefront URL
                    </p>
                </div>

                <div>
                    <label
                        for="description"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                    >
                        Description
                    </label>
                    <textarea
                        id="description"
                        v-model="basicForm.description"
                        rows="3"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <p
                        v-if="basicForm.errors.description"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ basicForm.errors.description }}
                    </p>
                </div>

                <!-- Operating Hours -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label
                            for="open_time"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                        >
                            Opening Time
                        </label>
                        <input
                            id="open_time"
                            v-model="basicForm.open_time"
                            type="time"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                        />
                        <p class="mt-1 text-sm text-gray-500">
                            Store will automatically close when outside
                            operating hours
                        </p>
                    </div>

                    <div>
                        <label
                            for="close_time"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                        >
                            Closing Time
                        </label>
                        <input
                            id="close_time"
                            v-model="basicForm.close_time"
                            type="time"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                        />
                        <p class="mt-1 text-sm text-gray-500">
                            Must be manually re-enabled after closing
                        </p>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="basicForm.processing"
                        class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{
                            basicForm.processing
                                ? "Saving..."
                                : "Save Basic Info"
                        }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Store Logo -->
        <div
            v-if="activeSection === 'logo'"
            class="p-6 bg-white dark:bg-gray-800 rounded-b-lg"
        >
            <h2
                class="text-lg font-semibold text-gray-800 dark:text-white mb-4"
            >
                Store Logo
            </h2>
            <div class="space-y-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Upload your store logo to display on your storefront.
                    Recommended aspect ratios: 4:3 or 16:9. Maximum file size:
                    5MB.
                </p>

                <!-- Current Logo Preview -->
                <div v-if="store.logo_url" class="flex items-start gap-6">
                    <div class="flex-shrink-0">
                        <img
                            :src="store.logo_url"
                            :alt="store.name + ' logo'"
                            class="max-w-xs max-h-32 object-contain border border-gray-200 rounded-lg"
                        />
                    </div>
                    <div class="flex-1">
                        <p class="text-sm text-gray-600 mb-3">Current logo</p>
                        <button
                            @click="removeLogo"
                            type="button"
                            :disabled="logoForm.processing"
                            class="text-red-600 hover:text-red-700 text-sm font-medium disabled:opacity-50"
                        >
                            Remove Logo
                        </button>
                    </div>
                </div>

                <!-- Logo Upload Form -->
                <form @submit.prevent="uploadLogo" class="space-y-4">
                    <div>
                        <label
                            for="logo"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                        >
                            {{
                                store.logo_url ? "Replace Logo" : "Upload Logo"
                            }}
                        </label>
                        <input
                            id="logo"
                            ref="logoInput"
                            type="file"
                            accept="image/*"
                            @change="handleLogoSelect"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                        />
                        <p
                            v-if="logoForm.errors.logo"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ logoForm.errors.logo }}
                        </p>
                        <p class="mt-1 text-sm text-gray-500">
                            Accepted formats: JPEG, PNG, GIF, WebP
                        </p>
                    </div>

                    <!-- Image Preview -->
                    <div v-if="logoPreview" class="mt-4">
                        <p
                            class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                        >
                            Preview:
                        </p>
                        <img
                            :src="logoPreview"
                            alt="Logo preview"
                            class="max-w-xs max-h-32 object-contain border border-gray-200 rounded-lg"
                        />
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            :disabled="!logoFile || logoForm.processing"
                            class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{
                                logoForm.processing
                                    ? "Uploading..."
                                    : "Upload Logo"
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Contact Information -->
        <div
            v-if="activeSection === 'contact'"
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6"
        >
            <h2
                class="text-lg font-semibold text-gray-800 dark:text-white mb-4"
            >
                Contact Information
            </h2>
            <form @submit.prevent="saveContactInfo" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label
                            for="email"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                        >
                            Email
                        </label>
                        <input
                            id="email"
                            v-model="contactForm.email"
                            type="email"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>

                    <div>
                        <label
                            for="phone"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                        >
                            Phone
                        </label>
                        <input
                            id="phone"
                            v-model="contactForm.phone"
                            type="tel"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>
                </div>

                <div>
                    <label
                        for="address"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                    >
                        Address
                    </label>
                    <input
                        id="address"
                        v-model="contactForm.address"
                        type="text"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label
                            for="city"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                        >
                            City
                        </label>
                        <input
                            id="city"
                            v-model="contactForm.city"
                            type="text"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>

                    <div>
                        <label
                            for="state"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                        >
                            State
                        </label>
                        <input
                            id="state"
                            v-model="contactForm.state"
                            type="text"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>

                    <div>
                        <label
                            for="postcode"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                        >
                            Post Code
                        </label>
                        <input
                            id="postcode"
                            v-model="contactForm.postcode"
                            type="text"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>
                </div>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="contactForm.processing"
                        class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{
                            contactForm.processing
                                ? "Saving..."
                                : "Save Contact Info"
                        }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Business Settings -->
        <div
            v-if="activeSection === 'business'"
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6"
        >
            <h2
                class="text-lg font-semibold text-gray-800 dark:text-white mb-4"
            >
                Business Settings
            </h2>
            <form @submit.prevent="saveBusinessSettings" class="space-y-4">
                <div>
                    <label
                        for="currency"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                    >
                        Currency
                    </label>
                    <select
                        id="currency"
                        v-model="businessForm.currency"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="AUD">AUD - Australian Dollar</option>
                        <option value="USD">USD - US Dollar</option>
                        <option value="EUR">EUR - Euro</option>
                        <option value="GBP">GBP - British Pound</option>
                        <option value="CAD">CAD - Canadian Dollar</option>
                    </select>
                </div>

                <div>
                    <label
                        for="timezone"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                    >
                        Timezone
                    </label>
                    <select
                        id="timezone"
                        v-model="businessForm.timezone"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="Australia/Sydney">
                            Sydney (AEDT/AEST)
                        </option>
                        <option value="Australia/Melbourne">
                            Melbourne (AEDT/AEST)
                        </option>
                        <option value="Australia/Brisbane">
                            Brisbane (AEST)
                        </option>
                        <option value="Australia/Perth">Perth (AWST)</option>
                        <option value="Australia/Adelaide">
                            Adelaide (ACDT/ACST)
                        </option>
                        <option value="Australia/Hobart">
                            Hobart (AEDT/AEST)
                        </option>
                        <option value="Australia/Darwin">Darwin (ACST)</option>
                    </select>
                </div>

                <div>
                    <label
                        for="tax_rate"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                    >
                        Default Tax Rate (%)
                    </label>
                    <input
                        id="tax_rate"
                        v-model.number="businessForm.tax_rate"
                        type="number"
                        step="0.01"
                        min="0"
                        max="100"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>
                <!-- Deprecated
                <ToggleSwitch
                    v-model="businessForm.auto_fulfill"
                    label="Automatically fulfill orders when paid"
                />
                -->
                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="businessForm.processing"
                        class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{
                            businessForm.processing
                                ? "Saving..."
                                : "Save Business Settings"
                        }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Pickup Settings Section -->
        <div
            v-if="activeSection === 'pickup'"
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6"
        >
            <h2
                class="text-lg font-semibold text-gray-800 dark:text-white mb-4"
            >
                Pickup Settings
            </h2>
            <form @submit.prevent="savePickupSettings" class="space-y-4">
                <div>
                    <label
                        for="default_pickup_minutes"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                    >
                        Default Pickup Time (minutes)
                    </label>
                    <input
                        id="default_pickup_minutes"
                        v-model.number="pickupForm.default_pickup_minutes"
                        type="number"
                        step="5"
                        min="5"
                        max="480"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="30"
                    />
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        This sets the default estimated time for order pickup (5-480 minutes)
                    </p>
                </div>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="pickupForm.processing"
                        class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{
                            pickupForm.processing
                                ? "Saving..."
                                : "Save Pickup Settings"
                        }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Storefront Theme -->
        <div
            v-if="activeSection === 'theme'"
            class="p-6 bg-white dark:bg-gray-800 rounded-b-lg"
        >
            <h2
                class="text-lg font-semibold text-gray-800 dark:text-white mb-4"
            >
                Storefront Theme
            </h2>
            <form @submit.prevent="saveTheme" class="space-y-4">
                <p class="text-sm text-gray-600 mb-4">
                    Choose a theme for your storefront. This will change the
                    look and feel of your customer-facing store.<br />
                    More coming soon, for free!
                </p>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Classic Theme -->
                    <label
                        class="relative cursor-pointer rounded-lg border-2 p-3 hover:shadow-md transition-all"
                        :class="
                            themeForm.theme_key === 'classic'
                                ? 'border-blue-600 bg-blue-50'
                                : 'border-gray-300'
                        "
                    >
                        <input
                            type="radio"
                            v-model="themeForm.theme_key"
                            value="classic"
                            class="sr-only"
                        />
                        <div class="space-y-3">
                            <!-- Theme Preview -->
                            <div
                                class="aspect-video bg-gradient-to-br from-blue-50 to-blue-100 rounded-md border border-blue-200 flex items-center justify-center"
                            >
                                <div class="text-center">
                                    <div
                                        class="w-16 h-16 mx-auto bg-blue-600 rounded-lg mb-2 flex items-center justify-center"
                                    >
                                        <svg
                                            class="w-8 h-8 text-white"
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
                                    </div>
                                    <div class="space-y-1">
                                        <div
                                            class="h-2 w-20 bg-blue-600 rounded mx-auto"
                                        ></div>
                                        <div
                                            class="h-1.5 w-16 bg-blue-400 rounded mx-auto"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                            <!-- Theme Info -->
                            <div>
                                <h3
                                    class="font-semibold text-gray-900 flex items-center"
                                >
                                    Classic
                                    <span
                                        v-if="themeForm.theme_key === 'classic'"
                                        class="ml-2"
                                    >
                                        <svg
                                            class="w-5 h-5 text-blue-600"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </span>
                                </h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    Traditional e-commerce design with blue
                                    accents and clean layouts
                                </p>
                            </div>
                        </div>
                    </label>

                    <!-- Modern Theme -->
                    <label
                        class="relative cursor-pointer rounded-lg border-2 p-3 hover:shadow-md transition-all"
                        :class="
                            themeForm.theme_key === 'modern'
                                ? 'border-purple-600 bg-purple-50'
                                : 'border-gray-300'
                        "
                    >
                        <input
                            type="radio"
                            v-model="themeForm.theme_key"
                            value="modern"
                            class="sr-only"
                        />
                        <div class="space-y-3">
                            <!-- Theme Preview -->
                            <div
                                class="aspect-video bg-gradient-to-br from-purple-50 via-pink-50 to-purple-100 rounded-md border border-purple-200 flex items-center justify-center"
                            >
                                <div class="text-center">
                                    <div
                                        class="w-16 h-16 mx-auto bg-gradient-to-br from-purple-600 to-pink-600 rounded-2xl mb-2 flex items-center justify-center shadow-lg"
                                    >
                                        <svg
                                            class="w-8 h-8 text-white"
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
                                    </div>
                                    <div class="space-y-1">
                                        <div
                                            class="h-2 w-20 bg-gradient-to-r from-purple-600 to-pink-600 rounded mx-auto"
                                        ></div>
                                        <div
                                            class="h-1.5 w-16 bg-purple-300 rounded mx-auto"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                            <!-- Theme Info -->
                            <div>
                                <h3
                                    class="font-semibold text-gray-900 flex items-center"
                                >
                                    Modern
                                    <span
                                        v-if="themeForm.theme_key === 'modern'"
                                        class="ml-2"
                                    >
                                        <svg
                                            class="w-5 h-5 text-purple-600"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </span>
                                </h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    Minimalist design with gradient accents and
                                    rounded corners
                                </p>
                            </div>
                        </div>
                    </label>

                    <!-- Bold Theme -->
                    <label
                        class="relative cursor-pointer rounded-lg border-2 p-3 hover:shadow-md transition-all"
                        :class="
                            themeForm.theme_key === 'bold'
                                ? 'border-orange-600 bg-orange-50'
                                : 'border-gray-300'
                        "
                    >
                        <input
                            type="radio"
                            v-model="themeForm.theme_key"
                            value="bold"
                            class="sr-only"
                        />
                        <div class="space-y-3">
                            <!-- Theme Preview -->
                            <div
                                class="aspect-video bg-gradient-to-br from-gray-900 to-gray-800 rounded-md border border-orange-500 flex items-center justify-center"
                            >
                                <div class="text-center">
                                    <div
                                        class="w-16 h-16 mx-auto bg-gradient-to-br from-orange-500 to-yellow-500 rounded-lg mb-2 flex items-center justify-center shadow-xl"
                                    >
                                        <svg
                                            class="w-8 h-8 text-gray-900"
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
                                    </div>
                                    <div class="space-y-1">
                                        <div
                                            class="h-2 w-20 bg-gradient-to-r from-orange-500 to-yellow-500 rounded mx-auto"
                                        ></div>
                                        <div
                                            class="h-1.5 w-16 bg-gray-400 rounded mx-auto"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                            <!-- Theme Info -->
                            <div>
                                <h3
                                    class="font-semibold text-gray-900 flex items-center"
                                >
                                    Bold
                                    <span
                                        v-if="themeForm.theme_key === 'bold'"
                                        class="ml-2"
                                    >
                                        <svg
                                            class="w-5 h-5 text-orange-600"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </span>
                                </h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    Dark theme with vibrant orange accents for a
                                    bold statement
                                </p>
                            </div>
                        </div>
                    </label>

                    <!-- Monochrome Theme -->
                    <label
                        class="relative cursor-pointer rounded-lg border-2 p-3 hover:shadow-md transition-all"
                        :class="
                            themeForm.theme_key === 'monochrome'
                                ? 'border-gray-900 bg-gray-50'
                                : 'border-gray-300'
                        "
                    >
                        <input
                            type="radio"
                            v-model="themeForm.theme_key"
                            value="monochrome"
                            class="sr-only"
                        />
                        <div class="space-y-3">
                            <!-- Theme Preview -->
                            <div
                                class="aspect-video bg-white rounded-md border border-gray-200 flex items-center justify-center"
                            >
                                <div class="text-center">
                                    <div
                                        class="w-16 h-16 mx-auto bg-gray-900/90 backdrop-blur-xl rounded-lg mb-2 flex items-center justify-center shadow-2xl"
                                    >
                                        <svg
                                            class="w-8 h-8 text-white"
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
                                    </div>
                                    <div class="space-y-1">
                                        <div
                                            class="h-2 w-20 bg-gray-900 rounded mx-auto"
                                        ></div>
                                        <div
                                            class="h-1.5 w-16 bg-gray-400 rounded mx-auto"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                            <!-- Theme Info -->
                            <div>
                                <h3
                                    class="font-semibold text-gray-900 flex items-center"
                                >
                                    Monochrome
                                    <span
                                        v-if="
                                            themeForm.theme_key === 'monochrome'
                                        "
                                        class="ml-2"
                                    >
                                        <svg
                                            class="w-5 h-5 text-gray-900"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </span>
                                </h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    Clean white theme with iOS-style glass
                                    buttons
                                </p>
                            </div>
                        </div>
                    </label>
                </div>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="themeForm.processing"
                        class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ themeForm.processing ? "Saving..." : "Save Theme" }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Stripe Connect -->
        <div
            v-if="activeSection === 'stripe' && user && user.role === 'owner'"
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6"
        >
            <h2
                class="text-lg font-semibold text-gray-800 dark:text-white mb-4"
            >
                Stripe Connect
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                Connect your Stripe account to accept payments from customers.
                This is required before you can process orders.
            </p>
            <StripeConnectWidget />
        </div>

        <!-- Subscription Management -->
        <div
            v-if="
                activeSection === 'subscription' &&
                user &&
                user.role === 'owner'
            "
        >
            <SubscriptionTab />
        </div>

        <!-- Sales & Discounts -->
        <div
            v-if="activeSection === 'sales' && user && user.role === 'owner'"
            class="p-6 bg-white dark:bg-gray-800 rounded-b-lg"
        >
            <SalesTab />
        </div>
        </div>
        <!-- End Store Settings Section -->

        <!-- Data Migration -->
        <div
            v-if="activeSection === 'migration'"
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6"
        >
            <DataMigrationTab :store="store" :has-active-subscription="hasActiveSubscription" />
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import { useToast } from "@/Composables/useToast";
import ToggleSwitch from "@/Components/ToggleSwitch.vue";
import StripeConnectWidget from "@/Components/StripeConnectWidget.vue";
import DataMigrationTab from "@/Components/DataMigration/DataMigrationTab.vue";
import SubscriptionTab from "@/Components/Settings/SubscriptionTab.vue";
import SalesTab from "@/Components/Settings/SalesTab.vue";

const toast = useToast();

const props = defineProps({
    storeSettings: Object,
    store: Object,
    user: Object,
    merchant: Object,
});

// Active section for sub-navigation
const activeSection = ref("basic");

// Check if subscription is active
const hasActiveSubscription = computed(() => {
    const status = props.merchant?.subscription_status;
    return status && status !== "canceled" && status !== null;
});

// Check for hash in URL on mount and when hash changes
onMounted(() => {
    const hash = window.location.hash.replace("#", "");
    if (
        hash &&
        [
            "basic",
            "logo",
            "contact",
            "business",
            "pickup",
            "theme",
            "stripe",
            "subscription",
            "sales",
            "migration",
        ].includes(hash)
    ) {
        activeSection.value = hash;
    }

    // Listen for hash changes
    window.addEventListener("hashchange", () => {
        const newHash = window.location.hash.replace("#", "");
        if (
            newHash &&
            [
                "basic",
                "logo",
                "contact",
                "business",
                "pickup",
                "theme",
                "stripe",
                "subscription",
                "sales",
                "migration",
            ].includes(newHash)
        ) {
            activeSection.value = newHash;
        }
    });
});

// Navigate to section and update URL hash
const navigateToSection = (section) => {
    activeSection.value = section;
    window.location.hash = section;
};

// Navigate to sub-section within Store Settings
const navigateToSubSection = (subSection) => {
    activeSection.value = subSection;
    window.location.hash = subSection;
};

// Logo upload state
const logoInput = ref(null);
const logoFile = ref(null);
const logoPreview = ref(null);
const logoForm = useForm({
    logo: null,
});

const basicForm = useForm({
    name: props.store.name,
    subdomain: props.store.subdomain,
    description: props.store.description,
    is_active: props.store.is_active,
    open_time: props.store.open_time
        ? props.store.open_time.substring(0, 5)
        : "",
    close_time: props.store.close_time
        ? props.store.close_time.substring(0, 5)
        : "",
});

const contactForm = useForm({
    email: props.storeSettings?.email || "",
    phone: props.storeSettings?.phone || "",
    address: props.storeSettings?.address || "",
    city: props.storeSettings?.city || "",
    state: props.storeSettings?.state || "",
    postcode: props.storeSettings?.postcode || "",
});

const businessForm = useForm({
    currency: props.storeSettings?.currency || "AUD",
    timezone: props.storeSettings?.timezone || "Australia/Sydney",
    tax_rate: props.storeSettings?.tax_rate || 0,
    auto_fulfill: props.storeSettings?.auto_fulfill || false,
});

const pickupForm = useForm({
    default_pickup_minutes: props.store?.default_pickup_minutes || 30,
});

const themeForm = useForm({
    theme_key: props.store?.theme_key || "classic",
});

const saveBasicInfo = () => {
    basicForm.put(route("store.settings.basic"), {
        preserveScroll: true,
        onSuccess: (page) => {
            const flash = page.props?.flash;
            if (flash?.success) {
                toast.success(flash.success);
            } else if (flash?.error) {
                toast.error(flash.error);
            }
        },
    });
};

const saveContactInfo = () => {
    contactForm.put(route("store.settings.contact"), {
        preserveScroll: true,
        onSuccess: (page) => {
            const flash = page.props?.flash;
            if (flash?.success) {
                toast.success(flash.success);
            } else if (flash?.error) {
                toast.error(flash.error);
            }
        },
    });
};

const saveBusinessSettings = () => {
    businessForm.put(route("store.settings.business"), {
        preserveScroll: true,
        onSuccess: (page) => {
            const flash = page.props?.flash;
            if (flash?.success) {
                toast.success(flash.success);
            } else if (flash?.error) {
                toast.error(flash.error);
            }
        },
    });
};

const savePickupSettings = () => {
    pickupForm.put(route("store.settings.pickup"), {
        preserveScroll: true,
        onSuccess: (page) => {
            const flash = page.props?.flash;
            if (flash?.success) {
                toast.success(flash.success);
            } else if (flash?.error) {
                toast.error(flash.error);
            }
        },
    });
};

const saveTheme = () => {
    themeForm.put(route("store.settings.theme"), {
        preserveScroll: true,
        onSuccess: (page) => {
            const flash = page.props?.flash;
            if (flash?.success) {
                toast.success(flash.success);
            } else if (flash?.error) {
                toast.error(flash.error);
            }
        },
    });
};

// Logo upload handlers
const handleLogoSelect = (event) => {
    const file = event.target.files[0];
    if (file) {
        logoFile.value = file;

        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            logoPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const uploadLogo = () => {
    if (!logoFile.value) return;

    logoForm.logo = logoFile.value;
    logoForm.post(route("store.settings.logo"), {
        forceFormData: true,
        onSuccess: (page) => {
            const flash = page.props?.flash;
            if (flash?.success) {
                toast.success(flash.success);
            } else if (flash?.error) {
                toast.error(flash.error);
            }
            logoFile.value = null;
            logoPreview.value = null;
            if (logoInput.value) {
                logoInput.value.value = "";
            }
        },
    });
};

const removeLogo = () => {
    if (confirm("Are you sure you want to remove the store logo?")) {
        router.delete(route("store.settings.logo.remove"), {
            onSuccess: (page) => {
                const flash = page.props?.flash;
                if (flash?.success) {
                    toast.success(flash.success);
                } else if (flash?.error) {
                    toast.error(flash.error);
                }
            },
        });
    }
};
</script>
