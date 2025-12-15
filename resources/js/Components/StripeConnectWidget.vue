<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 dark:border-gray-700">
        <div class="flex items-center justify-between mb-3">
            <!-- Status Badge -->
            <span
                v-if="status"
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                :class="statusBadgeClass"
            >
                {{ statusText }}
            </span>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-8">
            <svg
                class="animate-spin h-8 w-8 text-blue-600"
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
        </div>

        <!-- Status Display -->
        <div v-else class="space-y-3">
            <!-- Connection Status -->
            <div v-if="status" class="grid grid-cols-2 gap-2 text-sm">
                <div class="flex items-center gap-2">
                    <div
                        class="w-2 h-2 rounded-full"
                        :class="
                            status.connected ? 'bg-green-500' : 'bg-gray-300'
                        "
                    ></div>
                    <span class="text-gray-700 dark:text-gray-300"
                        >Connected</span
                    >
                </div>
                <div class="flex items-center gap-2">
                    <div
                        class="w-2 h-2 rounded-full"
                        :class="
                            status.onboarding_complete
                                ? 'bg-green-500'
                                : 'bg-gray-300'
                        "
                    ></div>
                    <span class="text-gray-700 dark:text-gray-300"
                        >Onboarding</span
                    >
                </div>
                <div class="flex items-center gap-2">
                    <div
                        class="w-2 h-2 rounded-full"
                        :class="
                            status.charges_enabled
                                ? 'bg-green-500'
                                : 'bg-orange-400'
                        "
                    ></div>
                    <span class="text-gray-700 dark:text-gray-300"
                        >Charges</span
                    >
                </div>
                <div class="flex items-center gap-2">
                    <div
                        class="w-2 h-2 rounded-full"
                        :class="
                            status.payouts_enabled
                                ? 'bg-green-500'
                                : 'bg-orange-400'
                        "
                    ></div>
                    <span class="text-gray-700 dark:text-gray-300"
                        >Payouts</span
                    >
                </div>
            </div>

            <!-- Account ID Display -->
            <div
                v-if="status?.account_id"
                class="text-xs text-gray-500 dark:text-gray-400 font-mono bg-gray-50 dark:bg-gray-700 p-2 rounded"
            >
                {{ status.account_id }}
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-2 pt-2">
                <!-- Connect Button (if not connected) -->
                <button
                    v-if="!status?.connected"
                    @click="initiateStripeConnect"
                    :disabled="initiating"
                    class="flex-1 flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors shadow-sm"
                >
                    <svg
                        v-if="!initiating"
                        class="w-4 h-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z"
                        />
                    </svg>
                    <svg
                        v-else
                        class="animate-spin h-4 w-4"
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
                    <span>{{
                        initiating ? "Starting..." : "Connect Stripe"
                    }}</span>
                </button>

                <!-- Dashboard Button (only if onboarding complete) -->
                <button
                    v-if="status?.onboarding_complete"
                    @click="openStripeDashboard"
                    class="flex-1 flex items-center justify-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors shadow-sm"
                >
                    <svg
                        class="w-4 h-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                        />
                    </svg>
                    <span>Dashboard</span>
                </button>

                <!-- Complete Onboarding Button (if connected but not complete) -->
                <button
                    v-if="status?.connected && !status?.onboarding_complete"
                    @click="initiateStripeConnect"
                    class="flex-1 flex items-center justify-center gap-2 px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors shadow-sm"
                >
                    <svg
                        class="w-4 h-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                    <span>Complete Onboarding</span>
                </button>

                <!-- Refresh Button -->
                <button
                    @click="refreshStatus"
                    class="px-3 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                    title="Refresh status"
                >
                    <svg
                        class="w-4 h-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                        />
                    </svg>
                </button>
            </div>

            <!-- Help Text -->
            <p
                v-if="!status?.connected"
                class="text-xs text-gray-500 dark:text-gray-400 mt-2"
            >
                Connect your Stripe account to accept payments
            </p>
            <p
                v-else-if="!status?.onboarding_complete"
                class="text-xs text-orange-600 dark:text-orange-400 mt-2"
            >
                Complete onboarding to enable payments
            </p>
            <p
                v-else-if="status?.charges_enabled"
                class="text-xs text-green-600 dark:text-green-400 mt-2"
            >
                ✓ Ready to accept payments
            </p>
        </div>

        <!-- Error Message -->
        <div
            v-if="error"
            class="mt-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg"
        >
            <p class="text-sm text-red-600 dark:text-red-400">{{ error }}</p>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useToast } from "@/Composables/useToast";

const toast = useToast();

// State
const loading = ref(true);
const initiating = ref(false);
const status = ref(null);
const error = ref(null);

// Computed properties
const statusBadgeClass = computed(() => {
    if (!status.value) return "bg-gray-100 text-gray-800";

    if (status.value.charges_enabled && status.value.payouts_enabled) {
        return "bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200";
    }

    if (status.value.connected && !status.value.onboarding_complete) {
        return "bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200";
    }

    if (status.value.connected) {
        return "bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200";
    }

    return "bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300";
});

const statusText = computed(() => {
    if (!status.value) return "Unknown";

    if (status.value.charges_enabled && status.value.payouts_enabled) {
        return "Active";
    }

    if (status.value.connected && !status.value.onboarding_complete) {
        return "Pending";
    }

    if (status.value.connected) {
        return "Connected";
    }

    return "Not Connected";
});

// Methods
const fetchStatus = async () => {
    loading.value = true;
    error.value = null;

    try {
        const response = await fetch("/stripe/connect/status", {
            headers: {
                Accept: "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
        });

        if (!response.ok) {
            throw new Error("Failed to fetch status");
        }

        status.value = await response.json();
    } catch (err) {
        error.value = err.message;
        console.error("Error fetching Stripe status:", err);
    } finally {
        loading.value = false;
    }
};

const initiateStripeConnect = async () => {
    initiating.value = true;
    error.value = null;

    try {
        const response = await fetch("/stripe/connect/initiate", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
                "X-Requested-With": "XMLHttpRequest",
            },
        });

        if (!response.ok) {
            const data = await response.json();
            throw new Error(
                data.message || "Failed to initiate Stripe Connect"
            );
        }

        const data = await response.json();

        // Redirect to Stripe onboarding
        if (data.url) {
            window.location.href = data.url;
        } else {
            throw new Error("No onboarding URL received");
        }
    } catch (err) {
        error.value = err.message;
        toast.error("Error", err.message);
        initiating.value = false;
    }
};

const openStripeDashboard = async () => {
    try {
        const response = await fetch("/stripe/connect/dashboard", {
            headers: {
                Accept: "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
        });

        if (!response.ok) {
            throw new Error("Failed to get dashboard URL");
        }

        const data = await response.json();

        if (data.url) {
            window.open(data.url, "_blank");
        }
    } catch (err) {
        error.value = err.message;
        toast.error("Error", err.message);
    }
};

const refreshStatus = () => {
    fetchStatus();
    toast.success("Status Updated", "Stripe Connect status refreshed");
};

// Lifecycle
onMounted(() => {
    fetchStatus();
});
</script>
