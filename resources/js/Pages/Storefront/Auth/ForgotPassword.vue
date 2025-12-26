<template>
    <div class="min-h-screen" :class="themeConfig.background">
        <!-- Header -->
        <header
            :class="
                store.theme === 'bold'
                    ? 'bg-gray-900 border-b border-orange-500/20'
                    : 'bg-white shadow-sm'
            "
        >
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex justify-between items-center">
                    <a :href="`/store/${store.id}`" class="flex items-center">
                        <h1
                            class="text-2xl font-bold"
                            :class="
                                store.theme === 'bold'
                                    ? 'text-white'
                                    : 'text-gray-900'
                            "
                        >
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
            <div
                class="rounded-lg shadow-md p-8"
                :class="themeConfig.cardBackground"
            >
                <h2
                    class="text-3xl font-bold mb-2 text-center"
                    :class="
                        store.theme === 'bold' ? 'text-white' : 'text-gray-900'
                    "
                >
                    Forgot Password?
                </h2>
                <p
                    class="text-center mb-8"
                    :class="
                        store.theme === 'bold'
                            ? 'text-gray-400'
                            : 'text-gray-600'
                    "
                >
                    Enter your email and we'll send you a reset link
                </p>

                <!-- Success Message -->
                <div
                    v-if="$page.props.flash?.success"
                    class="mb-6 p-4 rounded-lg"
                    :class="
                        store.theme === 'bold'
                            ? 'bg-green-900/20 border border-green-500/20'
                            : 'bg-green-50 border border-green-200'
                    "
                >
                    <div class="flex items-start">
                        <svg
                            class="w-5 h-5 mr-3 flex-shrink-0 mt-0.5"
                            :class="
                                store.theme === 'bold'
                                    ? 'text-green-400'
                                    : 'text-green-600'
                            "
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                            ></path>
                        </svg>
                        <p
                            class="text-sm"
                            :class="
                                store.theme === 'bold'
                                    ? 'text-green-300'
                                    : 'text-green-700'
                            "
                        >
                            {{ $page.props.flash.success }}
                        </p>
                    </div>
                </div>

                <!-- Forgot Password Form -->
                <form @submit.prevent="submitForgotPassword">
                    <!-- Email -->
                    <div class="mb-6">
                        <label
                            class="block text-sm font-medium mb-1"
                            :class="
                                store.theme === 'bold'
                                    ? 'text-gray-300'
                                    : 'text-gray-700'
                            "
                        >
                            Email Address
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            autocomplete="email"
                            autofocus
                            class="w-full px-3 py-2 border rounded-md"
                            :class="[
                                store.theme === 'bold'
                                    ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-400 focus:ring-orange-500 focus:border-orange-500'
                                    : 'bg-white border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500',
                                form.errors.email ? 'border-red-500' : '',
                            ]"
                            placeholder="Enter your email"
                        />
                        <p
                            v-if="form.errors.email"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between mb-6">
                        <a
                            :href="`/store/${store.id}/login`"
                            class="text-sm"
                            :class="themeConfig.link"
                        >
                            Back to Login
                        </a>

                        <button
                            type="submit"
                            :disabled="processing"
                            class="font-semibold py-2 px-6 rounded-lg transition-all shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
                            :class="themeConfig.buttonPrimary"
                        >
                            {{ processing ? "Sending..." : "Send Reset Link" }}
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import { useTheme } from "@/Composables/useTheme";

const props = defineProps({
    store: {
        type: Object,
        required: true,
    },
});

const { config: themeConfig } = useTheme(props.store.theme);

const form = ref({
    email: "",
    errors: {},
});

const processing = ref(false);

const submitForgotPassword = () => {
    processing.value = true;
    form.value.errors = {};

    router.post(`/store/${props.store.id}/forgot-password`, form.value, {
        onFinish: () => {
            processing.value = false;
        },
        onSuccess: () => {
            form.value.email = "";
        },
        onError: (errors) => {
            form.value.errors = errors;
        },
    });
};
</script>
