<template>
    <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                    Loyalty Program
                </h1>
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400"
                        >Program Status:</span
                    >
                    <span
                        class="px-3 py-1 text-sm font-semibold rounded-full"
                        :class="
                            form.is_enabled
                                ? 'bg-green-100 text-green-800'
                                : 'bg-gray-100 text-gray-800'
                        "
                    >
                        {{ form.is_enabled ? "Active" : "Inactive" }}
                    </span>
                </div>
            </div>

            <!-- Enable/Disable Program -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2
                            class="text-lg font-semibold text-gray-800 dark:text-white"
                        >
                            Enable Loyalty Program
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">
                            Reward customers for their purchases and encourage
                            repeat business
                        </p>
                    </div>
                    <label
                        class="relative inline-flex items-center cursor-pointer"
                    >
                        <input
                            type="checkbox"
                            v-model="form.is_enabled"
                            class="sr-only peer"
                        />
                        <div
                            class="w-11 h-6 bg-gray-200 dark:bg-gray-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white dark:after:bg-gray-300 after:border-gray-300 dark:after:border-gray-600 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"
                        ></div>
                    </label>
                </div>
            </div>

            <div v-if="form.is_enabled" class="space-y-6">
                <!-- Points Earning Rules -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h2
                        class="text-lg font-semibold text-gray-800 dark:text-white mb-4"
                    >
                        Points Earning Rules
                    </h2>
                    <div class="space-y-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                            >
                                Points per Dollar Spent
                            </label>
                            <input
                                v-model.number="form.points_per_dollar"
                                type="number"
                                min="1"
                                class="max-w-xs w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500"
                            />
                            <p
                                class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                            >
                                Customers earn this many points for every dollar
                                spent
                            </p>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                            >
                                Sign-up Bonus Points
                            </label>
                            <input
                                v-model.number="form.signup_bonus"
                                type="number"
                                min="0"
                                class="max-w-xs w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500"
                            />
                            <p
                                class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                            >
                                One-time bonus when customers join the program
                            </p>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                            >
                                Birthday Bonus Points
                            </label>
                            <input
                                v-model.number="form.birthday_bonus"
                                type="number"
                                min="0"
                                class="max-w-xs w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500"
                            />
                            <p
                                class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                            >
                                Annual bonus on customer's birthday
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Points Redemption -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h2
                        class="text-lg font-semibold text-gray-800 dark:text-white mb-4"
                    >
                        Reward Configuration
                    </h2>
                    <div class="space-y-6">
                        <!-- Minimum Points Threshold -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                            >
                                Minimum Points Required for Reward
                            </label>
                            <input
                                v-model.number="form.min_redemption_points"
                                type="number"
                                min="1"
                                class="max-w-xs w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500"
                            />
                            <p
                                class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                            >
                                Customers must reach this points balance to
                                unlock the reward
                            </p>
                        </div>

                        <!-- Reward Type Selection -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                            >
                                Reward Type
                            </label>
                            <select
                                v-model="form.reward_type"
                                class="max-w-md w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="percentage">
                                    Percentage Discount
                                </option>
                                <option value="fixed_amount">
                                    Fixed Dollar Amount
                                </option>
                            </select>
                            <p
                                class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                            >
                                Choose how the reward will be applied to orders
                            </p>
                        </div>

                        <!-- Reward Value -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                            >
                                {{
                                    form.reward_type === "percentage"
                                        ? "Discount Percentage (%)"
                                        : "Discount Amount ($)"
                                }}
                            </label>
                            <div class="flex items-center max-w-md">
                                <span
                                    v-if="form.reward_type === 'fixed_amount'"
                                    class="px-4 py-2 bg-gray-100 dark:bg-gray-600 border border-r-0 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-l-md"
                                >
                                    $
                                </span>
                                <input
                                    v-model.number="form.reward_value"
                                    type="number"
                                    :min="
                                        form.reward_type === 'percentage'
                                            ? 1
                                            : 0.01
                                    "
                                    :max="
                                        form.reward_type === 'percentage'
                                            ? 100
                                            : undefined
                                    "
                                    step="0.01"
                                    :class="[
                                        'flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500',
                                        form.reward_type === 'percentage'
                                            ? 'rounded-l-md'
                                            : '',
                                        form.reward_type === 'fixed_amount'
                                            ? 'rounded-r-md'
                                            : '',
                                    ]"
                                />
                                <span
                                    v-if="form.reward_type === 'percentage'"
                                    class="px-4 py-2 bg-gray-100 dark:bg-gray-600 border border-l-0 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-r-md"
                                >
                                    %
                                </span>
                            </div>
                            <p
                                class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                            >
                                <template
                                    v-if="form.reward_type === 'percentage'"
                                >
                                    Enter the percentage discount (e.g., 10 for
                                    10% off)
                                </template>
                                <template v-else>
                                    Enter the fixed dollar amount discount
                                    (e.g., 5.00 for $5.00 off)
                                </template>
                            </p>
                        </div>

                        <!-- Reward Description -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                            >
                                Reward Description
                            </label>
                            <input
                                v-model="form.reward_description"
                                type="text"
                                placeholder="e.g., 10% off your next purchase"
                                class="max-w-md w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500"
                            />
                            <p
                                class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                            >
                                This message will be shown to customers when
                                they unlock the reward
                            </p>
                        </div>

                        <!-- Preview -->
                        <div
                            class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4"
                        >
                            <h4
                                class="text-sm font-semibold text-blue-900 dark:text-blue-300 mb-2"
                            >
                                Reward Preview
                            </h4>
                            <div
                                class="text-sm text-blue-800 dark:text-blue-400"
                            >
                                <p>
                                    <strong>Threshold:</strong>
                                    {{ form.min_redemption_points }} points
                                </p>
                                <p>
                                    <strong>Reward:</strong>
                                    <template
                                        v-if="form.reward_type === 'percentage'"
                                    >
                                        {{ form.reward_value }}% off
                                    </template>
                                    <template v-else>
                                        ${{ form.reward_value.toFixed(2) }} off
                                    </template>
                                </p>
                                <p>
                                    <strong>Message:</strong>
                                    {{
                                        form.reward_description ||
                                        "No description"
                                    }}
                                </p>
                            </div>
                            <div
                                class="mt-3 p-3 bg-white dark:bg-gray-800 rounded border border-blue-200 dark:border-blue-700"
                            >
                                <p
                                    class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1"
                                >
                                    How it works:
                                </p>
                                <ul
                                    class="text-xs text-gray-600 dark:text-gray-400 space-y-1 list-disc list-inside"
                                >
                                    <li
                                        v-if="form.reward_type === 'percentage'"
                                    >
                                        When customers reach
                                        {{ form.min_redemption_points }} points,
                                        they can redeem for
                                        {{ form.reward_value }}% off their order
                                        total at checkout
                                    </li>
                                    <li v-else>
                                        When customers reach
                                        {{ form.min_redemption_points }} points,
                                        they can redeem for ${{
                                            form.reward_value.toFixed(2)
                                        }}
                                        off their order total at checkout
                                    </li>
                                    <li>
                                        Points are automatically deducted ({{
                                            form.min_redemption_points
                                        }}
                                        points) when reward is applied
                                    </li>
                                    <li>
                                        Customers can choose whether to use
                                        their reward or save points for later
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tier System -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2
                                class="text-lg font-semibold text-gray-800 dark:text-white"
                            >
                                Tier System
                            </h2>
                            <p class="text-sm text-gray-600 mt-1">
                                Feature coming soon.
                            </p>
                        </div>
                        <label class="flex items-center">
                            <input
                                disabled
                                type="checkbox"
                                v-model="form.enable_tiers"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            />
                            <span
                                class="ml-2 text-sm text-gray-700 dark:text-gray-300"
                                >Enable Tiers</span
                            >
                        </label>
                    </div>

                    <div v-if="form.enable_tiers" class="space-y-3">
                        <div
                            v-for="tier in tiers"
                            :key="tier.id"
                            class="border border-gray-200 rounded-lg p-4"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-medium text-gray-900">
                                        {{ tier.name }}
                                    </h3>
                                    <p
                                        class="text-sm text-gray-600 dark:text-gray-400"
                                    >
                                        Spend ${{ tier.threshold }}+ to unlock
                                    </p>
                                </div>
                                <div class="text-right">
                                    <div
                                        class="text-sm font-medium text-blue-600 dark:text-blue-400"
                                    >
                                        {{ tier.bonus_multiplier }}x Points
                                    </div>
                                    <div
                                        class="text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        {{ tier.benefits }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Expiration Settings -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h2
                        class="text-lg font-semibold text-gray-800 dark:text-white mb-4"
                    >
                        Points Expiration
                    </h2>
                    <div class="flex items-center">
                        <input
                            id="enable_expiration"
                            v-model="form.enable_expiration"
                            type="checkbox"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        />
                        <label
                            for="enable_expiration"
                            class="ml-2 block text-sm text-gray-700 dark:text-gray-300"
                        >
                            Points expire after inactivity
                        </label>
                    </div>

                    <div v-if="form.enable_expiration" class="mt-4 ml-6">
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                        >
                            Expiration Period (months)
                        </label>
                        <input
                            v-model.number="form.expiration_months"
                            type="number"
                            min="1"
                            max="36"
                            class="max-w-xs w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500"
                        />
                        <p
                            class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                        >
                            Points expire if no activity for this many months
                        </p>
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end">
                <button
                    @click="saveSettings"
                    :disabled="form.processing"
                    class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50"
                >
                    {{ form.processing ? "Saving..." : "Save Settings" }}
                </button>
            </div>
        </div>
</template>

<script setup>
import { useForm } from "@inertiajs/vue3";

const props = defineProps({
    settings: Object,
    tiers: Array,
    store: Object,
    user: Object,
    loyaltyConfig: Object,
});

const form = useForm({
    is_enabled: props.settings?.is_enabled || false,
    points_per_dollar: props.settings?.points_per_dollar || 1,
    signup_bonus: props.settings?.signup_bonus || 100,
    birthday_bonus: props.settings?.birthday_bonus || 50,
    min_redemption_points: props.settings?.min_redemption_points || 100,
    reward_type: props.loyaltyConfig?.reward_json?.type || "percentage",
    reward_value: props.loyaltyConfig?.reward_json?.value || 10,
    reward_description:
        props.loyaltyConfig?.reward_json?.description ||
        "10% off your next purchase",
    enable_tiers: props.settings?.enable_tiers || false,
    enable_expiration: props.settings?.enable_expiration || false,
    expiration_months: props.settings?.expiration_months || 12,
});

const saveSettings = () => {
    form.post(route("loyalty.update"), {
        preserveScroll: true,
    });
};
</script>
