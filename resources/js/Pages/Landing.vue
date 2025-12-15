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
                    <div class="flex items-center">
                        <img
                            :src="scrolled ? '/images/logo/logo-banner.png' : '/images/logo/logo-banner-white.png'"
                            alt="StoreFlow"
                            class="h-10 w-auto transition-opacity duration-300"
                        />
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
            <!-- Gradient Background with Scroll Animation -->
            <div
                class="absolute inset-0 transition-all duration-700 ease-out"
                :style="{
                    background: `linear-gradient(${135 + gradientPosition * 0.5}deg, rgb(37, 99, 235) ${0 + gradientPosition * 0.2}%, rgb(147, 51, 234) ${50 - gradientPosition * 0.1}%, rgb(107, 33, 168) ${100 - gradientPosition * 0.2}%)`
                }"
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

            <!-- Mascot -->
            <div class="absolute left-0 bottom-24 z-10">
                <img
                    src="/images/logo/mascot.svg"
                    alt="StoreFlow Mascot"
                    class="w-32 h-32 sm:w-48 sm:h-48 lg:w-64 lg:h-64 opacity-90 animate-float"
                />
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
                            :href="route('register')"
                            class="px-8 py-4 bg-white text-blue-600 rounded-lg hover:bg-gray-50 transition-all duration-300 shadow-2xl hover:shadow-3xl font-semibold text-lg transform hover:scale-105"
                        >
                            Get Started For Free
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
                    class="absolute left-1/2 transform -translate-x-1/2 animate-bounce z-20"
                    style="bottom: -4rem"
                >
                    <a
                        href="#showcase"
                        @click.prevent="scrollToSection('showcase')"
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

            <!-- Wave Transition - White wave on gradient background -->
            <div class="absolute bottom-0 left-0 w-full overflow-hidden leading-none">
                <svg class="relative block w-full h-24" viewBox="0 0 1200 120" preserveAspectRatio="none">
                    <path d="M0,0 C150,80 350,0 600,40 C850,80 1050,20 1200,60 L1200,120 L0,120 Z" fill="#ffffff"></path>
                </svg>
            </div>
        </section>

        <!-- Showcase Section -->
        <section id="showcase" class="relative bg-white overflow-hidden">

            <div class="pt-32 pb-24">
                <!-- Customer Storefront Section -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-32">
                    <div class="flex flex-col lg:flex-row items-center gap-12">
                        <!-- Image - Left Side -->
                        <div
                            class="w-full lg:w-1/2 showcase-card group relative"
                            @mouseenter="handleShowcaseHover($event, 'store')"
                            @mousemove="handleShowcaseMove"
                            @mouseleave="handleShowcaseLeave('store')"
                            :style="showcaseStyles.store"
                        >
                            <div
                                class="relative h-[300px] md:h-[400px] lg:h-[500px] rounded-2xl overflow-hidden shadow-2xl border-4 border-gray-200 bg-white transform transition-transform duration-500 group-hover:scale-105"
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
                                    class="absolute top-6 right-6 bg-gradient-to-br from-blue-600 to-purple-600 text-white px-4 py-2 rounded-full shadow-2xl font-semibold text-sm transform transition-all duration-500 group-hover:scale-110"
                                >
                                    Customer
                                </div>
                            </div>
                        </div>

                        <!-- Text - Right Side -->
                        <div class="lg:w-1/2">
                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-6">
                                Customer Storefront
                            </h3>
                            <p class="text-lg text-gray-600 mb-6 leading-relaxed">
                                Give your customers a beautiful, responsive shopping experience that works seamlessly across all devices. Our storefront is designed to boost conversions and make shopping a breeze.
                            </p>
                            <ul class="space-y-4">
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 text-blue-600 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-700">Mobile-first responsive design that looks great on any screen</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 text-blue-600 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-700">Fast checkout process with secure payment integration</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 text-blue-600 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-700">Real-time inventory updates and product search</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Backend Dashboard Section -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col lg:flex-row-reverse items-center gap-12">
                        <!-- Image - Right Side -->
                        <div
                            class="w-full lg:w-1/2 showcase-card group relative"
                            @mouseenter="handleShowcaseHover($event, 'dashboard')"
                            @mousemove="handleShowcaseMove"
                            @mouseleave="handleShowcaseLeave('dashboard')"
                            :style="showcaseStyles.dashboard"
                        >
                            <div
                                class="relative h-[300px] md:h-[400px] lg:h-[500px] rounded-2xl overflow-hidden shadow-2xl border-4 border-gray-200 bg-white transform transition-transform duration-500 group-hover:scale-105"
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
                                    class="absolute top-6 right-6 bg-gradient-to-br from-gray-900 to-gray-700 text-white px-4 py-2 rounded-full shadow-2xl font-semibold text-sm transform transition-all duration-500 group-hover:scale-110"
                                >
                                    Staff
                                </div>
                            </div>
                        </div>

                        <!-- Text - Left Side -->
                        <div class="lg:w-1/2">
                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-6">
                                Backend Dashboard
                            </h3>
                            <p class="text-lg text-gray-600 mb-6 leading-relaxed">
                                Manage your entire operation from a powerful, intuitive dashboard. Track orders, manage inventory, and gain insights—all from one centralized platform.
                            </p>
                            <ul class="space-y-4">
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 text-purple-600 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-700">Real-time order management with instant notifications</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 text-purple-600 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-700">Multi-store support with centralized control</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 text-purple-600 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-700">Comprehensive analytics and reporting tools</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Free Trial CTA Section -->
        <section class="relative py-20 overflow-hidden">
            <!-- Gradient Background -->
            <div class="absolute inset-0 bg-gradient-to-r from-blue-600 via-purple-600 to-pink-500"></div>

            <!-- Animated Background Shapes -->
            <div class="absolute inset-0 overflow-hidden opacity-20">
                <div class="absolute top-0 -left-4 w-72 h-72 bg-white rounded-full mix-blend-multiply filter blur-xl animate-float"></div>
                <div class="absolute top-0 -right-4 w-72 h-72 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl animate-float-delayed"></div>
                <div class="absolute -bottom-8 left-20 w-72 h-72 bg-pink-300 rounded-full mix-blend-multiply filter blur-xl animate-float"></div>
            </div>

            <!-- Content -->
            <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-8 md:p-12 border border-white/20">
                    <h2 class="text-3xl md:text-5xl font-bold text-white mb-4">
                        Cut the bullsh*t. Let's get going.
                    </h2>
                    <p class="text-lg md:text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
                        Full access to all features. Cancel anytime.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                        <a
                            :href="route('register')"
                            class="px-10 py-4 bg-white text-purple-600 rounded-lg hover:bg-gray-50 transition-all duration-300 shadow-2xl hover:shadow-3xl font-bold text-lg transform hover:scale-105"
                        >
                            Start Free Trial
                        </a>
                        <div class="flex items-center gap-2 text-white text-sm">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>It's easy as</span>
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

        <!-- Payment Processing Section -->
        <section id="pricing" class="py-20 bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">
                        Simple, Transparent Pricing
                    </h2>
                    <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                        Get paid faster with industry-leading rates powered by Stripe
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                    <!-- Pricing Card -->
                    <div class="bg-white rounded-2xl shadow-2xl p-8 md:p-12 border-2 border-purple-100">
                        <div class="flex items-center justify-center mb-6">
                            <svg class="w-16 h-16 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M4 4h16a2 2 0 012 2v12a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2zm0 2v12h16V6H4zm2 3h12v2H6V9zm0 4h8v2H6v-2z"/>
                            </svg>
                        </div>
                        <h3 class="text-3xl font-bold text-center text-gray-900 mb-4">
                            Per Transaction
                        </h3>
                        <div class="text-center mb-4">
                            <span class="text-5xl font-bold text-purple-600">2.5%</span>
                            <span class="text-2xl text-gray-600"> + </span>
                            <span class="text-5xl font-bold text-purple-600">$0.30</span>

                        </div>
                            <ul class="space-y-4 mb-8">
                            <li class="flex items-start">
                                <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-700">Same day payouts to your bank account</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-700">Powered by Stripe - industry-leading security</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-700">Other platforms charge 30% per transaction</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-700">Accept all major credit cards & digital wallets</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Information Side -->
                    <div class="space-y-6">
                        <div class="bg-white rounded-xl p-6 shadow-lg border border-purple-100">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Cost breakdown</h4>
                                    <p class="text-gray-600">Stripe takes 1.5% per transaction + $0.30. StoreFlow takes 1.0% per transaction. <br>After your trial, we charge $30 a month for our all services.</br></p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl p-6 shadow-lg border border-purple-100">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Same Day Payouts</h4>
                                    <p class="text-gray-600">Get access to your funds instantly. No waiting periods, no delays.</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl p-6 shadow-lg border border-purple-100">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Secure Processing</h4>
                                    <p class="text-gray-600">Bank-level encryption and PCI compliance through Stripe's trusted infrastructure.</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl p-6 shadow-lg border border-purple-100">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-pink-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Transparent Pricing</h4>
                                    <p class="text-gray-600">What you see is what you pay. No surprises, no fine print.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Trust Badge -->
                <div class="mt-12 text-center">
                    <p class="text-sm text-gray-500 mb-4">Trusted payment processing by</p>
                    <div class="flex justify-center items-center">
                        <div class="px-8 py-4 bg-white rounded-lg shadow-md mb-4">
                            <span class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">Stripe</span>
                        </div>
                    </div>
                    <p class="text-sm text-gray-800 max-w-2xl mx-auto">
                        Why do we use Stripe? We want you to handle your own money, not us.<br>
                        Stripe is a global leader in the financial industry, trusted by millions of businesses for secure and reliable payment processing.
                    </p>
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
                        Join a local today to experience the future of e-commerce management.</br>
                    </p>
                    <a
                        :href="route('register')"
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
                        <div class="flex items-center mb-4">
                            <img
                                src="/images/logo/logo-banner-white.png"
                                alt="StoreFlow"
                                class="h-10 w-auto"
                            />
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

// Gradient animation based on scroll
const gradientPosition = ref(0);

const navItems = [
    { id: "hero", label: "Home" },
    { id: "showcase", label: "Showcase" },
    { id: "features", label: "Features" },
    { id: "about", label: "About" },
    { id: "pricing", label: "Pricing" },
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
        register: "/register",
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
        const offset = 100; // Navigation height + extra spacing
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

    // Update gradient position based on scroll (creates flowing animation)
    const maxScroll = 500; // Maximum scroll distance for full gradient shift
    const scrollPercentage = Math.min(window.scrollY / maxScroll, 1);
    gradientPosition.value = scrollPercentage * 100; // 0 to 100

    // Update active section
    const sections = ["hero", "showcase", "features", "about", "pricing", "cta"];
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
