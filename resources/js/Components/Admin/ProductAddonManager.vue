<template>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">Product Addons</h3>
            <button
                type="button"
                @click="showAddonModal = true"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Addon
            </button>
        </div>

        <!-- Addon List -->
        <div v-if="localAddons.length > 0" class="space-y-3">
            <div
                v-for="(addon, index) in localAddons"
                :key="index"
                class="bg-gray-50 border border-gray-200 rounded-lg p-4"
            >
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h4 class="font-medium text-gray-900">{{ addon.name }}</h4>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                :class="addon.is_required ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'"
                            >
                                {{ addon.is_required ? 'Required' : 'Optional' }}
                            </span>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800"
                            >
                                {{ addon.selection_type === 'single' ? 'Single Select' : 'Multi Select' }}
                            </span>
                        </div>
                        <p v-if="addon.description" class="text-sm text-gray-600 mb-3">{{ addon.description }}</p>

                        <!-- Addon Options -->
                        <div class="space-y-2">
                            <div
                                v-for="(option, optIndex) in addon.options"
                                :key="optIndex"
                                class="flex items-center justify-between bg-white border border-gray-200 rounded px-3 py-2"
                            >
                                <div class="flex items-center gap-2">
                                    <svg
                                        v-if="option.is_default"
                                        class="w-4 h-4 text-green-600"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                    <span class="text-sm font-medium text-gray-900">{{ option.name }}</span>
                                </div>
                                <span class="text-sm text-gray-600">
                                    {{ option.price_adjustment > 0 ? '+' : '' }}{{ formatCurrency(option.price_adjustment) }}
                                </span>
                            </div>
                        </div>

                        <!-- Min/Max Selections -->
                        <div v-if="addon.selection_type === 'multiple'" class="mt-3 flex gap-4 text-xs text-gray-600">
                            <span v-if="addon.min_selections">Min: {{ addon.min_selections }}</span>
                            <span v-if="addon.max_selections">Max: {{ addon.max_selections }}</span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2 ml-4">
                        <button
                            type="button"
                            @click="editAddon(index)"
                            class="p-2 text-blue-600 hover:bg-blue-50 rounded transition-colors"
                            title="Edit addon"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                        <button
                            type="button"
                            @click="removeAddon(index)"
                            class="p-2 text-red-600 hover:bg-red-50 rounded transition-colors"
                            title="Remove addon"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No addons</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by adding an addon to this product.</p>
        </div>

        <!-- Addon Modal -->
        <div
            v-if="showAddonModal"
            class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50 p-4"
            @click.self="closeAddonModal"
        >
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-semibold text-gray-900">
                            {{ editingIndex !== null ? 'Edit Addon' : 'Add New Addon' }}
                        </h3>
                        <button
                            type="button"
                            @click="closeAddonModal"
                            class="text-gray-400 hover:text-gray-600 transition-colors"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form @submit.prevent="saveAddon" class="space-y-6">
                        <!-- Addon Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Addon Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="currentAddon.name"
                                type="text"
                                required
                                placeholder="e.g., Size, Toppings, Extras"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                            />
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea
                                v-model="currentAddon.description"
                                rows="2"
                                placeholder="Optional description for customers"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                            />
                        </div>

                        <!-- Selection Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Selection Type <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer transition-colors"
                                    :class="currentAddon.selection_type === 'single' ? 'border-blue-600 bg-blue-50' : 'border-gray-300 hover:bg-gray-50'"
                                >
                                    <input
                                        v-model="currentAddon.selection_type"
                                        type="radio"
                                        value="single"
                                        class="h-4 w-4 text-blue-600"
                                    />
                                    <span class="ml-2 text-sm font-medium">Single Select</span>
                                </label>
                                <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer transition-colors"
                                    :class="currentAddon.selection_type === 'multiple' ? 'border-blue-600 bg-blue-50' : 'border-gray-300 hover:bg-gray-50'"
                                >
                                    <input
                                        v-model="currentAddon.selection_type"
                                        type="radio"
                                        value="multiple"
                                        class="h-4 w-4 text-blue-600"
                                    />
                                    <span class="ml-2 text-sm font-medium">Multi Select</span>
                                </label>
                            </div>
                        </div>

                        <!-- Required -->
                        <div>
                            <label class="flex items-center">
                                <input
                                    v-model="currentAddon.is_required"
                                    type="checkbox"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                />
                                <span class="ml-2 text-sm font-medium text-gray-700">Required (customers must make a selection)</span>
                            </label>
                        </div>

                        <!-- Min/Max Selections for Multiple -->
                        <div v-if="currentAddon.selection_type === 'multiple'" class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Selections</label>
                                <input
                                    v-model.number="currentAddon.min_selections"
                                    type="number"
                                    min="0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Maximum Selections</label>
                                <input
                                    v-model.number="currentAddon.max_selections"
                                    type="number"
                                    min="1"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>
                        </div>

                        <!-- Addon Options -->
                        <div>
                            <div class="flex justify-between items-center mb-3">
                                <label class="block text-sm font-medium text-gray-700">
                                    Options <span class="text-red-500">*</span>
                                </label>
                                <button
                                    type="button"
                                    @click="addOption"
                                    class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                                >
                                    + Add Option
                                </button>
                            </div>

                            <div class="space-y-3">
                                <div
                                    v-for="(option, index) in currentAddon.options"
                                    :key="index"
                                    class="flex gap-3 items-start bg-gray-50 p-3 rounded-lg"
                                >
                                    <div class="flex-1 grid grid-cols-2 gap-3">
                                        <input
                                            v-model="option.name"
                                            type="text"
                                            placeholder="Option name"
                                            required
                                            class="px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                        />
                                        <div class="relative">
                                            <span class="absolute left-3 top-2 text-gray-600">$</span>
                                            <input
                                                v-model.number="option.price_adjustment"
                                                type="number"
                                                step="0.01"
                                                placeholder="0.00"
                                                class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                            />
                                        </div>
                                    </div>
                                    <label class="flex items-center pt-2">
                                        <input
                                            v-model="option.is_default"
                                            type="checkbox"
                                            @change="handleDefaultChange(index)"
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                        />
                                        <span class="ml-1 text-xs text-gray-600">Default</span>
                                    </label>
                                    <button
                                        type="button"
                                        @click="removeOption(index)"
                                        class="p-2 text-red-600 hover:bg-red-50 rounded transition-colors"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <p v-if="currentAddon.options.length === 0" class="text-sm text-gray-500 mt-2">
                                Add at least one option
                            </p>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end gap-3 pt-4 border-t">
                            <button
                                type="button"
                                @click="closeAddonModal"
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
                                :disabled="!isAddonValid"
                            >
                                {{ editingIndex !== null ? 'Update Addon' : 'Add Addon' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
    addons: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['update:addons']);

const localAddons = ref([...props.addons]);
const showAddonModal = ref(false);
const editingIndex = ref(null);
const currentAddon = ref({
    name: '',
    description: '',
    selection_type: 'single',
    is_required: false,
    min_selections: null,
    max_selections: null,
    options: [],
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-AU', {
        style: 'currency',
        currency: 'AUD',
    }).format(amount);
};

const isAddonValid = computed(() => {
    return currentAddon.value.name.trim() !== '' && currentAddon.value.options.length > 0;
});

const addOption = () => {
    currentAddon.value.options.push({
        name: '',
        price_adjustment: 0,
        is_default: false,
    });
};

const removeOption = (index) => {
    currentAddon.value.options.splice(index, 1);
};

const handleDefaultChange = (index) => {
    // For single select, only one option can be default
    if (currentAddon.value.selection_type === 'single') {
        currentAddon.value.options.forEach((option, i) => {
            if (i !== index) {
                option.is_default = false;
            }
        });
    }
};

const editAddon = (index) => {
    editingIndex.value = index;
    currentAddon.value = JSON.parse(JSON.stringify(localAddons.value[index]));
    showAddonModal.value = true;
};

const removeAddon = (index) => {
    if (confirm('Are you sure you want to remove this addon?')) {
        localAddons.value.splice(index, 1);
        emit('update:addons', localAddons.value);
    }
};

const saveAddon = () => {
    if (!isAddonValid.value) return;

    if (editingIndex.value !== null) {
        localAddons.value[editingIndex.value] = { ...currentAddon.value };
    } else {
        localAddons.value.push({ ...currentAddon.value });
    }

    emit('update:addons', localAddons.value);
    closeAddonModal();
};

const closeAddonModal = () => {
    showAddonModal.value = false;
    editingIndex.value = null;
    currentAddon.value = {
        name: '',
        description: '',
        selection_type: 'single',
        is_required: false,
        min_selections: null,
        max_selections: null,
        options: [],
    };
};

// Watch for external changes to addons prop
watch(() => props.addons, (newAddons) => {
    localAddons.value = [...newAddons];
}, { deep: true });
</script>
