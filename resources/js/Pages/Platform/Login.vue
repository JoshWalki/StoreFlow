<template>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900">
        <div class="max-w-md w-full space-y-8 p-8">
            <div class="text-center">
                <h2 class="text-4xl font-bold text-white mb-2">StoreFlow</h2>
                <p class="text-gray-400 text-sm">Platform Owner Dashboard</p>
            </div>

            <form @submit.prevent="submit" class="mt-8 space-y-6 bg-gray-800 p-8 rounded-lg shadow-2xl border border-gray-700">
                <!-- Error Message -->
                <div v-if="errorMessage" class="bg-red-900/50 border border-red-700 text-red-200 px-4 py-3 rounded">
                    {{ errorMessage }}
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-2">
                        Access Password
                    </label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        required
                        class="appearance-none rounded-md relative block w-full px-3 py-3 border border-gray-600 placeholder-gray-500 text-white bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Enter platform password"
                    />
                </div>

                <!-- Turnstile Widget -->
                <div>
                    <TurnstileWidget
                        ref="turnstileRef"
                        :site-key="page.props.turnstile_site_key"
                        v-model="form.turnstile_token"
                        theme="dark"
                    />
                    <p v-if="page.props.errors?.turnstile_token" class="mt-2 text-sm text-red-400 text-center">
                        {{ page.props.errors.turnstile_token }}
                    </p>
                </div>

                <!-- Submit Button -->
                <div>
                    <button
                        type="submit"
                        :disabled="processing"
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                    >
                        <span v-if="!processing">Access Dashboard</span>
                        <span v-else>Verifying...</span>
                    </button>
                </div>
            </form>

            <p class="text-center text-xs text-gray-500 mt-4">
                Restricted access for StoreFlow platform administrators only
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import TurnstileWidget from '@/Components/TurnstileWidget.vue';

const form = ref({
    password: '',
    turnstile_token: null,
});

const processing = ref(false);
const turnstileRef = ref(null);
const page = usePage();

const errorMessage = computed(() => page.props.flash?.error || null);

const submit = () => {
    // Validate Turnstile token
    if (!turnstileRef.value?.isValid()) {
        // Set error in page props
        if (!page.props.errors) {
            page.props.errors = {};
        }
        page.props.errors.turnstile_token = 'Please complete the security verification';
        return;
    }

    processing.value = true;

    router.post(route('platform.login.post'), form.value, {
        onFinish: () => {
            processing.value = false;
            turnstileRef.value?.reset();
            // Clear password on error
            if (errorMessage.value) {
                form.value.password = '';
            }
        },
        onError: () => {
            turnstileRef.value?.reset();
        },
    });
};
</script>
