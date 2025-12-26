<template>
    <div class="min-h-screen" :class="themeConfig.background">
        <!-- Header -->
        <header :class="store.theme === 'bold' ? 'bg-gray-900 border-b border-orange-500/20' : 'bg-white shadow-sm'">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex justify-between items-center">
                    <a :href="`/store/${store.id}`" class="flex items-center">
                        <h1 class="text-2xl font-bold" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                            {{ store.name }}
                        </h1>
                    </a>
                    <a :href="`/store/${store.id}`" :class="themeConfig.link">
                        ← Back to Store
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="rounded-lg shadow-md p-8" :class="themeConfig.cardBackground">
                <h2 class="text-3xl font-bold mb-2 text-center" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                    Create Account
                </h2>
                <p class="text-center mb-8" :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">
                    Join {{ store.name }} today
                </p>

                <!-- Registration Form -->
                <form @submit.prevent="submitRegister">
                    <!-- First Name -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">
                            First Name *
                        </label>
                        <input
                            v-model="form.first_name"
                            type="text"
                            required
                            autocomplete="given-name"
                            class="w-full px-3 py-2 border rounded-md"
                            :class="[
                                store.theme === 'bold'
                                    ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-400 focus:ring-orange-500 focus:border-orange-500'
                                    : 'bg-white border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500',
                                errors.first_name ? 'border-red-500' : ''
                            ]"
                        />
                        <p v-if="errors.first_name" class="mt-1 text-sm text-red-600">{{ errors.first_name }}</p>
                    </div>

                    <!-- Last Name -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">
                            Last Name *
                        </label>
                        <input
                            v-model="form.last_name"
                            type="text"
                            required
                            autocomplete="family-name"
                            class="w-full px-3 py-2 border rounded-md"
                            :class="[
                                store.theme === 'bold'
                                    ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-400 focus:ring-orange-500 focus:border-orange-500'
                                    : 'bg-white border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500',
                                errors.last_name ? 'border-red-500' : ''
                            ]"
                        />
                        <p v-if="errors.last_name" class="mt-1 text-sm text-red-600">{{ errors.last_name }}</p>
                    </div>

                    <!-- Email -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">
                            Email Address *
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            autocomplete="email"
                            class="w-full px-3 py-2 border rounded-md"
                            :class="[
                                store.theme === 'bold'
                                    ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-400 focus:ring-orange-500 focus:border-orange-500'
                                    : 'bg-white border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500',
                                errors.email ? 'border-red-500' : ''
                            ]"
                        />
                        <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
                    </div>

                    <!-- Mobile -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">
                            Mobile (Optional)
                        </label>
                        <input
                            v-model="form.mobile"
                            type="tel"
                            autocomplete="tel"
                            class="w-full px-3 py-2 border rounded-md"
                            :class="[
                                store.theme === 'bold'
                                    ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-400 focus:ring-orange-500 focus:border-orange-500'
                                    : 'bg-white border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500',
                                errors.mobile ? 'border-red-500' : ''
                            ]"
                        />
                        <p v-if="errors.mobile" class="mt-1 text-sm text-red-600">{{ errors.mobile }}</p>
                    </div>

                    <!-- Password -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">
                            Password * (minimum 8 characters)
                        </label>
                        <div class="relative">
                            <input
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                minlength="8"
                                autocomplete="new-password"
                                class="w-full px-3 py-2 border rounded-md pr-10"
                                :class="[
                                    store.theme === 'bold'
                                        ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-400 focus:ring-orange-500 focus:border-orange-500'
                                        : 'bg-white border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500',
                                    errors.password ? 'border-red-500' : ''
                                ]"
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
                        <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium mb-1" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">
                            Confirm Password *
                        </label>
                        <div class="relative">
                            <input
                                v-model="form.password_confirmation"
                                :type="showPasswordConfirm ? 'text' : 'password'"
                                required
                                minlength="8"
                                autocomplete="new-password"
                                class="w-full px-3 py-2 border rounded-md pr-10"
                                :class="[
                                    store.theme === 'bold'
                                        ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-400 focus:ring-orange-500 focus:border-orange-500'
                                        : 'bg-white border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500'
                                ]"
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

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="processing"
                        class="w-full font-semibold py-3 px-6 rounded-lg transition-all shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
                        :class="themeConfig.buttonPrimary"
                    >
                        {{ processing ? 'Creating Account...' : 'Create Account' }}
                    </button>
                </form>

                <!-- Login Link -->
                <div class="mt-6 text-center">
                    <p :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">
                        Already have an account?
                        <a :href="`/store/${store.id}/login`" :class="themeConfig.link" class="font-medium">
                            Login
                        </a>
                    </p>
                </div>

                <!-- Legal Links -->
                <div class="mt-4 text-center text-xs" :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">
                    By creating an account, you agree to our
                    <button
                        type="button"
                        @click="showTermsModal = true"
                        class="underline"
                        :class="themeConfig.link"
                    >Terms of Service</button>
                    and
                    <button
                        type="button"
                        @click="showPrivacyModal = true"
                        class="underline"
                        :class="themeConfig.link"
                    >Privacy Policy</button>
                </div>
            </div>
        </main>

        <!-- Privacy Policy Modal -->
        <PrivacyPolicyModal
            :isOpen="showPrivacyModal"
            @close="showPrivacyModal = false"
            :contactEmail="store.contact_email || 'hello@storeflow.com'"
        />

        <!-- Terms of Service Modal -->
        <TermsOfServiceModal
            :isOpen="showTermsModal"
            @close="showTermsModal = false"
            :contactEmail="store.contact_email || 'hello@storeflow.com'"
        />
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useTheme } from '@/Composables/useTheme';
import PrivacyPolicyModal from '@/Components/Legal/PrivacyPolicyModal.vue';
import TermsOfServiceModal from '@/Components/Legal/TermsOfServiceModal.vue';

const props = defineProps({
    store: {
        type: Object,
        required: true,
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const { config: themeConfig } = useTheme(props.store.theme);

const form = ref({
    first_name: '',
    last_name: '',
    email: '',
    mobile: '',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showPasswordConfirm = ref(false);
const processing = ref(false);

// Modal states
const showPrivacyModal = ref(false);
const showTermsModal = ref(false);

const submitRegister = () => {
    processing.value = true;

    router.post(`/store/${props.store.id}/register`, form.value, {
        onFinish: () => {
            processing.value = false;
        },
        onError: () => {
            form.value.password = '';
            form.value.password_confirmation = '';
        },
    });
};
</script>
