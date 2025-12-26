<template>
    <TransitionRoot appear :show="isOpen" as="template">
        <Dialog as="div" @close="closeModal" class="relative z-50">
            <TransitionChild
                as="template"
                enter="duration-300 ease-out"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="duration-200 ease-in"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-black bg-opacity-25" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div
                    class="flex min-h-full items-center justify-center p-4 text-center"
                >
                    <TransitionChild
                        as="template"
                        enter="duration-300 ease-out"
                        enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100"
                        leave="duration-200 ease-in"
                        leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95"
                    >
                        <DialogPanel
                            class="w-full max-w-3xl transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 p-6 text-left align-middle shadow-xl transition-all"
                        >
                            <!-- Header -->
                            <div class="flex justify-between items-start mb-6">
                                <div>
                                    <DialogTitle
                                        as="h3"
                                        class="text-2xl font-semibold leading-6 text-gray-900 dark:text-white"
                                    >
                                        Order {{ order.public_id }}
                                    </DialogTitle>
                                    <p
                                        class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        Created
                                        {{ formatDateTime(order.created_at) }}
                                    </p>
                                </div>
                                <button
                                    @click="closeModal"
                                    class="text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-300"
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
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>
                            </div>

                            <!-- Customer & Delivery Info -->
                            <div class="grid grid-cols-2 gap-6 mb-6">
                                <div>
                                    <h4
                                        class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2"
                                    >
                                        Customer
                                    </h4>
                                    <div class="flex items-center gap-2 mb-1">
                                        <p
                                            class="text-gray-900 dark:text-white"
                                        >
                                            {{ order.customer_name }}
                                        </p>
                                        <span
                                            v-if="
                                                order.customer_order_count === 1
                                            "
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                                        >
                                            First Order
                                        </span>
                                    </div>
                                    <p
                                        class="text-sm text-gray-600 dark:text-gray-400"
                                    >
                                        {{ order.customer_email }}
                                    </p>
                                    <p
                                        class="text-sm text-gray-600 dark:text-gray-400"
                                    >
                                        {{ order.customer_phone }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Total orders:
                                        {{ order.customer_order_count }}
                                    </p>
                                </div>

                                <div>
                                    <h4
                                        class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2"
                                    >
                                        {{
                                            order.fulfilment_type === "pickup"
                                                ? "Pickup Details"
                                                : "Shipping Address"
                                        }}
                                    </h4>
                                    <div
                                        v-if="
                                            order.fulfilment_type === 'pickup'
                                        "
                                    >
                                        <p class="text-sm text-gray-700 dark:text-gray-400">
                                            Requested: {{
                                                order.pickup_time
                                                    ? formatDateTime(
                                                          order.pickup_time
                                                      )
                                                    : "TBD"
                                            }}
                                        </p>
                                        <div class="mt-3">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Pickup ETA
                                                <span v-if="!currentPickupEta" class="text-xs font-normal text-gray-500 dark:text-gray-400">(Default)</span>
                                            </label>
                                            <div class="space-y-2">
                                                <!-- Manual datetime input -->
                                                <input
                                                    v-model="editableEta"
                                                    type="datetime-local"
                                                    @change="updateEtaFromInput"
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                                />

                                                <!-- Quick adjust buttons -->
                                                <div class="flex items-center gap-2">
                                                    <button
                                                        @click="adjustPickupEta(-5)"
                                                        :disabled="updatingEta"
                                                        class="flex-1 flex items-center justify-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50 transition-colors"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        -5 min
                                                    </button>
                                                    <button
                                                        @click="adjustPickupEta(5)"
                                                        :disabled="updatingEta"
                                                        class="flex-1 flex items-center justify-center gap-2 px-3 py-2 text-sm font-medium text-white bg-green-600 border border-green-600 rounded-md hover:bg-green-700 disabled:opacity-50 transition-colors"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        +5 min
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="text-sm text-gray-900">
                                        <p>{{ order.shipping_name }}</p>
                                        <p>{{ order.line1 }}</p>
                                        <p v-if="order.line2">
                                            {{ order.line2 }}
                                        </p>
                                        <p>
                                            {{ order.city }}, {{ order.state }}
                                            {{ order.postcode }}
                                        </p>
                                        <p>{{ order.country }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Order Items -->
                            <div class="mb-6">
                                <h4
                                    class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3"
                                >
                                    Order Items
                                </h4>
                                <div
                                    class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden"
                                >
                                    <table
                                        class="min-w-full divide-y divide-gray-200"
                                    >
                                        <thead
                                            class="bg-gray-50 dark:bg-gray-700"
                                        >
                                            <tr>
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                                >
                                                    Product
                                                </th>
                                                <th
                                                    class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                                >
                                                    Qty
                                                </th>
                                                <th
                                                    class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                                >
                                                    Price
                                                </th>
                                                <th
                                                    class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                                >
                                                    Total
                                                </th>
                                                <th
                                                    class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                                >
                                                    Action
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody
                                            class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700"
                                        >
                                            <tr
                                                v-for="item in order.items"
                                                :key="item.id"
                                                :class="item.is_refunded ? 'bg-red-50 dark:bg-red-900/20' : ''"
                                            >
                                                <td
                                                    class="px-4 py-3 text-sm text-gray-900 dark:text-white"
                                                >
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        <span>{{ item.product_name }}</span>
                                                        <!-- Discount Badge -->
                                                        <span
                                                            v-if="item.product && item.product.price_cents && item.unit_price_cents && item.unit_price_cents < item.product.price_cents"
                                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200"
                                                        >
                                                            {{ Math.round(((item.product.price_cents - item.unit_price_cents) / item.product.price_cents) * 100) }}% OFF
                                                        </span>
                                                        <span
                                                            v-if="item.is_refunded"
                                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200"
                                                        >
                                                            Refunded
                                                        </span>
                                                    </div>
                                                    <!-- Addons -->
                                                    <div v-if="item.addons && item.addons.length > 0" class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                                                        <div v-for="(addon, addonIdx) in item.addons" :key="addonIdx" class="flex items-center gap-1">
                                                            <span>+ {{ addon.name }}</span>
                                                            <span v-if="addon.quantity > 1" class="text-gray-500">(x{{ addon.quantity }})</span>
                                                            <span v-if="addon.total_price_cents > 0" class="text-gray-500">(+{{ formatCurrency(addon.total_price_cents) }})</span>
                                                        </div>
                                                    </div>
                                                    <!-- Special Instructions -->
                                                    <div v-if="item.special_instructions" class="mt-1 text-xs italic text-blue-600 dark:text-blue-400">
                                                        Note: {{ item.special_instructions }}
                                                    </div>
                                                    <!-- Refund Info -->
                                                    <div v-if="item.is_refunded" class="mt-1 text-xs text-red-600 dark:text-red-400">
                                                        <div>Refunded: {{ formatDateTime(item.refund_date) }}</div>
                                                        <div>Reason: {{ item.refund_reason }}</div>
                                                    </div>
                                                </td>
                                                <td
                                                    class="px-4 py-3 text-sm text-gray-900 dark:text-white text-center"
                                                >
                                                    {{ item.quantity }}
                                                </td>
                                                <td
                                                    class="px-4 py-3 text-sm text-right"
                                                >
                                                    <!-- Show discount pricing if applicable -->
                                                    <div v-if="item.product && item.product.price_cents && item.unit_price_cents && item.unit_price_cents < item.product.price_cents" class="flex flex-col items-end gap-0.5">
                                                        <span class="font-semibold text-green-600 dark:text-green-400">
                                                            {{ formatCurrency(item.unit_price_cents) }}
                                                        </span>
                                                        <span class="text-xs line-through text-gray-500 dark:text-gray-400">
                                                            {{ formatCurrency(item.product.price_cents) }}
                                                        </span>
                                                        <span class="text-xs text-green-600 dark:text-green-400">
                                                            Save {{ formatCurrency(item.product.price_cents - item.unit_price_cents) }}
                                                        </span>
                                                    </div>
                                                    <!-- Regular price -->
                                                    <span v-else class="text-gray-900 dark:text-white">
                                                        {{
                                                            formatCurrency(
                                                                item.unit_price_cents || item.price_cents || (item.total_cents / item.quantity)
                                                            )
                                                        }}
                                                    </span>
                                                </td>
                                                <td
                                                    class="px-4 py-3 text-sm text-gray-900 dark:text-white text-right"
                                                >
                                                    {{
                                                        formatCurrency(
                                                            item.total_cents
                                                        )
                                                    }}
                                                </td>
                                                <td
                                                    class="px-4 py-3 text-center"
                                                >
                                                    <button
                                                        v-if="!item.is_refunded && order.payment_status === 'paid'"
                                                        @click="openRefundModal(item)"
                                                        class="px-3 py-1 text-xs font-medium text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 border border-red-300 dark:border-red-600 rounded hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                                    >
                                                        Refund
                                                    </button>
                                                    <span v-else-if="item.is_refunded" class="text-xs text-gray-400">-</span>
                                                    <span v-else class="text-xs text-gray-400">N/A</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Order Summary -->
                            <div
                                class="mb-6 bg-gray-50 dark:bg-gray-700 rounded-lg p-4"
                            >
                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span
                                            class="text-gray-600 dark:text-gray-400"
                                            >Subtotal:</span
                                        >
                                        <span
                                            class="text-gray-900 dark:text-white"
                                            >{{
                                                formatCurrency(
                                                    order.items_total_cents
                                                )
                                            }}</span
                                        >
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span
                                            class="text-gray-600 dark:text-gray-400"
                                            >Shipping:</span
                                        >
                                        <span
                                            class="text-gray-900 dark:text-white"
                                            >{{
                                                formatCurrency(
                                                    order.shipping_cost_cents
                                                )
                                            }}</span
                                        >
                                    </div>
                                    <div
                                        class="flex justify-between text-base font-semibold pt-2 border-t border-gray-200 dark:border-gray-600"
                                    >
                                        <span
                                            class="text-gray-900 dark:text-white"
                                            >Total:</span
                                        >
                                        <span
                                            class="text-gray-900 dark:text-white"
                                            >{{
                                                formatCurrency(
                                                    order.total_cents
                                                )
                                            }}</span
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- Status & Actions -->
                            <div class="mb-6">
                                <h4
                                    class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3"
                                >
                                    Status & Actions
                                </h4>
                                <div class="flex items-center space-x-3 mb-4">
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800"
                                    >
                                        {{ formatStatus(order.status) }}
                                    </span>
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                        :class="
                                            order.payment_status === 'paid'
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-yellow-100 text-yellow-800'
                                        "
                                    >
                                        Payment: {{ order.payment_status }}
                                    </span>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex flex-wrap gap-3">
                                    <button
                                        v-for="action in availableActions"
                                        :key="action.status"
                                        @click="updateStatus(action.status)"
                                        class="px-6 py-3 rounded-lg text-base font-semibold transition-colors shadow-md hover:shadow-lg"
                                        :class="action.class"
                                    >
                                        {{ action.label }}
                                    </button>

                                    <!-- Print Receipt Button (only shown when in_progress) -->
                                    <button
                                        v-if="order.status === 'in_progress'"
                                        @click="printReceipt"
                                        class="px-6 py-3 rounded-lg text-base font-semibold transition-colors shadow-md hover:shadow-lg bg-gray-600 text-white hover:bg-gray-700 flex items-center gap-2"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                        </svg>
                                        Print Receipt
                                    </button>
                                </div>
                            </div>

                            <!-- Tracking Info (for shipping orders) -->
                            <div
                                v-if="order.fulfilment_type === 'shipping' || order.status === 'shipped'"
                                class="mb-6"
                            >
                                <h4
                                    class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2"
                                >
                                    Tracking Information
                                </h4>
                                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 space-y-3">
                                    <!-- Courier Company -->
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                            Courier Company
                                        </label>
                                        <input
                                            v-model="trackingForm.courier_company"
                                            type="text"
                                            placeholder="e.g., Australia Post, DHL, FedEx"
                                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"
                                        />
                                    </div>

                                    <!-- Tracking Number -->
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                            Tracking Number
                                        </label>
                                        <input
                                            v-model="trackingForm.tracking_number"
                                            type="text"
                                            placeholder="e.g., 1234567890"
                                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"
                                        />
                                    </div>

                                    <!-- Save Button -->
                                    <button
                                        @click="saveTrackingInfo"
                                        :disabled="savingTracking"
                                        class="w-full px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                    >
                                        {{ savingTracking ? 'Saving...' : 'Save Tracking Info' }}
                                    </button>

                                    <!-- Display saved tracking info -->
                                    <div v-if="order.tracking_number || order.courier_company" class="pt-3 border-t border-gray-200 dark:border-gray-700">
                                        <p v-if="order.courier_company" class="text-sm text-gray-700 dark:text-gray-300">
                                            <span class="font-medium">Courier:</span> {{ order.courier_company }}
                                        </p>
                                        <p v-if="order.tracking_number" class="text-sm text-gray-700 dark:text-gray-300">
                                            <span class="font-medium">Tracking #:</span> {{ order.tracking_number }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="flex justify-between items-center">
                                <button
                                    v-if="
                                        order.payment_status === 'paid' &&
                                        order.status !== 'refunded'
                                    "
                                    @click="initiateRefund"
                                    class="px-3 py-1.5 text-xs font-medium text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50 hover:border-red-400 hover:text-red-600 transition-colors"
                                >
                                    ⚠️ Refund Order
                                </button>
                                <div v-else></div>
                                <button
                                    @click="closeModal"
                                    class="px-6 py-2.5 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors font-medium"
                                >
                                    Close
                                </button>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>

    <!-- Refund Modal - Custom Implementation -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showRefundModal"
                class="fixed inset-0 z-[60] overflow-y-auto"
                @click.self="() => {}"
            >
                <!-- Overlay -->
                <div class="fixed inset-0 bg-black bg-opacity-50"></div>

                <!-- Modal Container -->
                <div class="flex min-h-full items-center justify-center p-4">
                    <Transition
                        enter-active-class="transition duration-300 ease-out"
                        enter-from-class="opacity-0 scale-95"
                        enter-to-class="opacity-100 scale-100"
                        leave-active-class="transition duration-200 ease-in"
                        leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-95"
                    >
                        <div
                            v-if="showRefundModal"
                            class="relative w-full max-w-md transform overflow-visible rounded-2xl bg-white dark:bg-gray-800 p-6 text-left align-middle shadow-xl min-h-[420px]"
                        >
                            <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-white mb-4">
                                Refund Item
                            </h3>

                            <div class="mb-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                    You are about to refund:
                                </p>
                                <p class="text-base font-medium text-gray-900 dark:text-white">
                                    {{ refundingItem?.product_name }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Amount: {{ refundingItem ? formatCurrency(refundingItem.total_cents) : '' }}
                                </p>
                            </div>

                            <div class="mb-6 pb-56">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Refund Reason <span class="text-red-500">*</span>
                                </label>

                                <!-- Custom Dropdown -->
                                <div class="relative">
                                    <button
                                        @click="toggleRefundDropdown"
                                        type="button"
                                        class="w-full px-3 py-2 text-left border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white bg-white flex items-center justify-between"
                                    >
                                        <span :class="refundReason ? 'text-gray-900 dark:text-white' : 'text-gray-400'">
                                            {{ refundReason || 'Select a reason' }}
                                        </span>
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    <!-- Dropdown Menu -->
                                    <div
                                        v-show="showRefundDropdown"
                                        class="absolute z-[70] w-full mt-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg max-h-60 overflow-auto"
                                    >
                                        <button
                                            v-for="option in refundReasonOptions"
                                            :key="option"
                                            @click="selectRefundReason(option)"
                                            type="button"
                                            class="w-full px-3 py-2 text-left hover:bg-gray-100 dark:hover:bg-gray-600 text-gray-900 dark:text-white transition-colors"
                                            :class="{ 'bg-gray-100 dark:bg-gray-600': refundReason === option }"
                                        >
                                            {{ option }}
                                        </button>
                                    </div>
                                </div>

                                <!-- Custom reason input if "Other" is selected -->
                                <input
                                    v-if="refundReason === 'Other'"
                                    v-model="customRefundReason"
                                    type="text"
                                    placeholder="Please specify the reason"
                                    class="mt-2 w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white"
                                    required
                                />
                            </div>

                            <div class="flex gap-3">
                                <button
                                    @click="closeRefundModal"
                                    :disabled="processingRefund"
                                    class="flex-1 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors font-medium disabled:opacity-50"
                                >
                                    Cancel
                                </button>
                                <button
                                    @click="processRefund"
                                    :disabled="!canProcessRefund || processingRefund"
                                    class="flex-1 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    {{ processingRefund ? 'Processing...' : 'Confirm Refund' }}
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { computed, ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import axios from "axios";
import { useToast } from "vue-toastification";
import {
    TransitionRoot,
    TransitionChild,
    Dialog,
    DialogPanel,
    DialogTitle,
} from "@headlessui/vue";

const toast = useToast();

const props = defineProps({
    isOpen: {
        type: Boolean,
        required: true,
    },
    order: {
        type: Object,
        required: true,
    },
    defaultPickupMinutes: {
        type: Number,
        default: 30,
    },
});

const emit = defineEmits(["close", "status-updated"]);

const closeModal = () => {
    emit("close");
};

// Pickup ETA management
const currentPickupEta = ref(null);
const editableEta = ref('');
const updatingEta = ref(false);

// Helper to convert MySQL datetime to datetime-local format
const toDatetimeLocalFormat = (mysqlDatetime) => {
    if (!mysqlDatetime) return '';
    const date = new Date(mysqlDatetime);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${year}-${month}-${day}T${hours}:${minutes}`;
};

// Initialize pickup ETA when order changes
watch(() => props.order, (newOrder) => {
    if (newOrder && newOrder.pickup_eta) {
        currentPickupEta.value = newOrder.pickup_eta;
    } else {
        currentPickupEta.value = null;
    }
}, { immediate: true });

// Helper function to format Date to MySQL datetime format in local timezone
const formatDateToMySQLDateTime = (date) => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    const seconds = String(date.getSeconds()).padStart(2, '0');
    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
};

// Display ETA: either saved pickup_eta or calculated default
const displayEta = computed(() => {
    if (currentPickupEta.value) {
        return currentPickupEta.value;
    }

    // Calculate default ETA (now + default pickup minutes)
    const now = new Date();
    now.setMinutes(now.getMinutes() + props.defaultPickupMinutes);
    return formatDateToMySQLDateTime(now);
});

// Sync editableEta with displayEta
watch(displayEta, (newEta) => {
    editableEta.value = toDatetimeLocalFormat(newEta);
}, { immediate: true });

// Update ETA from manual input
const updateEtaFromInput = async () => {
    if (!editableEta.value) return;

    // Convert datetime-local to Date object
    const selectedDate = new Date(editableEta.value);
    await savePickupEta(formatDateToMySQLDateTime(selectedDate));
};

// Adjust pickup ETA by adding/subtracting minutes
const adjustPickupEta = async (minutes) => {
    // Use current editable ETA as base
    const currentDate = new Date(editableEta.value);
    currentDate.setMinutes(currentDate.getMinutes() + minutes);

    // Update the input field immediately for visual feedback
    editableEta.value = toDatetimeLocalFormat(formatDateToMySQLDateTime(currentDate));

    await savePickupEta(formatDateToMySQLDateTime(currentDate));
};

// Save pickup ETA to database
const savePickupEta = async (isoDatetime) => {
    updatingEta.value = true;

    try {
        await axios.patch(`/orders/${props.order.id}/pickup-eta`, {
            pickup_eta: isoDatetime,
        });

        // Update local state
        currentPickupEta.value = isoDatetime;

        toast.success("Pickup ETA updated");
    } catch (error) {
        console.error("Failed to update pickup ETA:", error);
        toast.error("Failed to update pickup ETA");
    } finally {
        updatingEta.value = false;
    }
};

const availableActions = computed(() => {
    const actions = [];
    const status = props.order.status;
    const fulfilmentType = props.order.fulfilment_type;

    const statusTransitions = {
        pending: [
            {
                status: "in_progress",
                label: "Start Processing",
                class: "bg-blue-600 text-white hover:bg-blue-700",
            },
            {
                status: "cancelled",
                label: "Cancel Order",
                class: "bg-red-600 text-white hover:bg-red-700",
            },
        ],
        in_progress:
            fulfilmentType === "pickup"
                ? [
                      {
                          status: "ready_for_pickup",
                          label: "Ready for Pickup",
                          class: "bg-green-600 text-white hover:bg-green-700",
                      },
                  ]
                : [
                      {
                          status: "ready",
                          label: "Ready to Pack",
                          class: "bg-purple-600 text-white hover:bg-purple-700",
                      },
                      {
                          status: "cancelled",
                          label: "Cancel Order",
                          class: "bg-red-600 text-white hover:bg-red-700",
                      },
                  ],
        ready: [
            {
                status: "packing",
                label: "Start Packing",
                class: "bg-indigo-600 text-white hover:bg-indigo-700",
            },
        ],
        packing: [
            {
                status: "shipped",
                label: "Mark as Shipped",
                class: "bg-teal-600 text-white hover:bg-teal-700",
            },
        ],
        shipped: [
            {
                status: "delivered",
                label: "Mark as Delivered",
                class: "bg-gray-600 text-white hover:bg-gray-700",
            },
        ],
        ready_for_pickup: [
            {
                status: "picked_up",
                label: "Mark as Picked Up",
                class: "bg-gray-600 text-white hover:bg-gray-700",
            },
        ],
    };

    return statusTransitions[status] || [];
});

const updateStatus = (newStatus) => {
    console.log("Updating status from", props.order.status, "to", newStatus);
    console.log("Order ID:", props.order.id);

    router.put(
        route("orders.status.update", props.order.id),
        { status: newStatus },
        {
            preserveScroll: true,
            onStart: () => {
                console.log("Request started");
            },
            onSuccess: (page) => {
                console.log("Status update successful", page);
                emit("status-updated", newStatus);

                // Auto-print receipt when status changes to in_progress
                if (newStatus === 'in_progress') {
                    setTimeout(() => printReceipt(), 500);
                }
            },
            onError: (errors) => {
                console.error("Status update failed:", errors);
                toast.error(
                    "Failed to update status: " +
                        (errors.status || "Unknown error")
                );
            },
            onFinish: () => {
                console.log("Request finished");
            },
        }
    );
};

const printReceipt = () => {
    const receiptUrl = route('orders.receipt', props.order.id);
    const printWindow = window.open(receiptUrl, '_blank', 'width=800,height=600');

    if (!printWindow || printWindow.closed || typeof printWindow.closed === 'undefined') {
        toast.warning('Please allow pop-ups to print receipts.');
    }
};

const formatCurrency = (cents) => {
    return new Intl.NumberFormat("en-AU", {
        style: "currency",
        currency: "AUD",
    }).format(cents / 100);
};

// Tracking form
const trackingForm = ref({
    tracking_number: '',
    courier_company: ''
});

const savingTracking = ref(false);

// Initialize tracking form when order changes
watch(() => props.order, (newOrder) => {
    if (newOrder) {
        trackingForm.value.tracking_number = newOrder.tracking_number || '';
        trackingForm.value.courier_company = newOrder.courier_company || '';
    }
}, { immediate: true });

// Save tracking information
const saveTrackingInfo = async () => {
    savingTracking.value = true;

    try {
        await axios.put(`/orders/${props.order.id}/shipping`, {
            tracking_number: trackingForm.value.tracking_number,
            courier_company: trackingForm.value.courier_company
        });

        // Update the order object
        props.order.tracking_number = trackingForm.value.tracking_number;
        props.order.courier_company = trackingForm.value.courier_company;

        toast.success('Tracking information saved successfully!');
    } catch (error) {
        console.error('Failed to save tracking info:', error);
        toast.error('Failed to save tracking information. Please try again.');
    } finally {
        savingTracking.value = false;
    }
};

const formatDateTime = (datetime) => {
    return new Date(datetime).toLocaleString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric",
        hour: "numeric",
        minute: "2-digit",
    });
};

const formatStatus = (status) => {
    return status.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

const initiateRefund = () => {
    // Placeholder function for refund functionality
    // This will be implemented in a future update with proper payment gateway integration
    if (
        confirm(
            "Are you sure you want to refund this order? This action cannot be undone."
        )
    ) {
        toast.info(
            "Refund functionality is currently under development. This feature will be available in a future update."
        );
        // Future implementation:
        // router.post(route('orders.refund', props.order.id), {}, {
        //     preserveScroll: true,
        //     onSuccess: () => {
        //         emit('status-updated', 'refunded');
        //     }
        // });
    }
};

// Refund modal state
const showRefundModal = ref(false);
const refundingItem = ref(null);
const refundReason = ref('');
const customRefundReason = ref('');
const processingRefund = ref(false);
const showRefundDropdown = ref(false);
const refundModalRef = ref(null);

const refundReasonOptions = [
    'Out of stock',
    'Customer request',
    'Damaged product',
    'Wrong item sent',
    'Quality issues',
    'Other'
];

const canProcessRefund = computed(() => {
    if (refundReason.value === 'Other') {
        return customRefundReason.value.trim().length > 0;
    }
    return refundReason.value.length > 0;
});

const toggleRefundDropdown = () => {
    showRefundDropdown.value = !showRefundDropdown.value;
};

const selectRefundReason = (reason) => {
    refundReason.value = reason;
    showRefundDropdown.value = false;
};

const openRefundModal = (item) => {
    refundingItem.value = item;
    refundReason.value = '';
    customRefundReason.value = '';
    showRefundDropdown.value = false;
    showRefundModal.value = true;
};

const closeRefundModal = () => {
    showRefundModal.value = false;
    refundingItem.value = null;
    refundReason.value = '';
    customRefundReason.value = '';
    showRefundDropdown.value = false;
};

const processRefund = async () => {
    if (!canProcessRefund.value || !refundingItem.value) {
        return;
    }

    processingRefund.value = true;

    try {
        const finalReason = refundReason.value === 'Other'
            ? customRefundReason.value
            : refundReason.value;

        const response = await axios.post(
            `/orders/${props.order.id}/items/${refundingItem.value.id}/refund`,
            {
                refund_reason: finalReason,
            }
        );

        // Update the item in the order with the response data
        const itemIndex = props.order.items.findIndex(
            (item) => item.id === refundingItem.value.id
        );
        if (itemIndex !== -1 && response.data.item) {
            // Use Object.assign to maintain reactivity
            Object.assign(props.order.items[itemIndex], {
                is_refunded: response.data.item.is_refunded,
                refund_date: response.data.item.refund_date,
                refund_reason: response.data.item.refund_reason,
            });
        }

        toast.success('Item refunded successfully! Customer has been notified via email.');
        closeRefundModal();
        emit('status-updated', 'item-refunded');
    } catch (error) {
        console.error('Failed to process refund:', error);
        toast.error(
            error.response?.data?.message ||
            'Failed to process refund. Please try again.'
        );
    } finally {
        processingRefund.value = false;
    }
};
</script>
