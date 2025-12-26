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
                    Reset Password
                </h2>
                <p
                    class="text-center mb-8"
                    :class="
                        store.theme === 'bold'
                            ? 'text-gray-400'
                            : 'text-gray-600'
                    "
                >
                    Enter your new password below
                </p>

                <!-- Reset Password Form -->
                <form @submit.prevent="submitResetPassword">
                    <input type="hidden" v-model="form.token" />

                    <!-- New Password -->
                    <div class="mb-4">
                        <label
                            class="block text-sm font-medium mb-1"
                            :class="
                                store.theme === 'bold'
                                    ? 'text-gray-300'
                                    : 'text-gray-700'
                            "
                        >
                            New Password
                        </label>
                        <input
                            v-model="form.password"
                            type="password"
                            required
                            autocomplete="new-password"
                            autofocus
                            class="w-full px-3 py-2 border rounded-md"
                            :class="[
                                store.theme === 'bold'
                                    ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-400 focus:ring-orange-500 focus:border-orange-500'
                                    : 'bg-white border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500',
                                form.errors.password ? 'border-red-500' : '',
                            ]"
                            placeholder="Enter new password"
                        />
                        <p
                            v-if="form.errors.password"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-6">
                        <label
                            class="block text-sm font-medium mb-1"
                            :class="
                                store.theme === 'bold'
                                    ? 'text-gray-300'
                                    : 'text-gray-700'
                            "
                        >
                            Confirm Password
                        </label>
                        <input
                            v-model="form.password_confirmation"
                            type="password"
                            required
                            autocomplete="new-password"
                            class="w-full px-3 py-2 border rounded-md"
                            :class="[
                                store.theme === 'bold'
                                    ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-400 focus:ring-orange-500 focus:border-orange-500'
                                    : 'bg-white border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500',
                                form.errors.password_confirmation ? 'border-red-500' : '',
                            ]"
                            placeholder="Confirm new password"
                        />
                        <p
                            v-if="form.errors.password_confirmation"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.password_confirmation }}
                        </p>
                    </div>

                    <!-- Token Error -->
                    <p
                        v-if="form.errors.token"
                        class="mb-4 text-sm text-red-600 text-center"
                    >
                        {{ form.errors.token }}
                    </p>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="processing"
                        class="w-full font-semibold py-3 px-6 rounded-lg transition-all shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
                        :class="themeConfig.buttonPrimary"
                    >
                        {{
                            processing
                                ? "Resetting Password..."
                                : "Reset Password"
                        }}
                    </button>
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
    token: {
        type: String,
        required: true,
    },
});

const { config: themeConfig } = useTheme(props.store.theme);

const form = ref({
    token: props.token,
    password: "",
    password_confirmation: "",
    errors: {},
});

const processing = ref(false);

const submitResetPassword = () => {
    processing.value = true;

    router.post(`/store/${props.store.id}/reset-password`, form.value, {
        onFinish: () => {
            processing.value = false;
        },
        onSuccess: () => {
            form.value.password = "";
            form.value.password_confirmation = "";
        },
    });
};
</script>
