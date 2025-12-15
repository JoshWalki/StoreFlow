<template>
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Shipping Methods</h3>
                <button
                    @click="openCreateModal"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    Add Method
                </button>
            </div>

            <!-- Methods List -->
            <div v-if="methods.length > 0" class="space-y-4">
                <div
                    v-for="method in methods"
                    :key="method.id"
                    class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
                >
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3">
                                <h4 class="text-base font-semibold text-gray-900 dark:text-white">{{ method.name }}</h4>
                                <span
                                    :class="[
                                        method.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800',
                                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium'
                                    ]"
                                >
                                    {{ method.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <p v-if="method.description" class="mt-1 text-sm text-gray-600">{{ method.description }}</p>

                            <div class="mt-3 grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs font-medium text-gray-500">Zone</p>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ getZoneName(method.shipping_zone_id) }}</p>
                                </div>
                                <div v-if="method.carrier">
                                    <p class="text-xs font-medium text-gray-500">Carrier</p>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ method.carrier }}</p>
                                </div>
                                <div v-if="method.service_code">
                                    <p class="text-xs font-medium text-gray-500">Service Code</p>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ method.service_code }}</p>
                                </div>
                                <div v-if="method.min_delivery_days || method.max_delivery_days">
                                    <p class="text-xs font-medium text-gray-500">Estimated Delivery</p>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                        <template v-if="method.min_delivery_days && method.max_delivery_days">
                                            {{ method.min_delivery_days }}-{{ method.max_delivery_days }} days
                                        </template>
                                        <template v-else-if="method.min_delivery_days">
                                            {{ method.min_delivery_days }}+ days
                                        </template>
                                        <template v-else>
                                            Up to {{ method.max_delivery_days }} days
                                        </template>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="flex space-x-2 ml-4">
                            <button
                                @click="toggleActive(method)"
                                :class="[
                                    method.is_active ? 'text-gray-600 hover:text-gray-900' : 'text-green-600 hover:text-green-900'
                                ]"
                                :title="method.is_active ? 'Deactivate' : 'Activate'"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path v-if="method.is_active" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </button>
                            <button
                                @click="openEditModal(method)"
                                class="text-indigo-600 hover:text-indigo-900"
                                title="Edit"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <button
                                @click="deleteMethod(method)"
                                class="text-red-600 hover:text-red-900"
                                title="Delete"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Shipping Rates Section -->
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <div class="flex justify-between items-center mb-3">
                            <h5 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Shipping Rates</h5>
                            <button
                                @click="openCreateRateModal(method)"
                                class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700"
                            >
                                Add Rate
                            </button>
                        </div>

                        <div v-if="method.shipping_rates && method.shipping_rates.length > 0" class="space-y-2">
                            <div
                                v-for="rate in method.shipping_rates"
                                :key="rate.id"
                                class="bg-gray-50 rounded p-3 text-sm"
                            >
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2">
                                            <span class="font-medium text-gray-900 dark:text-white">{{ rate.name }}</span>
                                            <span
                                                :class="[
                                                    rate.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800',
                                                    'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium'
                                                ]"
                                            >
                                                {{ rate.is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
                                        <div class="mt-1 text-xs text-gray-600">
                                            <span class="font-medium">Model:</span> {{ formatPricingModel(rate.pricing_model) }}
                                        </div>
                                        <div v-if="rate.pricing_model === 'flat'" class="mt-1 text-xs text-gray-600">
                                            <span class="font-medium">Cost:</span> {{ formatCurrency(rate.flat_rate_cents) }}
                                        </div>
                                        <div v-if="rate.free_shipping_threshold_cents" class="mt-1 text-xs text-green-600">
                                            Free shipping over {{ formatCurrency(rate.free_shipping_threshold_cents) }}
                                        </div>
                                    </div>
                                    <div class="flex space-x-2 ml-4">
                                        <button
                                            @click="openEditRateModal(method, rate)"
                                            class="text-indigo-600 hover:text-indigo-900"
                                            title="Edit Rate"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button
                                            @click="deleteRate(rate)"
                                            class="text-red-600 hover:text-red-900"
                                            title="Delete Rate"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-xs text-gray-500 italic">
                            No rates configured. Add a rate to enable shipping for this method.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No shipping methods</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a new shipping method.</p>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <TransitionRoot as="template" :show="showModal">
            <Dialog as="div" class="relative z-10" @close="closeModal">
                <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
                </TransitionChild>

                <div class="fixed inset-0 z-10 overflow-y-auto">
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200" leave-from="opacity-100 translate-y-0 sm:scale-100" leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                            <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                                <form @submit.prevent="submitForm">
                                    <div>
                                        <DialogTitle as="h3" class="text-lg font-semibold leading-6 text-gray-900 dark:text-white">
                                            {{ isEditing ? 'Edit Shipping Method' : 'Create Shipping Method' }}
                                        </DialogTitle>
                                        <div class="mt-4 space-y-4">
                                            <!-- Zone Selection -->
                                            <div>
                                                <label for="zone_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Shipping Zone *</label>
                                                <select
                                                    v-model="form.zone_id"
                                                    id="zone_id"
                                                    required
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                >
                                                    <option value="">Select a zone...</option>
                                                    <option v-for="zone in zones" :key="zone.id" :value="zone.id">
                                                        {{ zone.name }}
                                                    </option>
                                                </select>
                                            </div>

                                            <!-- Method Name -->
                                            <div>
                                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Method Name *</label>
                                                <input
                                                    v-model="form.name"
                                                    type="text"
                                                    id="name"
                                                    required
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    placeholder="e.g., Standard Shipping, Express"
                                                />
                                            </div>

                                            <!-- Description -->
                                            <div>
                                                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                                                <textarea
                                                    v-model="form.description"
                                                    id="description"
                                                    rows="2"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    placeholder="Optional description"
                                                ></textarea>
                                            </div>

                                            <!-- Carrier -->
                                            <div>
                                                <label for="carrier" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Carrier</label>
                                                <input
                                                    v-model="form.carrier"
                                                    type="text"
                                                    id="carrier"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    placeholder="e.g., AusPost, FedEx, UPS"
                                                />
                                            </div>

                                            <!-- Service Code -->
                                            <div>
                                                <label for="service_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Service Code</label>
                                                <input
                                                    v-model="form.service_code"
                                                    type="text"
                                                    id="service_code"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    placeholder="e.g., express, standard"
                                                />
                                            </div>

                                            <!-- Delivery Days -->
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label for="min_delivery_days" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Min Delivery Days</label>
                                                    <input
                                                        v-model.number="form.min_delivery_days"
                                                        type="number"
                                                        id="min_delivery_days"
                                                        min="0"
                                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    />
                                                </div>
                                                <div>
                                                    <label for="max_delivery_days" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max Delivery Days</label>
                                                    <input
                                                        v-model.number="form.max_delivery_days"
                                                        type="number"
                                                        id="max_delivery_days"
                                                        min="0"
                                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    />
                                                </div>
                                            </div>

                                            <!-- Display Order -->
                                            <div>
                                                <label for="display_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Display Order</label>
                                                <input
                                                    v-model.number="form.display_order"
                                                    type="number"
                                                    id="display_order"
                                                    min="0"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                />
                                                <p class="mt-1 text-xs text-gray-500">Lower numbers appear first</p>
                                            </div>

                                            <!-- Is Active -->
                                            <div class="flex items-center">
                                                <input
                                                    v-model="form.is_active"
                                                    type="checkbox"
                                                    id="is_active"
                                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                />
                                                <label for="is_active" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                                    Method is active
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                                        <button
                                            type="submit"
                                            :disabled="processing"
                                            class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 sm:col-start-2 disabled:opacity-50"
                                        >
                                            {{ processing ? 'Saving...' : (isEditing ? 'Update' : 'Create') }}
                                        </button>
                                        <button
                                            type="button"
                                            @click="closeModal"
                                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-300 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 sm:col-start-1 sm:mt-0"
                                        >
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>

        <!-- Rate Create/Edit Modal -->
        <TransitionRoot as="template" :show="showRateModal">
            <Dialog as="div" class="relative z-10" @close="closeRateModal">
                <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
                </TransitionChild>

                <div class="fixed inset-0 z-10 overflow-y-auto">
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200" leave-from="opacity-100 translate-y-0 sm:scale-100" leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                            <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:p-6">
                                <form @submit.prevent="submitRateForm">
                                    <div>
                                        <DialogTitle as="h3" class="text-lg font-semibold leading-6 text-gray-900 dark:text-white">
                                            {{ isEditingRate ? 'Edit Shipping Rate' : 'Create Shipping Rate' }}
                                        </DialogTitle>
                                        <div class="mt-4 space-y-4">
                                            <!-- Rate Name -->
                                            <div>
                                                <label for="rate_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Rate Name *</label>
                                                <input
                                                    v-model="rateForm.name"
                                                    type="text"
                                                    id="rate_name"
                                                    required
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    placeholder="e.g., Standard Rate, Express Rate"
                                                />
                                            </div>

                                            <!-- Pricing Model -->
                                            <div>
                                                <label for="pricing_model" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pricing Model *</label>
                                                <select
                                                    v-model="rateForm.pricing_model"
                                                    id="pricing_model"
                                                    required
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                >
                                                    <option value="">Select pricing model...</option>
                                                    <option value="flat">Flat Rate</option>
                                                    <option value="weight_based">Weight Based</option>
                                                    <option value="cart_total_based">Cart Total Based</option>
                                                    <option value="item_count">Item Count Based</option>
                                                </select>
                                            </div>

                                            <!-- Flat Rate Fields -->
                                            <div v-if="rateForm.pricing_model === 'flat'">
                                                <label for="flat_rate" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Flat Rate (AUD) *</label>
                                                <input
                                                    v-model.number="rateForm.flat_rate"
                                                    type="number"
                                                    id="flat_rate"
                                                    step="0.01"
                                                    min="0"
                                                    required
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    placeholder="10.00"
                                                />
                                            </div>

                                            <!-- Weight Based Fields -->
                                            <div v-if="rateForm.pricing_model === 'weight_based'" class="space-y-3">
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <label for="min_weight" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Min Weight (kg)</label>
                                                        <input
                                                            v-model.number="rateForm.min_weight"
                                                            type="number"
                                                            id="min_weight"
                                                            step="0.01"
                                                            min="0"
                                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                        />
                                                    </div>
                                                    <div>
                                                        <label for="max_weight" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max Weight (kg)</label>
                                                        <input
                                                            v-model.number="rateForm.max_weight"
                                                            type="number"
                                                            id="max_weight"
                                                            step="0.01"
                                                            min="0"
                                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                        />
                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <label for="base_rate" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Base Rate (AUD)</label>
                                                        <input
                                                            v-model.number="rateForm.base_weight_rate"
                                                            type="number"
                                                            id="base_rate"
                                                            step="0.01"
                                                            min="0"
                                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                        />
                                                    </div>
                                                    <div>
                                                        <label for="weight_rate" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Per kg Rate (AUD)</label>
                                                        <input
                                                            v-model.number="rateForm.weight_rate"
                                                            type="number"
                                                            id="weight_rate"
                                                            step="0.01"
                                                            min="0"
                                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                        />
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Cart Total Based Fields -->
                                            <div v-if="rateForm.pricing_model === 'cart_total_based'" class="space-y-3">
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <label for="min_cart_total" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Min Cart Total (AUD)</label>
                                                        <input
                                                            v-model.number="rateForm.min_cart_total"
                                                            type="number"
                                                            id="min_cart_total"
                                                            step="0.01"
                                                            min="0"
                                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                        />
                                                    </div>
                                                    <div>
                                                        <label for="max_cart_total" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max Cart Total (AUD)</label>
                                                        <input
                                                            v-model.number="rateForm.max_cart_total"
                                                            type="number"
                                                            id="max_cart_total"
                                                            step="0.01"
                                                            min="0"
                                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                        />
                                                    </div>
                                                </div>
                                                <div>
                                                    <label for="cart_rate" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Shipping Cost (AUD) *</label>
                                                    <input
                                                        v-model.number="rateForm.cart_total_rate"
                                                        type="number"
                                                        id="cart_rate"
                                                        step="0.01"
                                                        min="0"
                                                        required
                                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    />
                                                </div>
                                            </div>

                                            <!-- Item Count Based Fields -->
                                            <div v-if="rateForm.pricing_model === 'item_count'" class="space-y-3">
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <label for="min_items" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Min Items</label>
                                                        <input
                                                            v-model.number="rateForm.min_items"
                                                            type="number"
                                                            id="min_items"
                                                            min="0"
                                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                        />
                                                    </div>
                                                    <div>
                                                        <label for="max_items" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max Items</label>
                                                        <input
                                                            v-model.number="rateForm.max_items"
                                                            type="number"
                                                            id="max_items"
                                                            min="0"
                                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                        />
                                                    </div>
                                                </div>
                                                <div>
                                                    <label for="item_rate" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Per Item Rate (AUD) *</label>
                                                    <input
                                                        v-model.number="rateForm.item_rate"
                                                        type="number"
                                                        id="item_rate"
                                                        step="0.01"
                                                        min="0"
                                                        required
                                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    />
                                                </div>
                                            </div>

                                            <!-- Free Shipping Threshold -->
                                            <div>
                                                <label for="free_shipping" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Free Shipping Threshold (AUD)</label>
                                                <input
                                                    v-model.number="rateForm.free_shipping_threshold"
                                                    type="number"
                                                    id="free_shipping"
                                                    step="0.01"
                                                    min="0"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    placeholder="e.g., 100.00"
                                                />
                                                <p class="mt-1 text-xs text-gray-500">Orders above this amount get free shipping</p>
                                            </div>

                                            <!-- Is Active -->
                                            <div class="flex items-center">
                                                <input
                                                    v-model="rateForm.is_active"
                                                    type="checkbox"
                                                    id="rate_is_active"
                                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                />
                                                <label for="rate_is_active" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                                    Rate is active
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                                        <button
                                            type="submit"
                                            :disabled="processingRate"
                                            class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 sm:col-start-2 disabled:opacity-50"
                                        >
                                            {{ processingRate ? 'Saving...' : (isEditingRate ? 'Update' : 'Create') }}
                                        </button>
                                        <button
                                            type="button"
                                            @click="closeRateModal"
                                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-300 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 sm:col-start-1 sm:mt-0"
                                        >
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';

const toast = useToast();

const props = defineProps({
    methods: {
        type: Array,
        required: true,
    },
    zones: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(['refresh']);

const showModal = ref(false);
const isEditing = ref(false);
const processing = ref(false);

const form = ref({
    id: null,
    zone_id: '',
    name: '',
    description: '',
    carrier: '',
    service_code: '',
    min_delivery_days: null,
    max_delivery_days: null,
    display_order: 0,
    is_active: true,
});

const openCreateModal = () => {
    isEditing.value = false;
    resetForm();
    showModal.value = true;
};

const openEditModal = (method) => {
    isEditing.value = true;
    form.value = {
        id: method.id,
        zone_id: method.shipping_zone_id,
        name: method.name || '',
        description: method.description || '',
        carrier: method.carrier || '',
        service_code: method.service_code || '',
        min_delivery_days: method.min_delivery_days,
        max_delivery_days: method.max_delivery_days,
        display_order: method.display_order || 0,
        is_active: method.is_active !== undefined ? method.is_active : true,
    };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    resetForm();
};

const resetForm = () => {
    form.value = {
        id: null,
        zone_id: '',
        name: '',
        description: '',
        carrier: '',
        service_code: '',
        min_delivery_days: null,
        max_delivery_days: null,
        display_order: 0,
        is_active: true,
    };
};

const submitForm = () => {
    processing.value = true;

    const data = {
        zone_id: form.value.zone_id,
        name: form.value.name,
        description: form.value.description || null,
        carrier: form.value.carrier || null,
        service_code: form.value.service_code || null,
        min_delivery_days: form.value.min_delivery_days,
        max_delivery_days: form.value.max_delivery_days,
        display_order: form.value.display_order || 0,
        is_active: form.value.is_active,
    };

    const url = isEditing.value
        ? `/shipping/methods/${form.value.id}`
        : '/shipping/methods';

    const method = isEditing.value ? 'put' : 'post';

    router[method](url, data, {
        onSuccess: () => {
            closeModal();
            emit('refresh');
        },
        onError: (errors) => {
            console.error('Form errors:', errors);
            toast.error('Error saving method. Please check the form and try again.');
        },
        onFinish: () => {
            processing.value = false;
        },
    });
};

const toggleActive = (method) => {
    if (confirm(`Are you sure you want to ${method.is_active ? 'deactivate' : 'activate'} this method?`)) {
        router.put(`/shipping/methods/${method.id}`, {
            ...method,
            zone_id: method.shipping_zone_id,
            is_active: !method.is_active,
        }, {
            onSuccess: () => {
                emit('refresh');
            },
        });
    }
};

const deleteMethod = (method) => {
    if (confirm('Are you sure you want to delete this shipping method? This action cannot be undone.')) {
        router.delete(`/shipping/methods/${method.id}`, {
            onSuccess: () => {
                emit('refresh');
            },
        });
    }
};

const getZoneName = (zoneId) => {
    const zone = props.zones.find(z => z.id === zoneId);
    return zone ? zone.name : 'Unknown Zone';
};

// Rate modal state
const showRateModal = ref(false);
const isEditingRate = ref(false);
const processingRate = ref(false);

const rateForm = ref({
    id: null,
    shipping_method_id: null,
    name: '',
    pricing_model: '',
    flat_rate: null,
    min_weight: null,
    max_weight: null,
    weight_rate: null,
    base_weight_rate: null,
    min_cart_total: null,
    max_cart_total: null,
    cart_total_rate: null,
    min_items: null,
    max_items: null,
    item_rate: null,
    free_shipping_threshold: null,
    is_active: true,
});

// Rate modal methods
const openCreateRateModal = (method) => {
    isEditingRate.value = false;
    resetRateForm();
    rateForm.value.shipping_method_id = method.id;
    showRateModal.value = true;
};

const openEditRateModal = (method, rate) => {
    isEditingRate.value = true;
    rateForm.value = {
        id: rate.id,
        shipping_method_id: method.id,
        name: rate.name,
        pricing_model: rate.pricing_model,
        flat_rate: rate.flat_rate_cents ? rate.flat_rate_cents / 100 : null,
        min_weight: rate.min_weight_grams ? rate.min_weight_grams / 1000 : null,
        max_weight: rate.max_weight_grams ? rate.max_weight_grams / 1000 : null,
        weight_rate: rate.weight_rate_cents ? rate.weight_rate_cents / 100 : null,
        base_weight_rate: rate.base_weight_rate_cents ? rate.base_weight_rate_cents / 100 : null,
        min_cart_total: rate.min_cart_total_cents ? rate.min_cart_total_cents / 100 : null,
        max_cart_total: rate.max_cart_total_cents ? rate.max_cart_total_cents / 100 : null,
        cart_total_rate: rate.cart_total_rate_cents ? rate.cart_total_rate_cents / 100 : null,
        min_items: rate.min_items,
        max_items: rate.max_items,
        item_rate: rate.item_rate_cents ? rate.item_rate_cents / 100 : null,
        free_shipping_threshold: rate.free_shipping_threshold_cents ? rate.free_shipping_threshold_cents / 100 : null,
        is_active: rate.is_active,
    };
    showRateModal.value = true;
};

const closeRateModal = () => {
    showRateModal.value = false;
    resetRateForm();
};

const resetRateForm = () => {
    rateForm.value = {
        id: null,
        shipping_method_id: null,
        name: '',
        pricing_model: '',
        flat_rate: null,
        min_weight: null,
        max_weight: null,
        weight_rate: null,
        base_weight_rate: null,
        min_cart_total: null,
        max_cart_total: null,
        cart_total_rate: null,
        min_items: null,
        max_items: null,
        item_rate: null,
        free_shipping_threshold: null,
        is_active: true,
    };
};

const submitRateForm = () => {
    processingRate.value = true;

    // Convert AUD to cents
    const data = {
        shipping_method_id: rateForm.value.shipping_method_id,
        name: rateForm.value.name,
        pricing_model: rateForm.value.pricing_model,
        flat_rate_cents: rateForm.value.flat_rate ? Math.round(rateForm.value.flat_rate * 100) : null,
        min_weight_grams: rateForm.value.min_weight ? Math.round(rateForm.value.min_weight * 1000) : null,
        max_weight_grams: rateForm.value.max_weight ? Math.round(rateForm.value.max_weight * 1000) : null,
        weight_rate_cents: rateForm.value.weight_rate ? Math.round(rateForm.value.weight_rate * 100) : null,
        base_weight_rate_cents: rateForm.value.base_weight_rate ? Math.round(rateForm.value.base_weight_rate * 100) : null,
        min_cart_total_cents: rateForm.value.min_cart_total ? Math.round(rateForm.value.min_cart_total * 100) : null,
        max_cart_total_cents: rateForm.value.max_cart_total ? Math.round(rateForm.value.max_cart_total * 100) : null,
        cart_total_rate_cents: rateForm.value.cart_total_rate ? Math.round(rateForm.value.cart_total_rate * 100) : null,
        min_items: rateForm.value.min_items,
        max_items: rateForm.value.max_items,
        item_rate_cents: rateForm.value.item_rate ? Math.round(rateForm.value.item_rate * 100) : null,
        free_shipping_threshold_cents: rateForm.value.free_shipping_threshold ? Math.round(rateForm.value.free_shipping_threshold * 100) : null,
        is_active: rateForm.value.is_active,
    };

    const url = isEditingRate.value
        ? `/shipping/rates/${rateForm.value.id}`
        : '/shipping/rates';

    const method = isEditingRate.value ? 'put' : 'post';

    router[method](url, data, {
        onSuccess: () => {
            closeRateModal();
            emit('refresh');
        },
        onError: (errors) => {
            console.error('Form errors:', errors);
            toast.error('Error saving rate. Please check the form and try again.');
        },
        onFinish: () => {
            processingRate.value = false;
        },
    });
};

const deleteRate = (rate) => {
    if (confirm('Are you sure you want to delete this shipping rate? This action cannot be undone.')) {
        router.delete(`/shipping/rates/${rate.id}`, {
            onSuccess: () => {
                emit('refresh');
            },
        });
    }
};

const formatPricingModel = (model) => {
    const models = {
        'flat': 'Flat Rate',
        'weight_based': 'Weight Based',
        'cart_total_based': 'Cart Total Based',
        'item_count': 'Item Count',
    };
    return models[model] || model;
};

const formatCurrency = (cents) => {
    if (!cents) return 'A$0.00';
    return new Intl.NumberFormat('en-AU', {
        style: 'currency',
        currency: 'AUD',
    }).format(cents / 100);
};
</script>
