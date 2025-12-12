<template>
    <div class="min-h-screen bg-white overflow-x-hidden">
        <!-- Navigation -->
        <nav
            class="fixed top-0 left-0 right-0 z-50 transition-all duration-300"
            :class="scrolled ? 'bg-white shadow-md' : 'bg-transparent'"
        >
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo -->
                    <div class="flex items-center space-x-2">
                        <img
                            src="/logo.png"
                            alt="StoreFlow"
                            class="h-8 w-auto"
                        />
                        <span
                            class="text-xl font-bold transition-colors"
                            :class="scrolled ? 'text-gray-900' : 'text-white'"
                        >
                            StoreFlow
                        </span>
                    </div>

                    <!-- Desktop Navigation -->
                    <div class="hidden md:flex space-x-8">
                        <a
                            v-for="item in navItems"
                            :key="item.id"
                            :href="`#${item.id}`"
                            @click.prevent="scrollToSection(item.id)"
                            class="text-sm font-medium transition-colors hover:text-blue-600"
                            :class="[
                                scrolled ? 'text-gray-700' : 'text-white',
                                activeSection === item.id
                                    ? 'text-blue-600'
                                    : '',
                            ]"
                        >
                            {{ item.label }}
                        </a>
                    </div>

                    <!-- Login Button -->
                    <div class="flex items-center space-x-4">
                        <a
                            :href="route('login')"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all duration-300 shadow-lg hover:shadow-xl font-medium text-sm"
                        >
                            Login
                        </a>

                        <!-- Mobile Menu Button -->
                        <button
                            @click="mobileMenuOpen = !mobileMenuOpen"
                            class="md:hidden p-2 rounded-lg"
                            :class="scrolled ? 'text-gray-900' : 'text-white'"
                        >
                            <svg
                                class="w-6 h-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    v-if="!mobileMenuOpen"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"
                                />
                                <path
                                    v-else
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Mobile Menu -->
                <transition
                    enter-active-class="transition-all duration-300 ease-out"
                    enter-from-class="opacity-0 transform -translate-y-4"
                    enter-to-class="opacity-100 transform translate-y-0"
                    leave-active-class="transition-all duration-200 ease-in"
                    leave-from-class="opacity-100 transform translate-y-0"
                    leave-to-class="opacity-0 transform -translate-y-4"
                >
                    <div
                        v-if="mobileMenuOpen"
                        class="md:hidden py-4 space-y-2 bg-white shadow-lg rounded-b-lg"
                    >
                        <a
                            v-for="item in navItems"
                            :key="item.id"
                            :href="`#${item.id}`"
                            @click.prevent="
                                scrollToSection(item.id);
                                mobileMenuOpen = false;
                            "
                            class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors font-medium"
                        >
                            {{ item.label }}
                        </a>
                    </div>
                </transition>
            </div>
        </nav>

        <!-- Hero Section -->
        <section
            id="hero"
            class="relative min-h-screen flex items-center justify-center overflow-hidden"
        >
            <!-- Gradient Background -->
            <div
                class="absolute inset-0 bg-gradient-to-br from-blue-600 via-purple-600 to-purple-800"
            ></div>

            <!-- Animated Shapes -->
            <div class="absolute inset-0 overflow-hidden">
                <div
                    class="absolute top-20 left-4 sm:left-10 w-48 sm:w-72 h-48 sm:h-72 bg-white opacity-10 rounded-full blur-3xl animate-float"
                ></div>
                <div
                    class="absolute bottom-20 right-4 sm:right-10 w-64 sm:w-96 h-64 sm:h-96 bg-blue-300 opacity-10 rounded-full blur-3xl animate-float-delayed"
                ></div>
            </div>

            <!-- Content -->
            <div
                class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center"
            >
                <div
                    class="opacity-0 animate-fade-in-up"
                    :class="{ 'animation-started': heroVisible }"
                >
                    <h1
                        class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-bold text-white mb-6 leading-tight"
                    >
                        Transform Your Business<br />
                        <span
                            class="text-transparent bg-clip-text bg-gradient-to-r from-blue-200 to-purple-200"
                        >
                            Into Digital Success
                        </span>
                    </h1>
                    <p
                        class="text-xl sm:text-2xl text-blue-100 mb-10 max-w-3xl mx-auto"
                    >
                        StoreFlow is a power full e-commerce platform designed for
                        merchants and the customers.
                        Seamlessly manage your active orders and products.
                        Remove the annoyance of web-design, we will cover that. Just upload and sell.
                    </p>
                    <div
                        class="flex flex-col sm:flex-row gap-4 justify-center items-center"
                    >
                        <a
                            :href="route('login')"
                            class="px-8 py-4 bg-white text-blue-600 rounded-lg hover:bg-gray-50 transition-all duration-300 shadow-2xl hover:shadow-3xl font-semibold text-lg transform hover:scale-105"
                        >
                            Get Started Free
                        </a>
                        <a
                            href="#features"
                            @click.prevent="scrollToSection('features')"
                            class="px-8 py-4 bg-transparent text-white border-2 border-white rounded-lg hover:bg-white hover:text-blue-600 transition-all duration-300 font-semibold text-lg transform hover:scale-105"
                        >
                            Learn More
                        </a>
                    </div>
                </div>

                <!-- Scroll Indicator -->
                <div
                    class="absolute bottom-10 left-1/2 transform -translate-x-1/2 animate-bounce"
                >
                    <a
                        href="#features"
                        @click.prevent="scrollToSection('features')"
                        class="text-white opacity-75 hover:opacity-100 transition-opacity"
                    >
                        <svg
                            class="w-6 h-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 14l-7 7m0 0l-7-7m7 7V3"
                            />
                        </svg>
                    </a>
                </div>
            </div>
        </section>

        <!-- Showcase Section -->
        <section id="showcase" class="py-24 bg-white overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">
                        Experience the Platform
                    </h2>
                    <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                        See how StoreFlow transforms both your customer
                        experience and business operations
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
                    <!-- Customer Storefront Showcase -->
                    <div
                        class="showcase-card group relative"
                        @mouseenter="handleShowcaseHover($event, 'store')"
                        @mousemove="handleShowcaseMove"
                        @mouseleave="handleShowcaseLeave('store')"
                        :style="showcaseStyles.store"
                    >
                        <div
                            class="relative h-[500px] rounded-2xl overflow-hidden shadow-2xl border-4 border-gray-200 bg-white"
                        >
                            <!-- Actual Screenshot -->
                            <img
                                src="/images/showcase-customer.png"
                                alt="Customer Storefront"
                                class="absolute inset-0 w-full h-full object-cover object-top"
                            />

                            <!-- Hover Glow Effect -->
                            <div
                                class="absolute inset-0 bg-gradient-to-tr from-blue-500/30 to-purple-500/30 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"
                            ></div>

                            <!-- Floating Badge -->
                            <div
                                class="absolute top-6 right-6 bg-gradient-to-br from-blue-600 to-purple-600 text-white px-4 py-2 rounded-full shadow-2xl font-semibold text-sm transform transition-all duration-500 group-hover:scale-110 animate-pulse-subtle"
                            >
                                Customer
                            </div>
                        </div>

                        <!-- Label -->
                        <div class="mt-6 text-center">
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">
                                Customer Storefront
                            </h3>
                            <p class="text-gray-600">
                                Responsive, clean shopping experience tailored
                                to boost your sales
                            </p>
                        </div>
                    </div>

                    <!-- Backend Dashboard Showcase -->
                    <div
                        class="showcase-card group relative"
                        @mouseenter="handleShowcaseHover($event, 'dashboard')"
                        @mousemove="handleShowcaseMove"
                        @mouseleave="handleShowcaseLeave('dashboard')"
                        :style="showcaseStyles.dashboard"
                    >
                        <div
                            class="relative h-[500px] rounded-2xl overflow-hidden shadow-2xl border-4 border-gray-200 bg-white"
                        >
                            <!-- Actual Screenshot -->
                            <img
                                src="/images/showcase-dashboard.png"
                                alt="Backend Dashboard"
                                class="absolute inset-0 w-full h-full object-cover object-top"
                            />

                            <!-- Hover Glow Effect -->
                            <div
                                class="absolute inset-0 bg-gradient-to-tr from-gray-900/20 to-blue-500/30 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"
                            ></div>

                            <!-- Floating Badge -->
                            <div
                                class="absolute top-6 right-6 bg-gradient-to-br from-gray-900 to-gray-700 text-white px-4 py-2 rounded-full shadow-2xl font-semibold text-sm transform transition-all duration-500 group-hover:scale-110 animate-pulse-subtle"
                            >
                                Staff
                            </div>
                        </div>

                        <!-- Label -->
                        <div class="mt-6 text-center">
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">
                                Backend Dashboard
                            </h3>
                            <p class="text-gray-600">
                                Order management, product listings and
                                multi-store support settings at your finger tips
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-20 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">
                        Everything You Need to Succeed
                    </h2>
                    <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                        Built-in features to streamline your operations and
                        delight your customers
                    </p>
                </div>

                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"
                >
                    <div
                        v-for="(feature, index) in features"
                        :key="index"
                        class="bg-white rounded-xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 opacity-0 animate-fade-in-up"
                        :style="{ animationDelay: `${index * 100}ms` }"
                        :class="{ 'animation-started': featuresVisible }"
                    >
                        <div
                            class="w-14 h-14 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center mb-6 shadow-lg"
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
                                    :d="feature.icon"
                                />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">
                            {{ feature.title }}
                        </h3>
                        <p class="text-gray-600 leading-relaxed">
                            {{ feature.description }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section id="about" class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div
                    class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center"
                >
                    <div
                        class="opacity-0 animate-fade-in-left"
                        :class="{ 'animation-started': aboutVisible }"
                    >
                        <h2 class="text-4xl font-bold text-gray-900 mb-6">
                            Built for Modern Commerce
                        </h2>
                        <p class="text-lg text-gray-600 mb-6 leading-relaxed">
                            StoreFlow is more than just a platform, it's your
                            partner in digital transformation. We understand the
                            challenges of running an online business, and we've
                            built a solution that grows with you.
                        </p>
                        <p class="text-lg text-gray-600 mb-8 leading-relaxed">
                            From seamless product management to real-time order
                            tracking, every feature is designed to save you time
                            and help you focus on what matters most: your
                            customers.
                        </p>
                        <div class="grid grid-cols-2 gap-6">
                            <div
                                class="text-center p-4 bg-gradient-to-br from-blue-50 to-purple-50 rounded-lg"
                            >
                                <div
                                    class="text-3xl font-bold text-blue-600 mb-2"
                                >
                                    99.9%
                                </div>
                                <div class="text-sm text-gray-600">
                                    Cloud provided SLA uptime
                                </div>
                            </div>
                            <div
                                class="text-center p-4 bg-gradient-to-br from-blue-50 to-purple-50 rounded-lg"
                            >
                                <div
                                    class="text-3xl font-bold text-purple-600 mb-2"
                                >
                                    Local support
                                </div>
                                <div class="text-sm text-gray-600">
                                    in Victoria
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="opacity-0 animate-fade-in-right"
                        :class="{ 'animation-started': aboutVisible }"
                    >
                        <!-- Illustration Placeholder -->
                        <div class="relative">
                            <div
                                class="aspect-square bg-gradient-to-br from-blue-100 to-purple-100 rounded-2xl shadow-2xl overflow-hidden"
                            >
                                <div
                                    class="absolute inset-0 flex items-center justify-center"
                                >
                                    <div class="text-center p-8">
                                        <svg
                                            class="w-32 h-32 mx-auto text-blue-600 opacity-50"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1.5"
                                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                                            />
                                        </svg>
                                        <p
                                            class="text-xl font-semibold text-gray-700 mt-4"
                                        >
                                            Secure & Reliable<br />
                                            Productive & Efficient<br />
                                            Fast & Optimized<br />
                                            Customer & Staff Driven
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <!-- Floating Elements -->
                            <div
                                class="absolute top-0 right-0 w-16 sm:w-24 h-16 sm:h-24 bg-blue-400 rounded-full opacity-20 blur-xl animate-pulse"
                            ></div>
                            <div
                                class="absolute bottom-0 left-0 w-20 sm:w-32 h-20 sm:h-32 bg-purple-400 rounded-full opacity-20 blur-xl animate-pulse"
                                style="animation-delay: 1s"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Call to Action Section -->
        <section
            id="cta"
            class="py-20 bg-gradient-to-br from-blue-600 via-purple-600 to-purple-800 relative overflow-hidden"
        >
            <!-- Background Pattern -->
            <div class="absolute inset-0 opacity-10">
                <div
                    class="absolute top-0 left-0 w-full h-full"
                    style="
                        background-image: radial-gradient(
                            circle at 2px 2px,
                            white 1px,
                            transparent 0
                        );
                        background-size: 40px 40px;
                    "
                ></div>
            </div>

            <div
                class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center"
            >
                <div
                    class="opacity-0 animate-fade-in-up"
                    :class="{ 'animation-started': ctaVisible }"
                >
                    <h2 class="text-4xl sm:text-5xl font-bold text-white mb-6">
                        Ready to Transform Your Business?
                    </h2>
                    <p class="text-xl text-blue-100 mb-10 max-w-2xl mx-auto">
                        Join a local today to experiance the future of e-commerce management.</br>
                    </p>
                    <a
                        :href="route('login')"
                        class="inline-block px-10 py-4 bg-white text-blue-600 rounded-lg hover:bg-gray-50 transition-all duration-300 shadow-2xl hover:shadow-3xl font-bold text-lg transform hover:scale-105"
                    >
                        Start Free Trial
                    </a>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                    <div class="col-span-1 md:col-span-2">
                        <div class="flex items-center space-x-2 mb-4">
                            <img
                                src="/logo.png"
                                alt="StoreFlow"
                                class="h-8 w-auto"
                            />
                            <span class="text-xl font-bold">StoreFlow</span>
                        </div>
                        <p class="text-gray-400 mb-4">
                            Empowering merchants with cutting-edge e-commerce
                            solutions.
                        </p>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-4">Product</h4>
                        <ul class="space-y-2 text-gray-400">
                            <li>
                                <a
                                    href="#features"
                                    @click.prevent="scrollToSection('features')"
                                    class="hover:text-white transition-colors"
                                    >Features</a
                                >
                            </li>
                            <li>
                                <a
                                    href="#about"
                                    @click.prevent="scrollToSection('about')"
                                    class="hover:text-white transition-colors"
                                    >About</a
                                >
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-4">Company</h4>
                        <ul class="space-y-2 text-gray-400">
                            <li>
                                <a
                                    :href="route('login')"
                                    class="hover:text-white transition-colors"
                                    >Login</a
                                >
                            </li>
                        </ul>
                    </div>
                </div>
                <div
                    class="border-t border-gray-800 pt-8 text-center text-gray-400"
                >
                    <p>
                        &copy; {{ new Date().getFullYear() }} StoreFlow. All
                        rights reserved.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { router } from "@inertiajs/vue3";

const scrolled = ref(false);
const activeSection = ref("hero");
const mobileMenuOpen = ref(false);

// Visibility states for animations
const heroVisible = ref(false);
const featuresVisible = ref(false);
const aboutVisible = ref(false);
const ctaVisible = ref(false);

// Showcase 3D tilt effect state
const showcaseStyles = ref({
    store: {},
    dashboard: {},
});
const showcaseHovered = ref({
    store: false,
    dashboard: false,
});

const navItems = [
    { id: "hero", label: "Home" },
    { id: "features", label: "Features" },
    { id: "about", label: "About" },
    { id: "cta", label: "Get Started" },
];

const features = [
    {
        title: "Product Management",
        description:
            "Track analytics, manage addons or varients and easy migration.",
        icon: "M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4",
    },
    {
        title: "Order Processing",
        description:
            "Streamline your fulfillment with real-time order tracking, automated notifications, and shipping integration.",
        icon: "M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4",
    },
    {
        title: "Customer Portal",
        description:
            "Give your customers a seamless shopping experience with account management, order history, and tracking.",
        icon: "M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z",
    },
    {
        title: "Analytics Dashboard",
        description:
            "Make data-driven decisions with comprehensive insights into sales, customer behavior, and performance metrics.",
        icon: "M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z",
    },
    {
        title: "Multi-Store Support",
        description:
            "Manage multiple storefronts from a single dashboard. Perfect for businesses with multiple locations or brands.",
        icon: "M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4",
    },
    {
        title: "Payment Integration",
        description:
            "Accept payments securely with Stripe integration. Support for multiple payment methods and currencies.",
        icon: "M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z",
    },
];

// Route helper (Inertia route function)
const route = (name) => {
    const routes = {
        login: "/login",
    };
    return routes[name] || "/";
};

// Showcase 3D tilt handlers
const handleShowcaseHover = (e, type) => {
    showcaseHovered.value[type] = true;
};

const handleShowcaseMove = (e) => {
    const card = e.currentTarget;
    const rect = card.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    const centerX = rect.width / 2;
    const centerY = rect.height / 2;

    const rotateX = ((y - centerY) / centerY) * -10;
    const rotateY = ((x - centerX) / centerX) * 10;

    // Determine which card is being hovered by checking image src
    const img = card.querySelector("img");
    const cardType =
        img && img.src.includes("customer") ? "store" : "dashboard";

    showcaseStyles.value[cardType] = {
        transform: `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.02, 1.02, 1.02)`,
        transition: "transform 0.1s ease-out",
    };
};

const handleShowcaseLeave = (type) => {
    showcaseHovered.value[type] = false;
    showcaseStyles.value[type] = {
        transform:
            "perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)",
        transition: "transform 0.5s ease-out",
    };
};

// Smooth scroll to section
const scrollToSection = (sectionId) => {
    const element = document.getElementById(sectionId);
    if (element) {
        const offset = 64; // Navigation height
        const elementPosition =
            element.getBoundingClientRect().top + window.pageYOffset;
        const offsetPosition = elementPosition - offset;

        window.scrollTo({
            top: offsetPosition,
            behavior: "smooth",
        });
    }
};

// Handle scroll events
const handleScroll = () => {
    // Update scrolled state for navbar
    scrolled.value = window.scrollY > 50;

    // Update active section
    const sections = ["hero", "features", "about", "cta"];
    const scrollPosition = window.scrollY + 100;

    for (const sectionId of sections) {
        const section = document.getElementById(sectionId);
        if (section) {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.offsetHeight;

            if (
                scrollPosition >= sectionTop &&
                scrollPosition < sectionTop + sectionHeight
            ) {
                activeSection.value = sectionId;
                break;
            }
        }
    }

    // Trigger animations when sections come into view
    const triggerAnimation = (elementId, visibilityRef) => {
        const element = document.getElementById(elementId);
        if (element) {
            const rect = element.getBoundingClientRect();
            const isVisible = rect.top < window.innerHeight * 0.75;
            if (isVisible && !visibilityRef.value) {
                visibilityRef.value = true;
            }
        }
    };

    triggerAnimation("hero", heroVisible);
    triggerAnimation("features", featuresVisible);
    triggerAnimation("about", aboutVisible);
    triggerAnimation("cta", ctaVisible);
};

onMounted(() => {
    window.addEventListener("scroll", handleScroll);
    handleScroll(); // Initial check

    // Trigger hero animation immediately
    setTimeout(() => {
        heroVisible.value = true;
    }, 100);
});

onUnmounted(() => {
    window.removeEventListener("scroll", handleScroll);
});
</script>

<style scoped>
/* Smooth scrolling for the entire page */
html {
    scroll-behavior: smooth;
}

/* Animation Keyframes */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInLeft {
    from {
        opacity: 0;
        transform: translateX(-30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes fadeInRight {
    from {
        opacity: 0;
        transform: translateX(30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes float {
    0%,
    100% {
        transform: translateY(0) translateX(0);
    }
    50% {
        transform: translateY(-20px) translateX(20px);
    }
}

/* Animation Classes */
.animate-fade-in-up {
    animation: fadeInUp 0.8s ease-out forwards;
    animation-play-state: paused;
}

.animate-fade-in-left {
    animation: fadeInLeft 0.8s ease-out forwards;
    animation-play-state: paused;
}

.animate-fade-in-right {
    animation: fadeInRight 0.8s ease-out forwards;
    animation-play-state: paused;
}

.animation-started {
    animation-play-state: running !important;
}

.animate-float {
    animation: float 6s ease-in-out infinite;
}

.animate-float-delayed {
    animation: float 6s ease-in-out infinite;
    animation-delay: 3s;
}

.animate-pulse-subtle {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%,
    100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
}

/* Showcase Section Animations */
@keyframes bar-grow {
    from {
        transform: scaleY(0);
        transform-origin: bottom;
    }
    to {
        transform: scaleY(1);
        transform-origin: bottom;
    }
}

@keyframes bounce-subtle {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-5px);
    }
}

.animate-bar-grow {
    animation: bar-grow 0.8s ease-out forwards;
}

.animate-bounce-subtle {
    animation: bounce-subtle 2s ease-in-out infinite;
}

.animate-pulse-subtle {
    animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* 3D Showcase Cards */
.showcase-card {
    transform-style: preserve-3d;
    will-change: transform;
    cursor: pointer;
}

/* Custom Shadows */
.shadow-3xl {
    box-shadow: 0 35px 60px -15px rgba(0, 0, 0, 0.3);
}
</style>
