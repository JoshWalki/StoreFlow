<template>
    <div class="min-h-screen flex overflow-hidden">
        <!-- Left Section - Tips -->
        <div
            class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-blue-600 via-purple-600 to-purple-800 relative overflow-hidden"
        >
            <div class="absolute inset-0 overflow-hidden">
                <div
                    class="absolute top-20 left-10 w-72 h-72 bg-white opacity-10 rounded-full blur-3xl animate-float"
                ></div>
                <div
                    class="absolute bottom-20 right-10 w-96 h-96 bg-blue-300 opacity-10 rounded-full blur-3xl animate-float-delayed"
                ></div>
            </div>

            <div class="relative z-10 flex flex-col justify-center px-16 w-full">
                <div class="mb-8">
                    <img
                        src="/images/logo/logo-banner-white.png"
                        alt="StoreFlow"
                        class="h-12 mb-6"
                    />
                    <h2 class="text-4xl font-bold text-white mb-4">
                        Reset Your Password
                    </h2>
                    <p class="text-blue-100 text-lg">
                        Enter your username and we'll send you a password reset link
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Section - Form -->
        <div
            class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-gradient-to-br from-purple-50 via-pink-50 to-blue-50 relative overflow-hidden"
        >
            <div class="absolute inset-0 overflow-hidden">
                <div
                    class="absolute top-1/4 right-1/4 w-96 h-96 bg-purple-400 opacity-20 rounded-full blur-3xl animate-float-slow"
                ></div>
            </div>

            <div
                class="relative w-full max-w-md bg-white/70 backdrop-blur-xl rounded-3xl shadow-2xl p-8 border border-white/20"
            >
                <div class="lg:hidden mb-6 text-center">
                    <img
                        src="/images/logo/logo-banner.png"
                        alt="StoreFlow"
                        class="h-10 mx-auto mb-4"
                    />
                </div>

                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">
                        Forgot Password?
                    </h1>
                    <p class="text-gray-600">
                        No problem. Just let us know your username and we'll email you a password reset link.
                    </p>
                </div>

                <!-- Success Message -->
                <div
                    v-if="$page.props.flash?.success"
                    class="mb-6 p-4 bg-green-50/80 backdrop-blur-sm border border-green-200 rounded-xl"
                >
                    <div class="flex items-start">
                        <svg
                            class="w-5 h-5 text-green-600 mr-3 flex-shrink-0 mt-0.5"
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
                        <p class="text-sm text-green-700">
                            {{ $page.props.flash.success }}
                        </p>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label
                            for="username"
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Username
                        </label>
                        <input
                            id="username"
                            v-model="form.username"
                            type="text"
                            required
                            autofocus
                            autocomplete="username"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-white/50 backdrop-blur-sm focus:ring-2 focus:ring-purple-600 focus:border-transparent transition-all placeholder-gray-400"
                            :class="{ 'border-red-500': form.errors.username }"
                            placeholder="Enter your username"
                        />
                        <p v-if="form.errors.username" class="mt-2 text-sm text-red-600">
                            {{ form.errors.username }}
                        </p>
                    </div>

                    <div class="flex items-center justify-between">
                        <a
                            href="/login"
                            class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
                        >
                            Back to Login
                        </a>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="py-3 px-6 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl font-semibold hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-lg"
                        >
                            <span v-if="!form.processing">Email Password Reset Link</span>
                            <span v-else class="flex items-center justify-center">
                                <svg
                                    class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    ></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                                Sending...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    username: '',
});

const submit = () => {
    form.post('/forgot-password', {
        onFinish: () => form.reset('username'),
    });
};
</script>

<style scoped>
@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}

@keyframes float-delayed {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-30px); }
}

@keyframes float-slow {
    0%, 100% { transform: translate(0px, 0px); }
    33% { transform: translate(30px, -30px); }
    66% { transform: translate(-20px, 20px); }
}

.animate-float {
    animation: float 6s ease-in-out infinite;
}

.animate-float-delayed {
    animation: float-delayed 8s ease-in-out infinite;
    animation-delay: 1s;
}

.animate-float-slow {
    animation: float-slow 12s ease-in-out infinite;
}
</style>
