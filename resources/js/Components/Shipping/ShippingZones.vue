<template>
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    Shipping Zones
                </h3>
                <button
                    @click="openCreateModal"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    Add Zone
                </button>
            </div>

            <!-- Zones List -->
            <div v-if="zones.length > 0" class="space-y-4">
                <div
                    v-for="zone in zones"
                    :key="zone.id"
                    class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
                >
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <h4
                                    class="text-base font-semibold text-gray-900 dark:text-white"
                                >
                                    {{ zone.name }}
                                </h4>
                                <span
                                    :class="[
                                        zone.is_active
                                            ? 'bg-green-100 text-green-800'
                                            : 'bg-gray-100 text-gray-800',
                                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                    ]"
                                >
                                    {{ zone.is_active ? "Active" : "Inactive" }}
                                </span>
                            </div>
                            <p
                                v-if="zone.description"
                                class="mt-1 text-sm text-gray-600"
                            >
                                {{ zone.description }}
                            </p>
                            <div class="mt-2 space-y-1">
                                <p
                                    v-if="
                                        zone.countries &&
                                        zone.countries.length > 0
                                    "
                                    class="text-sm text-gray-600"
                                >
                                    <span class="font-medium">Countries:</span>
                                    <span class="ml-2">{{
                                        zone.countries.join(", ")
                                    }}</span>
                                </p>
                                <p
                                    v-if="zone.states && zone.states.length > 0"
                                    class="text-sm text-gray-600"
                                >
                                    <span class="font-medium">States:</span>
                                    <span class="ml-2">{{
                                        zone.states.join(", ")
                                    }}</span>
                                </p>
                                <p
                                    v-if="
                                        zone.postcodes &&
                                        zone.postcodes.length > 0
                                    "
                                    class="text-sm text-gray-600"
                                >
                                    <span class="font-medium">Postcodes:</span>
                                    <span class="ml-2">{{
                                        zone.postcodes.join(", ")
                                    }}</span>
                                </p>
                                <p
                                    v-if="
                                        !zone.countries?.length &&
                                        !zone.states?.length &&
                                        !zone.postcodes?.length
                                    "
                                    class="text-sm text-gray-500 italic"
                                >
                                    No coverage restrictions (all locations)
                                </p>
                            </div>
                            <div
                                v-if="
                                    zone.shipping_methods &&
                                    zone.shipping_methods.length > 0
                                "
                                class="mt-3"
                            >
                                <p
                                    class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-1"
                                >
                                    Shipping Methods:
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    <span
                                        v-for="method in zone.shipping_methods"
                                        :key="method.id"
                                        :class="[
                                            method.is_active
                                                ? 'bg-blue-100 text-blue-700'
                                                : 'bg-gray-100 text-gray-600',
                                            'inline-flex items-center px-2 py-1 rounded text-xs font-medium',
                                        ]"
                                    >
                                        {{ method.name }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex space-x-2 ml-4">
                            <button
                                @click="openEditModal(zone)"
                                class="text-indigo-600 hover:text-indigo-900"
                                title="Edit"
                            >
                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                    />
                                </svg>
                            </button>
                            <button
                                @click="toggleActive(zone)"
                                :class="[
                                    zone.is_active
                                        ? 'text-gray-600 hover:text-gray-900'
                                        : 'text-green-600 hover:text-green-900',
                                ]"
                                :title="
                                    zone.is_active ? 'Deactivate' : 'Activate'
                                "
                            >
                                <svg
                                    v-if="zone.is_active"
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"
                                    />
                                </svg>
                                <svg
                                    v-else
                                    class="h-5 w-5"
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
                            </button>
                            <button
                                @click="deleteZone(zone)"
                                class="text-red-600 hover:text-red-900"
                                title="Delete"
                            >
                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12">
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
                        d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"
                    />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                    No shipping zones
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    Get started by creating a new shipping zone.
                </p>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <TransitionRoot as="template" :show="showModal">
            <Dialog as="div" class="relative z-10" @close="closeModal">
                <TransitionChild
                    as="template"
                    enter="ease-out duration-300"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="ease-in duration-200"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <div
                        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    />
                </TransitionChild>

                <div class="fixed inset-0 z-10 overflow-y-auto">
                    <div
                        class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0"
                    >
                        <TransitionChild
                            as="template"
                            enter="ease-out duration-300"
                            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            enter-to="opacity-100 translate-y-0 sm:scale-100"
                            leave="ease-in duration-200"
                            leave-from="opacity-100 translate-y-0 sm:scale-100"
                            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        >
                            <DialogPanel
                                class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6"
                            >
                                <form @submit.prevent="submitForm">
                                    <div>
                                        <DialogTitle
                                            as="h3"
                                            class="text-lg font-semibold leading-6 text-gray-900 dark:text-white"
                                        >
                                            {{
                                                isEditing
                                                    ? "Edit Shipping Zone"
                                                    : "Create Shipping Zone"
                                            }}
                                        </DialogTitle>
                                        <div class="mt-4 space-y-4">
                                            <!-- Zone Name -->
                                            <div>
                                                <label
                                                    for="name"
                                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                                    >Zone Name *</label
                                                >
                                                <input
                                                    v-model="form.name"
                                                    type="text"
                                                    id="name"
                                                    required
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    placeholder="e.g., Australia Wide, NSW Metro"
                                                />
                                            </div>

                                            <!-- Description -->
                                            <div>
                                                <label
                                                    for="description"
                                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                                    >Description</label
                                                >
                                                <textarea
                                                    v-model="form.description"
                                                    id="description"
                                                    rows="2"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    placeholder="Optional description"
                                                ></textarea>
                                            </div>

                                            <!-- Countries -->
                                            <div>
                                                <label
                                                    for="countries"
                                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                                >
                                                    Countries (2-letter codes,
                                                    comma-separated)
                                                </label>
                                                <input
                                                    v-model="
                                                        form.countries_text
                                                    "
                                                    type="text"
                                                    id="countries"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    placeholder="e.g., AU, NZ, US"
                                                />
                                                <p
                                                    class="mt-1 text-xs text-gray-500"
                                                >
                                                    Leave empty for no country
                                                    restriction
                                                </p>
                                            </div>

                                            <!-- States -->
                                            <div>
                                                <label
                                                    for="states"
                                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                                >
                                                    States/Provinces
                                                    (comma-separated)
                                                </label>
                                                <input
                                                    v-model="form.states_text"
                                                    type="text"
                                                    id="states"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    placeholder="e.g., NSW, VIC, QLD"
                                                />
                                                <p
                                                    class="mt-1 text-xs text-gray-500"
                                                >
                                                    Leave empty for no state
                                                    restriction
                                                </p>
                                            </div>

                                            <!-- Postcodes -->
                                            <div>
                                                <label
                                                    for="postcodes"
                                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                                >
                                                    Postcodes (comma-separated,
                                                    supports ranges and
                                                    wildcards)
                                                </label>
                                                <textarea
                                                    v-model="
                                                        form.postcodes_text
                                                    "
                                                    id="postcodes"
                                                    rows="3"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    placeholder="e.g., 2000, 2001-2099, 20*"
                                                ></textarea>
                                                <p
                                                    class="mt-1 text-xs text-gray-500"
                                                >
                                                    Supports: exact (2000),
                                                    ranges (2000-2099),
                                                    wildcards (20*). Leave empty
                                                    for no postcode restriction.
                                                </p>
                                            </div>

                                            <!-- Priority -->
                                            <div>
                                                <label
                                                    for="priority"
                                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                                >
                                                    Priority (higher number =
                                                    higher priority)
                                                </label>
                                                <input
                                                    v-model.number="
                                                        form.priority
                                                    "
                                                    type="number"
                                                    id="priority"
                                                    min="0"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                />
                                            </div>

                                            <!-- Is Active -->
                                            <div class="flex items-center">
                                                <input
                                                    v-model="form.is_active"
                                                    type="checkbox"
                                                    id="is_active"
                                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                />
                                                <label
                                                    for="is_active"
                                                    class="ml-2 block text-sm text-gray-700 dark:text-gray-300"
                                                >
                                                    Zone is active
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3"
                                    >
                                        <button
                                            type="submit"
                                            :disabled="processing"
                                            class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 sm:col-start-2 disabled:opacity-50"
                                        >
                                            {{
                                                processing
                                                    ? "Saving..."
                                                    : isEditing
                                                    ? "Update"
                                                    : "Create"
                                            }}
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
    </div>
</template>

<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionChild,
    TransitionRoot,
} from "@headlessui/vue";

const props = defineProps({
    zones: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(["refresh"]);

const showModal = ref(false);
const isEditing = ref(false);
const processing = ref(false);

const form = ref({
    id: null,
    name: "",
    description: "",
    countries_text: "",
    states_text: "",
    postcodes_text: "",
    is_active: true,
    priority: 0,
});

const openCreateModal = () => {
    isEditing.value = false;
    resetForm();
    showModal.value = true;
};

const openEditModal = (zone) => {
    isEditing.value = true;
    form.value = {
        id: zone.id,
        name: zone.name || "",
        description: zone.description || "",
        countries_text: zone.countries ? zone.countries.join(", ") : "",
        states_text: zone.states ? zone.states.join(", ") : "",
        postcodes_text: zone.postcodes ? zone.postcodes.join(", ") : "",
        is_active: zone.is_active !== undefined ? zone.is_active : true,
        priority: zone.priority || 0,
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
        name: "",
        description: "",
        countries_text: "",
        states_text: "",
        postcodes_text: "",
        is_active: true,
        priority: 0,
    };
};

const submitForm = () => {
    processing.value = true;

    // Convert comma-separated text to arrays
    const data = {
        name: form.value.name,
        description: form.value.description || null,
        countries: form.value.countries_text
            ? form.value.countries_text
                  .split(",")
                  .map((s) => s.trim().toUpperCase())
                  .filter(Boolean)
            : null,
        states: form.value.states_text
            ? form.value.states_text
                  .split(",")
                  .map((s) => s.trim())
                  .filter(Boolean)
            : null,
        postcodes: form.value.postcodes_text
            ? form.value.postcodes_text
                  .split(",")
                  .map((s) => s.trim())
                  .filter(Boolean)
            : null,
        is_active: form.value.is_active,
        priority: form.value.priority || 0,
    };

    const url = isEditing.value
        ? `/shipping/zones/${form.value.id}`
        : "/shipping/zones";

    const method = isEditing.value ? "put" : "post";

    router[method](url, data, {
        onSuccess: () => {
            closeModal();
            emit("refresh");
        },
        onError: (errors) => {
            console.error("Form errors:", errors);
            alert("Error saving zone. Please check the form and try again.");
        },
        onFinish: () => {
            processing.value = false;
        },
    });
};

const toggleActive = (zone) => {
    if (
        confirm(
            `Are you sure you want to ${
                zone.is_active ? "deactivate" : "activate"
            } this zone?`
        )
    ) {
        router.put(
            `/shipping/zones/${zone.id}`,
            {
                ...zone,
                is_active: !zone.is_active,
            },
            {
                onSuccess: () => {
                    emit("refresh");
                },
            }
        );
    }
};

const deleteZone = (zone) => {
    if (
        confirm(
            "Are you sure you want to delete this shipping zone? This action cannot be undone."
        )
    ) {
        router.delete(`/shipping/zones/${zone.id}`, {
            onSuccess: () => {
                emit("refresh");
            },
        });
    }
};
</script>
