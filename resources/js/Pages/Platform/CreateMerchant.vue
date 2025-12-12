<template>
    <div class="min-h-screen bg-gray-900">
        <!-- Header -->
        <header class="bg-gray-800 border-b border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <Link :href="route('platform.merchants')" class="text-gray-400 hover:text-white text-sm">
                    ← Back to Merchants
                </Link>
                <h1 class="text-2xl font-bold text-white mt-2">Create New Merchant</h1>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <form @submit.prevent="submit" class="bg-gray-800 shadow rounded-lg border border-gray-700 p-6">
                <!-- Merchant Information -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-white mb-4">Merchant Information</h3>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="merchant_name" class="block text-sm font-medium text-gray-300 mb-2">
                                Merchant Name *
                            </label>
                            <input
                                id="merchant_name"
                                v-model="form.merchant_name"
                                type="text"
                                required
                                class="w-full px-3 py-2 rounded-md bg-gray-700 border border-gray-600 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="{ 'border-red-500': form.errors.merchant_name }"
                            />
                            <p v-if="form.errors.merchant_name" class="mt-1 text-sm text-red-400">
                                {{ form.errors.merchant_name }}
                            </p>
                        </div>

                        <div>
                            <label for="merchant_slug" class="block text-sm font-medium text-gray-300 mb-2">
                                Merchant Slug (optional)
                            </label>
                            <input
                                id="merchant_slug"
                                v-model="form.merchant_slug"
                                type="text"
                                placeholder="Auto-generated if empty"
                                class="w-full px-3 py-2 rounded-md bg-gray-700 border border-gray-600 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                        </div>
                    </div>
                </div>

                <!-- Owner Account Information -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-white mb-4">Owner Account</h3>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="owner_name" class="block text-sm font-medium text-gray-300 mb-2">
                                Owner Name *
                            </label>
                            <input
                                id="owner_name"
                                v-model="form.owner_name"
                                type="text"
                                required
                                class="w-full px-3 py-2 rounded-md bg-gray-700 border border-gray-600 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                        </div>

                        <div>
                            <label for="owner_email" class="block text-sm font-medium text-gray-300 mb-2">
                                Owner Email *
                            </label>
                            <input
                                id="owner_email"
                                v-model="form.owner_email"
                                type="email"
                                required
                                autocomplete="email"
                                class="w-full px-3 py-2 rounded-md bg-gray-700 border border-gray-600 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="{ 'border-red-500': form.errors.owner_email }"
                            />
                            <p v-if="form.errors.owner_email" class="mt-1 text-sm text-red-400">
                                {{ form.errors.owner_email }}
                            </p>
                        </div>

                        <div>
                            <label for="owner_username" class="block text-sm font-medium text-gray-300 mb-2">
                                Owner Username *
                            </label>
                            <input
                                id="owner_username"
                                v-model="form.owner_username"
                                type="text"
                                required
                                autocomplete="username"
                                class="w-full px-3 py-2 rounded-md bg-gray-700 border border-gray-600 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="{ 'border-red-500': form.errors.owner_username }"
                            />
                            <p v-if="form.errors.owner_username" class="mt-1 text-sm text-red-400">
                                {{ form.errors.owner_username }}
                            </p>
                        </div>

                        <div>
                            <label for="owner_password" class="block text-sm font-medium text-gray-300 mb-2">
                                Owner Password *
                            </label>
                            <input
                                id="owner_password"
                                v-model="form.owner_password"
                                type="password"
                                required
                                minlength="8"
                                autocomplete="new-password"
                                class="w-full px-3 py-2 rounded-md bg-gray-700 border border-gray-600 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="{ 'border-red-500': form.errors.owner_password }"
                            />
                            <p v-if="form.errors.owner_password" class="mt-1 text-sm text-red-400">
                                {{ form.errors.owner_password }}
                            </p>
                        </div>

                        <div>
                            <label for="owner_password_confirmation" class="block text-sm font-medium text-gray-300 mb-2">
                                Confirm Password *
                            </label>
                            <input
                                id="owner_password_confirmation"
                                v-model="form.owner_password_confirmation"
                                type="password"
                                required
                                minlength="8"
                                autocomplete="new-password"
                                class="w-full px-3 py-2 rounded-md bg-gray-700 border border-gray-600 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3">
                    <Link
                        :href="route('platform.merchants')"
                        class="px-4 py-2 rounded-md border border-gray-600 text-gray-300 hover:bg-gray-700 transition-colors"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 transition-colors"
                    >
                        {{ form.processing ? 'Creating...' : 'Create Merchant' }}
                    </button>
                </div>
            </form>
        </main>
    </div>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    merchant_name: '',
    merchant_slug: '',
    owner_name: '',
    owner_email: '',
    owner_username: '',
    owner_password: '',
    owner_password_confirmation: '',
});

const submit = () => {
    form.post(route('platform.merchants.store'));
};
</script>
