<template>
    <DashboardLayout :store="store" :user="user">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Store Settings</h1>
            </div>

            <!-- Basic Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">
                    Basic Information
                </h2>
                <form @submit.prevent="saveBasicInfo" class="space-y-4">
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

                    <div>
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
                                Store will automatically close when outside operating hours
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

                    <div class="flex items-center">
                        <input
                            id="is_active"
                            v-model="basicForm.is_active"
                            type="checkbox"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        />
                        <label
                            for="is_active"
                            class="ml-2 block text-sm text-gray-700 dark:text-gray-300"
                        >
                            Store is active and accepting orders
                        </label>
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
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">
                    Store Logo
                </h2>
                <div class="space-y-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Upload your store logo to display on your storefront.
                        Recommended aspect ratios: 4:3 or 16:9. Maximum file
                        size: 5MB.
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
                            <p class="text-sm text-gray-600 mb-3">
                                Current logo
                            </p>
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
                                    store.logo_url
                                        ? "Replace Logo"
                                        : "Upload Logo"
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
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
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
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">
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
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">
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
                            <option value="Australia/Perth">
                                Perth (AWST)
                            </option>
                            <option value="Australia/Adelaide">
                                Adelaide (ACDT/ACST)
                            </option>
                            <option value="Australia/Hobart">
                                Hobart (AEDT/AEST)
                            </option>
                            <option value="Australia/Darwin">
                                Darwin (ACST)
                            </option>
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

                    <div class="flex items-center">
                        <input
                            id="auto_fulfill"
                            v-model="businessForm.auto_fulfill"
                            type="checkbox"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        />
                        <label
                            for="auto_fulfill"
                            class="ml-2 block text-sm text-gray-700 dark:text-gray-300"
                        >
                            Automatically fulfill orders when paid
                        </label>
                    </div>

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

            <!-- Storefront Theme -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">
                    Storefront Theme
                </h2>
                <form @submit.prevent="saveTheme" class="space-y-4">
                    <p class="text-sm text-gray-600 mb-4">
                        Choose a theme for your storefront. This will change the
                        look and feel of your customer-facing store.<br />
                        More coming soon, for free!
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Classic Theme -->
                        <label
                            class="relative cursor-pointer rounded-lg border-2 p-4 hover:shadow-md transition-all"
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
                                            v-if="
                                                themeForm.theme_key ===
                                                'classic'
                                            "
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
                            class="relative cursor-pointer rounded-lg border-2 p-4 hover:shadow-md transition-all"
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
                                            v-if="
                                                themeForm.theme_key === 'modern'
                                            "
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
                                        Minimalist design with gradient accents
                                        and rounded corners
                                    </p>
                                </div>
                            </div>
                        </label>

                        <!-- Bold Theme -->
                        <label
                            class="relative cursor-pointer rounded-lg border-2 p-4 hover:shadow-md transition-all"
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
                                            v-if="
                                                themeForm.theme_key === 'bold'
                                            "
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
                                        Dark theme with vibrant orange accents
                                        for a bold statement
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
                            {{
                                themeForm.processing
                                    ? "Saving..."
                                    : "Save Theme"
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import DashboardLayout from "@/Layouts/DashboardLayout.vue";

const props = defineProps({
    storeSettings: Object,
    store: Object,
    user: Object,
});

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
    open_time: props.store.open_time ? props.store.open_time.substring(0, 5) : '',
    close_time: props.store.close_time ? props.store.close_time.substring(0, 5) : '',
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

const themeForm = useForm({
    theme_key: props.store?.theme_key || "classic",
});

const saveBasicInfo = () => {
    basicForm.put(route("store.settings.basic"), {
        preserveScroll: true,
    });
};

const saveContactInfo = () => {
    contactForm.put(route("store.settings.contact"), {
        preserveScroll: true,
    });
};

const saveBusinessSettings = () => {
    businessForm.put(route("store.settings.business"), {
        preserveScroll: true,
    });
};

const saveTheme = () => {
    themeForm.put(route("store.settings.theme"), {
        preserveScroll: true,
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
        onSuccess: () => {
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
        router.delete(route("store.settings.logo.remove"));
    }
};
</script>
