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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900">
                            Privacy Policy
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
                            Your privacy is important to us. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our service.
                        </p>
                    </div>

                    <!-- Information We Collect -->
                    <div>
                        <h4 class="text-lg font-semibold mb-3 flex items-center gap-2 text-gray-900">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Information We Collect
                        </h4>
                        <div class="ml-7 space-y-2 text-sm">
                            <p><strong class="font-medium">Personal Information:</strong> Name, email address, phone number, and other contact details you provide when registering or placing orders.</p>
                            <p><strong class="font-medium">Account Information:</strong> Username, password (encrypted), and account preferences.</p>
                            <p><strong class="font-medium">Transaction Data:</strong> Order history, payment information, and billing/shipping addresses.</p>
                            <p><strong class="font-medium">Usage Data:</strong> Information about how you use our service, including IP address, browser type, and device information.</p>
                            <p><strong class="font-medium">Cookies:</strong> We use cookies and similar technologies to enhance your experience and analyze usage.</p>
                        </div>
                    </div>

                    <!-- How We Use Your Information -->
                    <div>
                        <h4 class="text-lg font-semibold mb-3 flex items-center gap-2 text-gray-900">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            How We Use Your Information
                        </h4>
                        <div class="ml-7 space-y-2 text-sm">
                            <p>• <strong>Service Delivery:</strong> To process orders, deliver products/services, and provide customer support.</p>
                            <p>• <strong>Account Management:</strong> To create and manage your account, including authentication and security.</p>
                            <p>• <strong>Communications:</strong> To send order confirmations, updates, and promotional materials (with your consent).</p>
                            <p>• <strong>Improvements:</strong> To analyze usage patterns and improve our services.</p>
                            <p>• <strong>Legal Compliance:</strong> To comply with legal obligations and protect our rights.</p>
                        </div>
                    </div>

                    <!-- Data Security -->
                    <div>
                        <h4 class="text-lg font-semibold mb-3 flex items-center gap-2 text-gray-900">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            Data Security
                        </h4>
                        <div class="ml-7 space-y-3 text-sm">
                            <p>We implement industry-standard security measures to protect your information:</p>
                            <div class="space-y-2">
                                <p> <strong>Encryption:</strong> All passwords are encrypted using bcrypt hashing. Data transmission is secured with SSL/TLS.</p>
                                <p> <strong>Access Controls:</strong> Strict access controls and authentication protocols to prevent unauthorized access.</p>
                                <p> <strong>Secure Storage:</strong> Data is stored in secure, access-controlled databases with regular backups.</p>
                                <p> <strong>Monitoring:</strong> Continuous monitoring for security threats and vulnerabilities.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Data Sharing -->
                    <div>
                        <h4 class="text-lg font-semibold mb-3 flex items-center gap-2 text-gray-900">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                            </svg>
                            Information Sharing
                        </h4>
                        <div class="ml-7 space-y-2 text-sm">
                            <p>We do not sell your personal information. We may share information with:</p>
                            <p>• <strong>Service Providers:</strong> Third-party vendors who assist in operations (e.g., payment processors, shipping companies).</p>
                            <p>• <strong>Legal Requirements:</strong> When required by law or to protect our rights.</p>
                            <p>• <strong>Business Transfers:</strong> In connection with mergers, acquisitions, or asset sales.</p>
                        </div>
                    </div>

                    <!-- Your Rights -->
                    <div>
                        <h4 class="text-lg font-semibold mb-3 flex items-center gap-2 text-gray-900">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Your Rights
                        </h4>
                        <div class="ml-7 space-y-2 text-sm">
                            <p>• <strong>Access:</strong> Request access to your personal information.</p>
                            <p>• <strong>Correction:</strong> Request correction of inaccurate information.</p>
                            <p>• <strong>Deletion:</strong> Request deletion of your information (subject to legal requirements).</p>
                            <p>• <strong>Portability:</strong> Request a copy of your data in a portable format.</p>
                            <p>• <strong>Opt-Out:</strong> Unsubscribe from marketing communications at any time.</p>
                        </div>
                    </div>

                    <!-- Data Retention -->
                    <div>
                        <h4 class="text-lg font-semibold mb-3 flex items-center gap-2 text-gray-900">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Data Retention
                        </h4>
                        <div class="ml-7 text-sm">
                            <p>We retain your information for as long as necessary to provide services, comply with legal obligations, resolve disputes, and enforce agreements. You may request deletion of your account at any time.</p>
                        </div>
                    </div>

                    <!-- Contact -->
                    <div class="p-4 rounded-lg bg-gray-50 border border-gray-200">
                        <h5 class="font-semibold mb-2 text-gray-900">
                            Questions About Privacy?
                        </h5>
                        <p class="text-sm">
                            If you have questions about this Privacy Policy or our data practices, please contact us at <a :href="`mailto:${contactEmail}`" class="text-blue-600 hover:underline">{{ contactEmail }}</a>
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
        default: 'privacy@example.com'
    },
    lastUpdated: {
        type: String,
        default: new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
    }
});

defineEmits(['close']);
</script>
