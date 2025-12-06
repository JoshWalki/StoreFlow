<template>
    <!-- Unclosable Modal Overlay -->
    <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-900 bg-opacity-90">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="relative w-full max-w-3xl transform rounded-lg bg-white dark:bg-gray-800 shadow-2xl transition-all">
                <!-- Header -->
                <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                    <div class="flex items-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                            <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Welcome to StoreFlow!</h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Let's create your first store to get started</p>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <form @submit.prevent="submitForm" enctype="multipart/form-data">
                    <div class="max-h-[calc(100vh-300px)] overflow-y-auto px-6 py-6">
                        <!-- Store Name (Required) -->
                        <div class="mb-6">
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Store Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                placeholder="My Store"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                            <p v-if="errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.name }}</p>
                        </div>

                        <!-- Description -->
                        <div class="mb-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Description
                            </label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                placeholder="Tell customers about your store..."
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            ></textarea>
                        </div>

                        <!-- Logo -->
                        <div class="mb-6">
                            <label for="logo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Store Logo
                            </label>
                            <input
                                id="logo"
                                ref="logoInput"
                                type="file"
                                accept="image/*"
                                @change="handleLogoChange"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                            />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">PNG, JPG or GIF (max 2MB)</p>
                            <p v-if="errors.logo" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.logo }}</p>
                        </div>

                        <!-- Contact Information -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Contact Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Email
                                    </label>
                                    <input
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        placeholder="store@example.com"
                                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>

                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Phone
                                    </label>
                                    <input
                                        id="phone"
                                        v-model="form.phone"
                                        type="tel"
                                        placeholder="+1 (555) 123-4567"
                                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Address Information -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Store Address</h3>
                            <div class="space-y-4">
                                <div>
                                    <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Street Address
                                    </label>
                                    <input
                                        id="address"
                                        v-model="form.address"
                                        type="text"
                                        placeholder="123 Main Street"
                                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="city" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            City
                                        </label>
                                        <input
                                            id="city"
                                            v-model="form.city"
                                            type="text"
                                            placeholder="New York"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                                        />
                                    </div>

                                    <div>
                                        <label for="state" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            State/Province
                                        </label>
                                        <input
                                            id="state"
                                            v-model="form.state"
                                            type="text"
                                            placeholder="NY"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                                        />
                                    </div>

                                    <div>
                                        <label for="postcode" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Postal Code
                                        </label>
                                        <input
                                            id="postcode"
                                            v-model="form.postcode"
                                            type="text"
                                            placeholder="10001"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                                        />
                                    </div>

                                    <div>
                                        <label for="country" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Country
                                        </label>
                                        <input
                                            id="country"
                                            v-model="form.country"
                                            type="text"
                                            placeholder="United States"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-4 bg-gray-50 dark:bg-gray-750">
                        <div class="flex justify-end">
                            <button
                                type="submit"
                                :disabled="processing || !form.name"
                                class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                            >
                                <span v-if="!processing">Create Store & Get Started</span>
                                <span v-else>Creating Store...</span>
                            </button>
                        </div>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400 text-right">
                            <span class="text-red-500">*</span> Required field
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const form = ref({
    name: '',
    description: '',
    logo: null,
    email: '',
    phone: '',
    address: '',
    city: '',
    state: '',
    postcode: '',
    country: '',
});

const logoInput = ref(null);
const processing = ref(false);
const errors = ref({});

const handleLogoChange = (event) => {
    form.value.logo = event.target.files[0];
};

const submitForm = () => {
    console.log('Form submission started', form.value);
    processing.value = true;
    errors.value = {};

    // Create FormData for file upload
    const formData = new FormData();
    Object.keys(form.value).forEach(key => {
        if (form.value[key] !== null && form.value[key] !== '') {
            formData.append(key, form.value[key]);
        }
    });

    console.log('Submitting to route:', route('onboarding.complete'));

    router.post(route('onboarding.complete'), formData, {
        forceFormData: true,
        onSuccess: (page) => {
            console.log('Success!', page);
        },
        onError: (err) => {
            console.error('Submission error:', err);
            errors.value = err;
        },
        onFinish: () => {
            console.log('Request finished');
            processing.value = false;
        },
    });
};
</script>
