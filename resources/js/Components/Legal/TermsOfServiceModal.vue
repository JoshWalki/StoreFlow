<template>
    <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto" @click="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black opacity-50"></div>

            <!-- Modal -->
            <div
                @click.stop
                class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full p-8 max-h-[90vh] overflow-y-auto"
            >
                <!-- Header -->
                <div class="flex justify-between items-start mb-6">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-blue-50">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900">
                            Terms of Service
                        </h3>
                    </div>
                    <button
                        @click="$emit('close')"
                        class="p-2 rounded-lg hover:bg-gray-100 text-gray-500 transition-colors"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Content -->
                <div class="space-y-6 text-gray-700">
                    <p class="text-sm text-gray-600">
                        <strong>Last Updated:</strong> {{ lastUpdated }}
                    </p>

                    <!-- Introduction -->
                    <div class="p-4 rounded-lg bg-blue-50 border border-blue-200">
                        <p class="text-sm text-gray-700">
                            By accessing or using our service, you agree to be bound by these Terms of Service. Please read them carefully.
                        </p>
                    </div>

                    <!-- Acceptance of Terms -->
                    <div>
                        <h4 class="text-lg font-semibold mb-3 flex items-center gap-2 text-gray-900">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Acceptance of Terms
                        </h4>
                        <div class="ml-7 text-sm">
                            <p>By creating an account or using our service, you acknowledge that you have read, understood, and agree to be bound by these Terms of Service and our Privacy Policy. If you do not agree to these terms, please do not use our service.</p>
                        </div>
                    </div>

                    <!-- Account Registration -->
                    <div>
                        <h4 class="text-lg font-semibold mb-3 flex items-center gap-2 text-gray-900">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Account Registration & Security
                        </h4>
                        <div class="ml-7 space-y-2 text-sm">
                            <p>• You must provide accurate and complete information when creating an account.</p>
                            <p>• You are responsible for maintaining the confidentiality of your account credentials.</p>
                            <p>• You must be at least 13 years old to create an account.</p>
                            <p>• You are responsible for all activities that occur under your account.</p>
                            <p>• You must notify us immediately of any unauthorized use of your account.</p>
                        </div>
                    </div>

                    <!-- Use of Service -->
                    <div>
                        <h4 class="text-lg font-semibold mb-3 flex items-center gap-2 text-gray-900">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                            </svg>
                            Acceptable Use
                        </h4>
                        <div class="ml-7 space-y-2 text-sm">
                            <p>You agree NOT to:</p>
                            <p>• Violate any laws or regulations</p>
                            <p>• Infringe on intellectual property rights</p>
                            <p>• Transmit malicious code or viruses</p>
                            <p>• Attempt to gain unauthorized access to systems</p>
                            <p>• Harass, abuse, or harm other users</p>
                            <p>• Use the service for fraudulent purposes</p>
                            <p>• Scrape or harvest data without permission</p>
                            <p>• Interfere with the proper functioning of the service</p>
                        </div>
                    </div>

                    <!-- Orders and Payments -->
                    <div>
                        <h4 class="text-lg font-semibold mb-3 flex items-center gap-2 text-gray-900">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Orders, Payments, and Refunds
                        </h4>
                        <div class="ml-7 space-y-2 text-sm">
                            <p>• All orders are subject to acceptance and availability.</p>
                            <p>• Prices are subject to change without notice.</p>
                            <p>• Payment must be received before order processing.</p>
                            <p>• We accept various payment methods as indicated at checkout.</p>
                            <p>• Refund policies vary by merchant and are subject to their terms.</p>
                            <p>• Refunds are processed within 5-10 business days when approved.</p>
                        </div>
                    </div>

                    <!-- Intellectual Property -->
                    <div>
                        <h4 class="text-lg font-semibold mb-3 flex items-center gap-2 text-gray-900">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            Intellectual Property
                        </h4>
                        <div class="ml-7 space-y-2 text-sm">
                            <p>• All content on this service is protected by copyright and trademark laws.</p>
                            <p>• You may not copy, modify, distribute, or reproduce any content without permission.</p>
                            <p>• Trademarks and logos are the property of their respective owners.</p>
                            <p>• User-generated content remains your property, but you grant us a license to use it.</p>
                        </div>
                    </div>

                    <!-- Disclaimers -->
                    <div>
                        <h4 class="text-lg font-semibold mb-3 flex items-center gap-2 text-gray-900">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            Disclaimers and Limitation of Liability
                        </h4>
                        <div class="ml-7 space-y-3 text-sm">
                            <p><strong>Service "As Is":</strong> The service is provided "as is" without warranties of any kind, express or implied.</p>
                            <p><strong>No Guarantee:</strong> We do not guarantee that the service will be uninterrupted, secure, or error-free.</p>
                            <p><strong>Limited Liability:</strong> We are not liable for indirect, incidental, special, consequential, or punitive damages.</p>
                            <p><strong>Maximum Liability:</strong> Our total liability shall not exceed the amount you paid for the service in the past 12 months.</p>
                        </div>
                    </div>

                    <!-- Termination -->
                    <div>
                        <h4 class="text-lg font-semibold mb-3 flex items-center gap-2 text-gray-900">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                            Account Termination
                        </h4>
                        <div class="ml-7 space-y-2 text-sm">
                            <p>• We reserve the right to suspend or terminate your account for violations of these terms.</p>
                            <p>• You may terminate your account at any time by contacting customer support.</p>
                            <p>• Upon termination, your right to use the service ceases immediately.</p>
                            <p>• We may retain certain information as required by law or for legitimate business purposes.</p>
                        </div>
                    </div>

                    <!-- Changes to Terms -->
                    <div>
                        <h4 class="text-lg font-semibold mb-3 flex items-center gap-2 text-gray-900">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Changes to Terms
                        </h4>
                        <div class="ml-7 text-sm">
                            <p>We reserve the right to modify these Terms of Service at any time. We will notify users of significant changes via email or prominent notice on the service. Continued use after changes constitutes acceptance of the updated terms.</p>
                        </div>
                    </div>

                    <!-- Governing Law -->
                    <div>
                        <h4 class="text-lg font-semibold mb-3 flex items-center gap-2 text-gray-900">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                            </svg>
                            Governing Law
                        </h4>
                        <div class="ml-7 text-sm">
                            <p>These Terms shall be governed by and construed in accordance with the laws of the jurisdiction in which the service provider operates, without regard to conflict of law principles.</p>
                        </div>
                    </div>

                    <!-- Contact -->
                    <div class="p-4 rounded-lg bg-gray-50 border border-gray-200">
                        <h5 class="font-semibold mb-2 text-gray-900">
                            Questions About These Terms?
                        </h5>
                        <p class="text-sm">
                            If you have questions about these Terms of Service, please contact us at <a :href="`mailto:${contactEmail}`" class="text-blue-600 hover:underline">{{ contactEmail }}</a>
                        </p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex justify-end mt-6 pt-6 border-t border-gray-200">
                    <button
                        @click="$emit('close')"
                        class="px-6 py-2 rounded-lg font-semibold bg-blue-600 text-white hover:bg-blue-700 transition-colors"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    isOpen: {
        type: Boolean,
        required: true
    },
    contactEmail: {
        type: String,
        default: 'legal@example.com'
    },
    lastUpdated: {
        type: String,
        default: new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
    }
});

defineEmits(['close']);
</script>
