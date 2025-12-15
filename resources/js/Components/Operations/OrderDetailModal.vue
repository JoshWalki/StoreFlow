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
                                        <p class="text-sm text-gray-900">
                                            {{
                                                order.pickup_time
                                                    ? formatDateTime(
                                                          order.pickup_time
                                                      )
                                                    : "TBD"
                                            }}
                                        </p>
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
                                            </tr>
                                        </thead>
                                        <tbody
                                            class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700"
                                        >
                                            <tr
                                                v-for="item in order.items"
                                                :key="item.id"
                                            >
                                                <td
                                                    class="px-4 py-3 text-sm text-gray-900 dark:text-white"
                                                >
                                                    <div>{{ item.product_name }}</div>
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
                                                </td>
                                                <td
                                                    class="px-4 py-3 text-sm text-gray-900 dark:text-white text-center"
                                                >
                                                    {{ item.quantity }}
                                                </td>
                                                <td
                                                    class="px-4 py-3 text-sm text-gray-900 dark:text-white text-right"
                                                >
                                                    {{
                                                        formatCurrency(
                                                            item.price_cents
                                                        )
                                                    }}
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
});

const emit = defineEmits(["close", "status-updated"]);

const closeModal = () => {
    emit("close");
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
</script>
