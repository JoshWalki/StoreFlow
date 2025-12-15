<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                Shipping Management
            </h1>
        </div>

        <!-- Tabs Navigation -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                    <button
                        @click="activeTab = 'zones'"
                        :class="[
                            activeTab === 'zones'
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
                        ]"
                    >
                        Shipping Zones
                    </button>
                    <button
                        @click="activeTab = 'methods'"
                        :class="[
                            activeTab === 'methods'
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
                        ]"
                    >
                        Shipping Methods
                    </button>
                    <button
                        @click="activeTab = 'calculator'"
                        :class="[
                            activeTab === 'calculator'
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
                        ]"
                    >
                        Rate Calculator
                    </button>
                    <button
                        @click="activeTab = 'settings'"
                        :class="[
                            activeTab === 'settings'
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
                        ]"
                    >
                        Settings
                    </button>
                </nav>
            </div>
        </div>

        <!-- Tab Content -->
        <div v-show="activeTab === 'zones'">
            <ShippingZones :zones="zones" @refresh="refreshZones" />
        </div>

        <div v-show="activeTab === 'methods'">
            <ShippingMethods
                :methods="methods"
                :zones="zones"
                @refresh="refreshMethods"
            />
        </div>

        <div v-show="activeTab === 'calculator'">
            <RateCalculator :zones="zones" />
        </div>

        <div v-show="activeTab === 'settings'">
            <!-- Shipping Settings -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h2
                    class="text-lg font-semibold text-gray-800 dark:text-white mb-4"
                >
                    Shipping Settings
                </h2>
                <form @submit.prevent="saveShippingSettings" class="space-y-4">
                    <div class="flex items-start space-x-3">
                        <div class="flex items-center h-5">
                            <input
                                id="shipping_enabled"
                                v-model="shippingForm.shipping_enabled"
                                type="checkbox"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            />
                        </div>
                        <div class="flex-1">
                            <label
                                for="shipping_enabled"
                                class="block text-sm font-medium text-gray-300"
                            >
                                Enable Shipping / Delivery
                            </label>
                            <p class="text-sm text-gray-500 mt-1">
                                When enabled, customers can choose delivery as a
                                fulfillment option. When disabled, only pickup
                                orders will be accepted.
                            </p>
                        </div>
                    </div>

                    <div
                        v-if="!shippingForm.shipping_enabled"
                        class="bg-yellow-50 border border-yellow-200 rounded-md p-4"
                    >
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg
                                    class="h-5 w-5 text-yellow-400"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">
                                    Shipping Disabled
                                </h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <p>
                                        Customers will only see pickup as a
                                        fulfillment option. Existing delivery
                                        orders will not be affected.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            :disabled="shippingForm.processing"
                            class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50"
                        >
                            {{
                                shippingForm.processing
                                    ? "Saving..."
                                    : "Save Shipping Settings"
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import ShippingZones from "@/Components/Shipping/ShippingZones.vue";
import ShippingMethods from "@/Components/Shipping/ShippingMethods.vue";
import RateCalculator from "@/Components/Shipping/RateCalculator.vue";

const props = defineProps({
    store: Object,
    user: Object,
    zones: {
        type: Array,
        required: true,
    },
    methods: {
        type: Array,
        required: true,
    },
});

const activeTab = ref("zones");

const shippingForm = useForm({
    shipping_enabled: props.store?.shipping_enabled ?? true,
});

const refreshZones = () => {
    router.reload({ only: ["zones"] });
};

const refreshMethods = () => {
    router.reload({ only: ["methods"] });
};

const saveShippingSettings = () => {
    shippingForm.put(route("store.settings.shipping"), {
        preserveScroll: true,
    });
};
</script>
