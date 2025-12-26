<template>
    <div class="min-h-screen flex flex-col lg:flex-row overflow-hidden">
        <!-- Left Section - Tips -->
        <div
            class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-blue-600 via-purple-600 to-purple-800 relative overflow-hidden"
        >
            <!-- Animated Background Shapes -->
            <div class="absolute inset-0 overflow-hidden">
                <div
                    class="absolute top-20 left-10 w-72 h-72 bg-white opacity-10 rounded-full blur-3xl animate-float"
                ></div>
                <div
                    class="absolute bottom-20 right-10 w-96 h-96 bg-blue-300 opacity-10 rounded-full blur-3xl animate-float-delayed"
                ></div>
            </div>

            <!-- Tips Content -->
            <div
                class="relative z-10 flex flex-col justify-center px-16 w-full"
            >
                <div class="mb-8">
                    <img
                        src="/images/logo/logo-banner-white.png"
                        alt="StoreFlow"
                        class="h-12 mb-6"
                    />
                    <h2 class="text-4xl font-bold text-white mb-4">
                        Welcome Back!
                    </h2>
                    <p class="text-blue-100 text-lg">
                        Here's a tip to make the most of your store
                    </p>
                </div>

                <!-- Rotating Tip -->
                <div
                    class="bg-white/10 backdrop-blur-md rounded-2xl p-8 border border-white/20 shadow-2xl"
                >
                    <div class="flex items-start space-x-4">
                        <div
                            class="flex-shrink-0 w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center"
                        >
                            <svg
                                class="w-6 h-6 text-white"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    :d="currentTip.icon"
                                />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-semibold text-white mb-2">
                                {{ currentTip.title }}
                            </h3>
                            <p class="text-blue-100 leading-relaxed">
                                {{ currentTip.description }}
                            </p>
                        </div>
                    </div>

                    <!-- Tip Counter -->
                    <div class="mt-6 flex items-center justify-between">
                        <div class="flex space-x-2">
                            <div
                                v-for="(tip, index) in tips"
                                :key="index"
                                class="h-1.5 rounded-full transition-all duration-300"
                                :class="
                                    index === currentTipIndex
                                        ? 'w-8 bg-white'
                                        : 'w-1.5 bg-white/40'
                                "
                            ></div>
                        </div>
                        <p class="text-blue-100 text-sm">
                            Tip {{ currentTipIndex + 1 }} of {{ tips.length }}
                        </p>
                    </div>
                </div>

                <!-- Feature Highlights -->
                <div class="mt-12 grid grid-cols-2 gap-4">
                    <div class="flex items-center space-x-3">
                        <svg
                            class="w-5 h-5 text-blue-200"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        <span class="text-blue-100 text-sm"
                            >Real-time Updates</span
                        >
                    </div>
                    <div class="flex items-center space-x-3">
                        <svg
                            class="w-5 h-5 text-blue-200"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        <span class="text-blue-100 text-sm"
                            >Multi-store Support</span
                        >
                    </div>
                    <div class="flex items-center space-x-3">
                        <svg
                            class="w-5 h-5 text-blue-200"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        <span class="text-blue-100 text-sm"
                            >Advanced Analytics</span
                        >
                    </div>
                    <div class="flex items-center space-x-3">
                        <svg
                            class="w-5 h-5 text-blue-200"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        <span class="text-blue-100 text-sm"
                            >Secure Payments</span
                        >
                    </div>
                </div>

                <!-- Wombat Mascot - Desktop Middle -->
                <div class="absolute top-1/2 -right-[10rem] z-20" style="transform: translateY(-15%);">
                    <img
                        src="/images/logo/storeflow_wombat_thumbsup.png"
                        alt="StoreFlow Mascot"
                        class="w-[40rem] h-[40rem] opacity-90"
                    />
                </div>
            </div>
        </div>

        <!-- Right Section - Login Form -->
        <div
            class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-gradient-to-br from-purple-50 via-pink-50 to-blue-50 relative overflow-hidden"
        >
            <!-- Animated Background Shapes -->
            <div class="absolute inset-0 overflow-hidden">
                <div
                    class="absolute top-1/4 right-1/4 w-96 h-96 bg-purple-400 opacity-20 rounded-full blur-3xl animate-float-slow"
                ></div>
                <div
                    class="absolute bottom-1/3 left-1/4 w-80 h-80 bg-blue-400 opacity-20 rounded-full blur-3xl animate-float-reverse"
                ></div>
                <div
                    class="absolute top-1/2 left-1/2 w-72 h-72 bg-pink-400 opacity-15 rounded-full blur-3xl animate-float-delayed"
                ></div>
            </div>

            <!-- Background Pattern -->
            <div
                class="absolute inset-0 opacity-5"
                style="
                    background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%239C92AC\' fill-opacity=\'0.4\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');
                "
            ></div>

            <!-- Login Form Card with Glass Effect -->
            <div
                class="relative w-full max-w-md bg-white/70 backdrop-blur-xl rounded-3xl shadow-2xl p-8 border border-white/20"
            >
                <!-- Mobile Logo (visible on mobile only) -->
                <div class="lg:hidden mb-6 text-center">
                    <img
                        src="/images/logo/logo-banner.png"
                        alt="StoreFlow"
                        class="h-10 mx-auto mb-4"
                    />
                </div>

                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">
                        Sign In
                    </h1>
                    <p class="text-gray-600">
                        Welcome back! Please sign in to continue
                    </p>
                </div>

                <!-- Error Messages -->
                <div
                    v-if="form.errors && Object.keys(form.errors).length > 0"
                    class="mb-6 p-4 bg-red-50/80 backdrop-blur-sm border border-red-200 rounded-xl"
                >
                    <div class="flex items-start">
                        <svg
                            class="w-5 h-5 text-red-600 mr-3 flex-shrink-0 mt-0.5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            ></path>
                        </svg>
                        <div>
                            <p
                                v-for="(error, field) in form.errors"
                                :key="field"
                                class="text-sm text-red-700"
                            >
                                {{ error }}
                            </p>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Username -->
                    <div>
                        <label
                            for="username"
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Username or Email
                        </label>
                        <input
                            id="username"
                            v-model="form.username"
                            type="text"
                            required
                            autocomplete="username"
                            autofocus
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-white/50 backdrop-blur-sm focus:ring-2 focus:ring-purple-600 focus:border-transparent transition-all placeholder-gray-400"
                            :class="{
                                'border-red-500': form.errors.username,
                            }"
                            placeholder="Enter your username"
                        />
                    </div>

                    <!-- Password -->
                    <div>
                        <label
                            for="password"
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Password
                        </label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            required
                            autocomplete="current-password"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-white/50 backdrop-blur-sm focus:ring-2 focus:ring-purple-600 focus:border-transparent transition-all placeholder-gray-400"
                            :class="{
                                'border-red-500': form.errors.password,
                            }"
                            placeholder="Enter your password"
                        />
                    </div>

                    <!-- Turnstile Widget -->
                    <div>
                        <TurnstileWidget
                            ref="turnstileRef"
                            :site-key="page.props.turnstile_site_key"
                            v-model="form.turnstile_token"
                            theme="light"
                        />
                        <p v-if="form.errors.turnstile_token" class="mt-1 text-sm text-red-600 text-center">
                            {{ form.errors.turnstile_token }}
                        </p>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input
                                v-model="form.remember"
                                type="checkbox"
                                class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                            />
                            <span class="ml-2 text-sm text-gray-600"
                                >Remember me</span
                            >
                        </label>
                        <a
                            :href="route('password.request')"
                            class="text-sm text-purple-600 hover:text-purple-700 font-medium"
                        >
                            Forgot password?
                        </a>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl font-semibold hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-lg"
                    >
                        <span v-if="!form.processing">Sign In</span>
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
                            Signing In...
                        </span>
                    </button>
                </form>

                <!-- Register Link -->
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        Don't have an account?
                        <a
                            :href="route('register')"
                            class="text-purple-600 hover:text-purple-700 font-medium"
                        >
                            Sign up for free
                        </a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Wombat Mascot - Mobile Bottom Section -->
        <div class="lg:hidden w-full">
            <img
                src="/images/logo/storeflow_wombat_thumbsup.png"
                alt="StoreFlow Mascot"
                class="w-full h-auto opacity-90"
            />
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import TurnstileWidget from "@/Components/TurnstileWidget.vue";

const page = usePage();

const form = useForm({
    username: "",
    password: "",
    remember: false,
    turnstile_token: null,
});

const turnstileRef = ref(null);

// Tips that rotate on page refresh
const tips = [
    {
        title: "Shipping Zones Made Easy",
        description:
            "Set up custom shipping zones and rates based on location. Define unlimited zones with different pricing for domestic and international customers.",
        icon: "M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z",
    },
    {
        title: "Drag & Drop Product Management",
        description:
            "Effortlessly organize your products and categories with intuitive drag-and-drop functionality. Reorder items in seconds on your operations dashboard.",
        icon: "M4 6h16M4 12h16M4 18h16",
    },
    {
        title: "Staff Permissions & Roles",
        description:
            "Control exactly what your team can access. Assign different permission levels to staff members, from basic cashier access to full management rights.",
        icon: "M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z",
    },
    {
        title: "Real-Time Order Notifications",
        description:
            "Never miss an order! Get instant notifications on your dashboard when new orders arrive. Stay connected with live order status updates.",
        icon: "M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9",
    },
    {
        title: "Multiple Store Management",
        description:
            "Manage all your stores from one dashboard. Switch between stores seamlessly and maintain separate inventories, settings, and branding for each location.",
        icon: "M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4",
    },
    {
        title: "Theme Customization",
        description:
            "Choose from beautiful pre-built themes or customize colors and layouts to match your brand. Make your storefront uniquely yours with just a few clicks.",
        icon: "M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01",
    },
    {
        title: "Advanced Product Categories",
        description:
            "Organize products with nested categories and tags. Customers can easily browse and filter products, improving their shopping experience.",
        icon: "M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z",
    },
    {
        title: "Smart Order Management",
        description:
            "Track orders from placement to delivery. Bulk update order statuses, generate shipping labels, and manage fulfillment all from your dashboard.",
        icon: "M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01",
    },
];

// Select a random tip on component mount
const currentTipIndex = ref(0);
const currentTip = ref(tips[0]);

onMounted(() => {
    // Pick a random tip
    currentTipIndex.value = Math.floor(Math.random() * tips.length);
    currentTip.value = tips[currentTipIndex.value];
});

const submit = () => {
    // Validate Turnstile token
    if (!turnstileRef.value?.isValid()) {
        form.errors.turnstile_token = 'Please complete the security verification';
        return;
    }

    form.post(route("login"), {
        onFinish: () => {
            form.reset("password");
            turnstileRef.value?.reset();
        },
        onError: () => {
            turnstileRef.value?.reset();
        },
    });
};

const route = (name) => {
    const routes = {
        login: "/login",
        register: "/register",
        "password.request": "/forgot-password",
    };
    return routes[name] || "/";
};
</script>

<style scoped>
@keyframes float {
    0%,
    100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-20px);
    }
}

@keyframes float-delayed {
    0%,
    100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-30px);
    }
}

@keyframes float-slow {
    0%,
    100% {
        transform: translate(0px, 0px);
    }
    33% {
        transform: translate(30px, -30px);
    }
    66% {
        transform: translate(-20px, 20px);
    }
}

@keyframes float-reverse {
    0%,
    100% {
        transform: translate(0px, 0px);
    }
    33% {
        transform: translate(-30px, 30px);
    }
    66% {
        transform: translate(20px, -20px);
    }
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

.animate-float-reverse {
    animation: float-reverse 10s ease-in-out infinite;
    animation-delay: 2s;
}
</style>
