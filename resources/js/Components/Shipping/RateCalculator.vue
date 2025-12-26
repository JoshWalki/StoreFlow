<template>
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">
                Shipping Rate Calculator
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                Test your shipping rates by entering customer details and order
                information.
            </p>

            <form @submit.prevent="calculateRates" class="space-y-6">
                <!-- Customer Address -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">
                        Customer Address
                    </h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label
                                for="address_line1"
                                class="block text-sm font-medium text-gray-700"
                                >Street Address</label
                            >
                            <input
                                v-model="testData.address.address_line1"
                                type="text"
                                id="address_line1"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="123 Main Street"
                            />
                        </div>
                        <div>
                            <label
                                for="city"
                                class="block text-sm font-medium text-gray-700"
                                >City</label
                            >
                            <input
                                v-model="testData.address.city"
                                type="text"
                                id="city"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="Melbourne"
                            />
                        </div>
                        <div>
                            <label
                                for="province"
                                class="block text-sm font-medium text-gray-700"
                                >Province/State</label
                            >
                            <input
                                v-model="testData.address.province"
                                type="text"
                                id="province"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="VIC"
                            />
                        </div>
                        <div>
                            <label
                                for="postal_code"
                                class="block text-sm font-medium text-gray-700"
                                >Postal Code</label
                            >
                            <input
                                v-model="testData.address.postal_code"
                                type="text"
                                id="postal_code"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="3004"
                            />
                        </div>
                        <div>
                            <label
                                for="country"
                                class="block text-sm font-medium text-gray-700"
                                >Country</label
                            >
                            <input
                                v-model="testData.address.country"
                                type="text"
                                id="country"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="Australia"
                            />
                        </div>
                    </div>
                </div>

                <!-- Order Details -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">
                        Order Details
                    </h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label
                                for="subtotal"
                                class="block text-sm font-medium text-gray-700"
                                >Subtotal (AUD)</label
                            >
                            <input
                                v-model.number="testData.order.subtotal"
                                type="number"
                                id="subtotal"
                                step="0.01"
                                min="0"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="100.00"
                            />
                        </div>
                        <div>
                            <label
                                for="weight_kg"
                                class="block text-sm font-medium text-gray-700"
                                >Total Weight (kg)</label
                            >
                            <input
                                v-model.number="testData.order.weight_kg"
                                type="number"
                                id="weight_kg"
                                step="0.01"
                                min="0"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="2.5"
                            />
                        </div>
                        <div>
                            <label
                                for="items_count"
                                class="block text-sm font-medium text-gray-700"
                                >Number of Items</label
                            >
                            <input
                                v-model.number="testData.order.items_count"
                                type="number"
                                id="items_count"
                                min="1"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="5"
                            />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="calculating"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50"
                    >
                        {{ calculating ? "Calculating..." : "Calculate Rates" }}
                    </button>
                </div>
            </form>

            <!-- Results -->
            <div v-if="results" class="mt-8">
                <h4 class="text-base font-semibold text-gray-900 mb-4">
                    Available Shipping Options
                </h4>

                <div
                    v-if="results.rates && results.rates.length > 0"
                    class="space-y-3"
                >
                    <div
                        v-for="rate in results.rates"
                        :key="rate.method_id"
                        class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors"
                    >
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h5 class="text-sm font-semibold text-gray-900">
                                    {{ rate.method_name }}
                                </h5>
                                <p
                                    v-if="rate.zone_name"
                                    class="text-xs text-gray-500 mt-1"
                                >
                                    Zone: {{ rate.zone_name }}
                                </p>
                                <p class="text-xs text-gray-600 mt-1">
                                    Estimated delivery:
                                    {{ rate.estimated_days_min }}-{{
                                        rate.estimated_days_max
                                    }}
                                    business days
                                </p>
                                <div
                                    v-if="rate.calculation_breakdown"
                                    class="mt-2 text-xs text-gray-500"
                                >
                                    <p class="font-medium">
                                        Calculation breakdown:
                                    </p>
                                    <ul
                                        class="list-disc list-inside ml-2 space-y-0.5"
                                    >
                                        <li
                                            v-for="(
                                                value, key
                                            ) in rate.calculation_breakdown"
                                            :key="key"
                                        >
                                            {{ formatBreakdownKey(key) }}:
                                            {{ formatCurrency(value) }}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="ml-4 text-right">
                                <p class="text-lg font-bold text-gray-900">
                                    {{ formatCurrency(rate.rate) }}
                                </p>
                                <p
                                    v-if="rate.rate === 0"
                                    class="text-xs font-medium text-green-600 mt-1"
                                >
                                    FREE
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-else-if="results.rates && results.rates.length === 0"
                    class="text-center py-8"
                >
                    <svg
                        class="mx-auto h-12 w-12 text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">
                        No shipping options available
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        This address is not covered by any of your shipping
                        zones.
                    </p>
                </div>

                <!-- Matched Zone Info -->
                <div
                    v-if="results.matched_zone"
                    class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-md"
                >
                    <p class="text-sm text-blue-800">
                        <span class="font-medium">Matched Zone:</span>
                        {{ results.matched_zone.name }}
                        <span class="ml-2 text-xs"
                            >({{ results.matched_zone.zone_type }})</span
                        >
                    </p>
                </div>

                <!-- Errors -->
                <div
                    v-if="results.error"
                    class="mt-4 p-3 bg-red-50 border border-red-200 rounded-md"
                >
                    <p class="text-sm text-red-800">{{ results.error }}</p>
                </div>

                <!-- Diagnostic Feedback -->
                <div v-if="results.feedback" class="mt-8">
                    <div class="border-t border-gray-200 pt-6">
                        <h4 class="text-base font-semibold text-gray-900 mb-4">
                            Diagnostic Information
                        </h4>

                        <!-- Zone Analysis -->
                        <div class="space-y-3">
                            <div
                                v-for="zone in results.feedback.zones"
                                :key="zone.id"
                                class="border rounded-lg overflow-hidden"
                                :class="{
                                    'border-green-300 bg-green-50':
                                        zone.matched &&
                                        zone.methods_available > 0,
                                    'border-yellow-300 bg-yellow-50':
                                        zone.matched &&
                                        zone.methods_available === 0,
                                    'border-gray-200 bg-gray-50': !zone.matched,
                                }"
                            >
                                <button
                                    @click="toggleZone(zone.id)"
                                    class="w-full px-4 py-3 flex items-center justify-between hover:bg-opacity-75 transition-colors"
                                >
                                    <div class="flex items-center space-x-3">
                                        <!-- Status Icon -->
                                        <div
                                            v-if="
                                                zone.matched &&
                                                zone.methods_available > 0
                                            "
                                        >
                                            <svg
                                                class="h-5 w-5 text-green-600"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                        </div>
                                        <div
                                            v-else-if="
                                                zone.matched &&
                                                zone.methods_available === 0
                                            "
                                        >
                                            <svg
                                                class="h-5 w-5 text-yellow-600"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                        </div>
                                        <div v-else>
                                            <svg
                                                class="h-5 w-5 text-gray-400"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                        </div>

                                        <div class="text-left">
                                            <p class="font-medium text-sm">
                                                {{ zone.name }}
                                            </p>
                                            <p
                                                class="text-xs text-gray-600 mt-0.5"
                                            >
                                                {{ zone.reason }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center space-x-2">
                                        <span
                                            v-if="zone.methods_available > 0"
                                            class="text-xs px-2 py-1 bg-green-100 text-green-800 rounded-full font-medium"
                                        >
                                            {{
                                                zone.methods_available
                                            }}
                                            available
                                        </span>
                                        <svg
                                            class="h-5 w-5 text-gray-400 transition-transform"
                                            :class="{
                                                'rotate-180':
                                                    expandedZones.includes(
                                                        zone.id
                                                    ),
                                            }"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 9l-7 7-7-7"
                                            />
                                        </svg>
                                    </div>
                                </button>

                                <!-- Expanded Zone Details -->
                                <div
                                    v-if="expandedZones.includes(zone.id)"
                                    class="px-4 pb-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700"
                                >
                                    <div
                                        v-if="zone.suggestion"
                                        class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-md"
                                    >
                                        <p
                                            class="text-xs font-medium text-blue-800"
                                        >
                                            💡 Suggestion:
                                        </p>
                                        <p class="text-xs text-blue-700 mt-1">
                                            {{ zone.suggestion }}
                                        </p>
                                    </div>

                                    <!-- Methods Analysis -->
                                    <div
                                        v-if="
                                            zone.methods_analysis &&
                                            zone.methods_analysis.length > 0
                                        "
                                        class="mt-4"
                                    >
                                        <p
                                            class="text-xs font-semibold text-gray-700 mb-2"
                                        >
                                            Shipping Methods ({{
                                                zone.methods_total
                                            }})
                                        </p>
                                        <div class="space-y-2">
                                            <div
                                                v-for="method in zone.methods_analysis"
                                                :key="method.id"
                                                class="border rounded-md p-3"
                                                :class="{
                                                    'border-green-200 bg-green-50':
                                                        method.available,
                                                    'border-gray-200 bg-gray-50':
                                                        !method.available,
                                                }"
                                            >
                                                <div
                                                    class="flex items-start justify-between"
                                                >
                                                    <div class="flex-1">
                                                        <p
                                                            class="text-sm font-medium"
                                                            :class="
                                                                method.available
                                                                    ? 'text-green-900'
                                                                    : 'text-gray-700'
                                                            "
                                                        >
                                                            {{ method.name }}
                                                        </p>
                                                        <p
                                                            class="text-xs text-gray-600 mt-1"
                                                        >
                                                            {{ method.reason }}
                                                        </p>
                                                        <div
                                                            v-if="
                                                                method.suggestion
                                                            "
                                                            class="mt-2 text-xs text-blue-700 italic"
                                                        >
                                                            💡
                                                            {{
                                                                method.suggestion
                                                            }}
                                                        </div>
                                                    </div>
                                                    <span
                                                        class="text-xs px-2 py-1 rounded-full font-medium ml-2"
                                                        :class="
                                                            method.available
                                                                ? 'bg-green-100 text-green-800'
                                                                : 'bg-gray-100 text-gray-600'
                                                        "
                                                    >
                                                        {{
                                                            method.available
                                                                ? "Available"
                                                                : "Unavailable"
                                                        }}
                                                    </span>
                                                </div>

                                                <!-- Rates Analysis -->
                                                <div
                                                    v-if="
                                                        method.rates_analysis &&
                                                        method.rates_analysis
                                                            .length > 0
                                                    "
                                                    class="mt-3 pl-3 border-l-2 border-gray-300"
                                                >
                                                    <p
                                                        class="text-xs font-medium text-gray-600 mb-2"
                                                    >
                                                        Rates:
                                                    </p>
                                                    <div class="space-y-2">
                                                        <div
                                                            v-for="rate in method.rates_analysis"
                                                            :key="rate.id"
                                                            class="text-xs p-2 rounded"
                                                            :class="
                                                                rate.applies
                                                                    ? 'bg-green-100'
                                                                    : 'bg-gray-100'
                                                            "
                                                        >
                                                            <div
                                                                class="flex items-start justify-between"
                                                            >
                                                                <div
                                                                    class="flex-1"
                                                                >
                                                                    <p
                                                                        class="font-medium"
                                                                        :class="
                                                                            rate.applies
                                                                                ? 'text-green-900'
                                                                                : 'text-gray-700'
                                                                        "
                                                                    >
                                                                        {{
                                                                            rate.name
                                                                        }}
                                                                        <span
                                                                            v-if="
                                                                                rate.applies &&
                                                                                rate.cost_cents !==
                                                                                    null
                                                                            "
                                                                            class="ml-2 text-green-700"
                                                                        >
                                                                            ({{
                                                                                formatCurrency(
                                                                                    rate.cost_cents /
                                                                                        100
                                                                                )
                                                                            }})
                                                                        </span>
                                                                    </p>
                                                                    <p
                                                                        class="text-gray-600 mt-1"
                                                                    >
                                                                        {{
                                                                            rate.reason
                                                                        }}
                                                                    </p>
                                                                    <p
                                                                        v-if="
                                                                            rate.suggestion
                                                                        "
                                                                        class="text-blue-700 mt-1 italic"
                                                                    >
                                                                        💡
                                                                        {{
                                                                            rate.suggestion
                                                                        }}
                                                                    </p>
                                                                </div>
                                                                <span
                                                                    class="text-xs px-1.5 py-0.5 rounded font-medium ml-2"
                                                                    :class="
                                                                        rate.applies
                                                                            ? 'bg-green-200 text-green-900'
                                                                            : 'bg-gray-200 text-gray-700'
                                                                    "
                                                                >
                                                                    {{
                                                                        rate.applies
                                                                            ? "✓"
                                                                            : "✗"
                                                                    }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        v-else
                                        class="mt-4 text-xs text-gray-500 italic"
                                    >
                                        No shipping methods configured for this
                                        zone.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";

const props = defineProps({
    zones: {
        type: Array,
        required: true,
    },
});

const calculating = ref(false);
const results = ref(null);
const expandedZones = ref([]);

const testData = ref({
    address: {
        address_line1: "",
        city: "",
        province: "",
        postal_code: "",
        country: "Australia",
    },
    order: {
        subtotal: 100.0,
        weight_kg: 2.5,
        items_count: 3,
    },
});

const calculateRates = async () => {
    calculating.value = true;
    results.value = null;

    try {
        const response = await axios.post("/shipping/calculate", {
            address: testData.value.address,
            order_details: testData.value.order,
        });

        results.value = response.data;

        // Log debug information to console
        if (response.data.debug) {
            console.group("🚚 Shipping Rate Calculator Debug");
            console.log("Request Data:", {
                address: testData.value.address,
                order_details: testData.value.order,
            });
            console.log("Debug Info:", response.data.debug);

            if (response.data.debug.all_zones) {
                console.group("Zones Analysis:");
                response.data.debug.all_zones.forEach((zone) => {
                    console.log(`Zone: ${zone.name}`, {
                        is_active: zone.is_active,
                        matches_address: zone.matches_address,
                        countries: zone.countries,
                        states: zone.states,
                        methods_count: zone.methods_count,
                        methods: zone.methods,
                    });
                });
                console.groupEnd();
            }

            console.log("Returned Rates:", response.data.rates);
            console.groupEnd();
        }
    } catch (error) {
        console.error("Error calculating rates:", error);
        results.value = {
            error:
                error.response?.data?.message ||
                "Failed to calculate shipping rates. Please try again.",
            rates: [],
        };
    } finally {
        calculating.value = false;
    }
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat("en-AU", {
        style: "currency",
        currency: "AUD",
    }).format(amount);
};

const formatBreakdownKey = (key) => {
    return key
        .split("_")
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(" ");
};

const toggleZone = (zoneId) => {
    const index = expandedZones.value.indexOf(zoneId);
    if (index > -1) {
        expandedZones.value.splice(index, 1);
    } else {
        expandedZones.value.push(zoneId);
    }
};
</script>
