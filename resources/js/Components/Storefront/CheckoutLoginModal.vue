<template>
    <!-- Modal Overlay -->
    <Transition
        enter-active-class="transition-opacity duration-300 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="isOpen"
            @click="handleClose"
            class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4"
        >
            <!-- Modal Content -->
            <Transition
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="opacity-0 scale-95 translate-y-4"
                enter-to-class="opacity-100 scale-100 translate-y-0"
                leave-active-class="transition-all duration-200 ease-in"
                leave-from-class="opacity-100 scale-100 translate-y-0"
                leave-to-class="opacity-0 scale-95 translate-y-4"
            >
                <div
                    v-if="isOpen"
                    @click.stop
                    class="w-full max-w-md rounded-2xl shadow-2xl overflow-hidden"
                    :class="themeConfig.cardBackground"
                >
                    <!-- Header -->
                    <div class="relative px-6 pt-6 pb-4" :class="store.theme === 'bold' ? 'bg-gray-900/50' : 'bg-gradient-to-r from-blue-50 to-purple-50'">
                        <button
                            @click="handleClose"
                            class="absolute right-4 top-4 p-2 rounded-full transition-colors"
                            :class="store.theme === 'bold' ? 'hover:bg-gray-800 text-gray-400 hover:text-white' : 'hover:bg-white/80 text-gray-500 hover:text-gray-700'"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>

                        <div class="text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full mb-3" :class="store.theme === 'bold' ? 'bg-orange-500/20' : 'bg-blue-100'">
                                <svg class="w-8 h-8" :class="store.theme === 'bold' ? 'text-orange-500' : 'text-blue-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <h2 class="text-2xl font-bold" :class="store.theme === 'bold' ? 'text-white' : 'text-gray-900'">
                                Welcome Back
                            </h2>
                            <p class="text-sm mt-1" :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">
                                Login to continue your checkout
                            </p>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="px-6 py-6">
                        <!-- Success Message -->
                        <Transition
                            enter-active-class="transition-all duration-300"
                            enter-from-class="opacity-0 -translate-y-2"
                            enter-to-class="opacity-100 translate-y-0"
                        >
                            <div v-if="loginSuccess" class="mb-4 p-4 rounded-lg" :class="store.theme === 'bold' ? 'bg-green-900/20 border border-green-500/30' : 'bg-green-50 border border-green-200'">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5" :class="store.theme === 'bold' ? 'text-green-400' : 'text-green-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="text-sm font-medium" :class="store.theme === 'bold' ? 'text-green-300' : 'text-green-800'">
                                        Login successful! Redirecting...
                                    </span>
                                </div>
                            </div>
                        </Transition>

                        <!-- Login Form -->
                        <form @submit.prevent="handleSubmit" class="space-y-4">
                            <!-- Email -->
                            <div>
                                <label class="block text-sm font-medium mb-1.5" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">
                                    Email Address
                                </label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    required
                                    autocomplete="email"
                                    :disabled="processing || loginSuccess"
                                    class="w-full px-4 py-2.5 border rounded-lg transition-all disabled:opacity-50"
                                    :class="[
                                        store.theme === 'bold'
                                            ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-400 focus:ring-orange-500 focus:border-orange-500'
                                            : 'bg-white border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500',
                                        errors.email ? 'border-red-500 focus:ring-red-500' : ''
                                    ]"
                                    placeholder="you@example.com"
                                />
                                <p v-if="errors.email" class="mt-1.5 text-sm text-red-600">
                                    {{ errors.email }}
                                </p>
                            </div>

                            <!-- Password -->
                            <div>
                                <label class="block text-sm font-medium mb-1.5" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">
                                    Password
                                </label>
                                <div class="relative">
                                    <input
                                        v-model="form.password"
                                        :type="showPassword ? 'text' : 'password'"
                                        required
                                        autocomplete="current-password"
                                        :disabled="processing || loginSuccess"
                                        class="w-full px-4 py-2.5 border rounded-lg pr-11 transition-all disabled:opacity-50"
                                        :class="[
                                            store.theme === 'bold'
                                                ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-400 focus:ring-orange-500 focus:border-orange-500'
                                                : 'bg-white border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500',
                                            errors.password ? 'border-red-500 focus:ring-red-500' : ''
                                        ]"
                                        placeholder="Enter your password"
                                    />
                                    <button
                                        type="button"
                                        @click="showPassword = !showPassword"
                                        :disabled="processing || loginSuccess"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 p-1 disabled:opacity-50"
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
                                <p v-if="errors.password" class="mt-1.5 text-sm text-red-600">
                                    {{ errors.password }}
                                </p>
                            </div>

                            <!-- Turnstile Widget -->
                            <div>
                                <TurnstileWidget
                                    ref="turnstileRef"
                                    :site-key="siteKey"
                                    v-model="form.turnstile_token"
                                    :theme="store.theme === 'bold' ? 'dark' : 'light'"
                                />
                                <p v-if="errors.turnstile_token" class="mt-1.5 text-sm text-red-600 text-center">
                                    {{ errors.turnstile_token }}
                                </p>
                            </div>

                            <!-- Remember Me -->
                            <div class="flex items-center justify-between">
                                <label class="flex items-center cursor-pointer">
                                    <input
                                        v-model="form.remember"
                                        type="checkbox"
                                        :disabled="processing || loginSuccess"
                                        class="h-4 w-4 rounded disabled:opacity-50"
                                        :class="store.theme === 'bold' ? 'text-orange-500 focus:ring-orange-500' : 'text-blue-600 focus:ring-blue-500'"
                                    />
                                    <span class="ml-2 text-sm" :class="store.theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">
                                        Remember me
                                    </span>
                                </label>
                                <a
                                    :href="`/store/${store.id}/forgot-password`"
                                    class="text-sm font-medium"
                                    :class="themeConfig.link"
                                    @click="handleClose"
                                >
                                    Forgot password?
                                </a>
                            </div>

                            <!-- Submit Button -->
                            <button
                                type="submit"
                                :disabled="processing || loginSuccess"
                                class="w-full font-semibold py-3 px-6 rounded-lg transition-all shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                                :class="themeConfig.buttonPrimary"
                            >
                                <svg v-if="processing" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>{{ processing ? 'Logging in...' : loginSuccess ? 'Success!' : 'Login' }}</span>
                            </button>
                        </form>

                        <!-- Register Link -->
                        <div class="mt-6 text-center pt-6 border-t" :class="store.theme === 'bold' ? 'border-gray-700' : 'border-gray-200'">
                            <p class="text-sm" :class="store.theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">
                                Don't have an account?
                                <a
                                    :href="`/store/${store.id}/register`"
                                    :class="themeConfig.link"
                                    class="font-medium"
                                    @click="handleClose"
                                >
                                    Register now
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </Transition>
        </div>
    </Transition>
</template>

<script setup>
import { ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import TurnstileWidget from '@/Components/TurnstileWidget.vue';

const props = defineProps({
    isOpen: {
        type: Boolean,
        required: true,
    },
    store: {
        type: Object,
        required: true,
    },
    themeConfig: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['close', 'success']);
const page = usePage();

const form = ref({
    email: '',
    password: '',
    remember: false,
    turnstile_token: null,
});

const showPassword = ref(false);
const processing = ref(false);
const loginSuccess = ref(false);
const errors = ref({});
const turnstileRef = ref(null);

const siteKey = page.props.turnstile_site_key;

// Reset form when modal closes
watch(() => props.isOpen, (newValue) => {
    if (!newValue) {
        setTimeout(() => {
            form.value = {
                email: '',
                password: '',
                remember: false,
                turnstile_token: null,
            };
            errors.value = {};
            showPassword.value = false;
            processing.value = false;
            loginSuccess.value = false;
            turnstileRef.value?.reset();
        }, 300);
    }
});

const handleClose = () => {
    if (!processing.value) {
        emit('close');
    }
};

const handleSubmit = () => {
    // Clear previous errors
    errors.value = {};

    // Validate Turnstile token
    if (!turnstileRef.value?.isValid()) {
        errors.value.turnstile_token = 'Please complete the security verification';
        return;
    }

    processing.value = true;

    router.post(`/store/${props.store.id}/login?checkout=1`, form.value, {
        onSuccess: () => {
            loginSuccess.value = true;
            processing.value = false;

            // Show success message briefly then reload checkout
            setTimeout(() => {
                emit('success');
                emit('close');
                // Reload the page to get updated customer data
                window.location.reload();
            }, 800);
        },
        onError: (serverErrors) => {
            errors.value = serverErrors;
            processing.value = false;
            form.value.password = '';
            turnstileRef.value?.reset();
        },
        preserveState: true,
        preserveScroll: true,
    });
};
</script>
