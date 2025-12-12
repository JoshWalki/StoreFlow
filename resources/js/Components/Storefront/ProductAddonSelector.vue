<template>
    <div v-if="addons.length > 0" class="space-y-6">
        <div class="border-t pt-6" :class="theme === 'bold' ? 'border-gray-700' : 'border-gray-200'">
            <h3 class="text-lg font-semibold mb-4" :class="theme === 'bold' ? 'text-white' : 'text-gray-900'">
                Customize Your Order
            </h3>

            <div class="space-y-6">
                <div
                    v-for="(addon, addonIndex) in addons"
                    :key="addonIndex"
                    class="rounded-lg p-4"
                    :class="theme === 'bold' ? 'bg-gray-800 border border-gray-700' : 'bg-gray-50 border border-gray-200'"
                >
                    <!-- Addon Header -->
                    <div class="mb-3">
                        <div class="flex items-center gap-2 mb-1">
                            <h4 class="font-medium" :class="theme === 'bold' ? 'text-white' : 'text-gray-900'">
                                {{ addon.name }}
                            </h4>
                            <span
                                v-if="addon.is_required"
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"
                            >
                                Required
                            </span>
                        </div>
                        <p v-if="addon.description" class="text-sm" :class="theme === 'bold' ? 'text-gray-400' : 'text-gray-600'">
                            {{ addon.description }}
                        </p>
                        <p v-if="addon.selection_type === 'multiple'" class="text-xs mt-1" :class="theme === 'bold' ? 'text-gray-500' : 'text-gray-500'">
                            <span v-if="addon.min_selections && addon.max_selections">
                                Select {{ addon.min_selections }}-{{ addon.max_selections }} options
                            </span>
                            <span v-else-if="addon.min_selections">
                                Select at least {{ addon.min_selections }} {{ addon.min_selections === 1 ? 'option' : 'options' }}
                            </span>
                            <span v-else-if="addon.max_selections">
                                Select up to {{ addon.max_selections }} {{ addon.max_selections === 1 ? 'option' : 'options' }}
                            </span>
                            <span v-else>
                                Select any number of options
                            </span>
                        </p>
                    </div>

                    <!-- Single Select Options (Radio) -->
                    <div v-if="addon.selection_type === 'single'" class="space-y-2">
                        <label
                            v-for="(option, optionIndex) in addon.options"
                            :key="optionIndex"
                            class="flex items-center justify-between p-3 border-2 rounded-lg cursor-pointer transition-all"
                            :class="[
                                isOptionSelected(addonIndex, optionIndex)
                                    ? (theme === 'bold' ? 'border-orange-500 bg-orange-500/10' : theme === 'modern' ? 'border-purple-600 bg-purple-50' : theme === 'monochrome' ? 'border-gray-900 bg-gray-100' : 'border-blue-600 bg-blue-50')
                                    : (theme === 'bold' ? 'border-gray-700 hover:bg-gray-700' : 'border-gray-300 hover:bg-gray-100')
                            ]"
                        >
                            <div class="flex items-center flex-1">
                                <input
                                    :name="`addon-${addonIndex}`"
                                    type="radio"
                                    :value="optionIndex"
                                    :checked="isOptionSelected(addonIndex, optionIndex)"
                                    @change="selectSingleOption(addonIndex, optionIndex)"
                                    :class="theme === 'bold' ? 'text-orange-500 focus:ring-orange-500' : theme === 'modern' ? 'text-purple-600 focus:ring-purple-500' : theme === 'monochrome' ? 'text-gray-900 focus:ring-gray-900' : 'text-blue-600 focus:ring-blue-500'"
                                    class="h-4 w-4"
                                />
                                <span class="ml-3 text-sm font-medium" :class="theme === 'bold' ? 'text-white' : 'text-gray-900'">
                                    {{ option.name }}
                                </span>
                            </div>
                            <span class="text-sm font-medium" :class="theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">
                                {{ option.price_adjustment > 0 ? '+' : '' }}{{ formatCurrency(option.price_adjustment) }}
                            </span>
                        </label>
                    </div>

                    <!-- Multiple Select Options (Checkbox) -->
                    <div v-else class="space-y-2">
                        <label
                            v-for="(option, optionIndex) in addon.options"
                            :key="optionIndex"
                            class="flex items-center justify-between p-3 border-2 rounded-lg cursor-pointer transition-all"
                            :class="[
                                isOptionSelected(addonIndex, optionIndex)
                                    ? (theme === 'bold' ? 'border-orange-500 bg-orange-500/10' : theme === 'modern' ? 'border-purple-600 bg-purple-50' : theme === 'monochrome' ? 'border-gray-900 bg-gray-100' : 'border-blue-600 bg-blue-50')
                                    : (theme === 'bold' ? 'border-gray-700 hover:bg-gray-700' : 'border-gray-300 hover:bg-gray-100'),
                                !canSelectMore(addonIndex) && !isOptionSelected(addonIndex, optionIndex) ? 'opacity-50 cursor-not-allowed' : ''
                            ]"
                        >
                            <div class="flex items-center flex-1">
                                <input
                                    type="checkbox"
                                    :checked="isOptionSelected(addonIndex, optionIndex)"
                                    @change="toggleMultipleOption(addonIndex, optionIndex)"
                                    :disabled="!canSelectMore(addonIndex) && !isOptionSelected(addonIndex, optionIndex)"
                                    :class="theme === 'bold' ? 'text-orange-500 focus:ring-orange-500' : theme === 'modern' ? 'text-purple-600 focus:ring-purple-500' : theme === 'monochrome' ? 'text-gray-900 focus:ring-gray-900' : 'text-blue-600 focus:ring-blue-500'"
                                    class="h-4 w-4 rounded"
                                />
                                <span class="ml-3 text-sm font-medium" :class="theme === 'bold' ? 'text-white' : 'text-gray-900'">
                                    {{ option.name }}
                                </span>
                            </div>
                            <span class="text-sm font-medium" :class="theme === 'bold' ? 'text-gray-300' : 'text-gray-700'">
                                {{ option.price_adjustment > 0 ? '+' : '' }}{{ formatCurrency(option.price_adjustment) }}
                            </span>
                        </label>

                        <!-- Selection Count -->
                        <div v-if="addon.max_selections" class="text-xs text-right" :class="theme === 'bold' ? 'text-gray-500' : 'text-gray-500'">
                            {{ getSelectedCount(addonIndex) }} / {{ addon.max_selections }} selected
                        </div>
                    </div>

                    <!-- Validation Error -->
                    <p v-if="getAddonError(addonIndex)" class="mt-2 text-sm text-red-600">
                        {{ getAddonError(addonIndex) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Total Price Preview -->
        <div
            v-if="totalAddonPrice > 0"
            class="rounded-lg p-4 border-2"
            :class="theme === 'bold' ? 'bg-orange-500/10 border-orange-500/30' : theme === 'modern' ? 'bg-purple-50 border-purple-200' : theme === 'monochrome' ? 'bg-gray-100 border-gray-300' : 'bg-blue-50 border-blue-200'"
        >
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium" :class="theme === 'bold' ? 'text-white' : 'text-gray-900'">
                    Addons Total:
                </span>
                <span class="text-lg font-bold" :class="theme === 'bold' ? 'text-orange-400' : theme === 'modern' ? 'text-purple-700' : theme === 'monochrome' ? 'text-gray-900' : 'text-blue-700'">
                    +{{ formatCurrency(totalAddonPrice) }}
                </span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';

const props = defineProps({
    addons: {
        type: Array,
        required: true,
    },
    theme: {
        type: String,
        default: 'default',
    },
});

const emit = defineEmits(['update:selections', 'validation-change']);

// Track selected options for each addon
const selections = ref({});
const validationErrors = ref({});

// Initialize selections with defaults
const initializeSelections = () => {
    const newSelections = {};
    props.addons.forEach((addon, addonIndex) => {
        const defaultOptions = addon.options
            .map((option, optionIndex) => (option.is_default === true || option.is_default === 1 || option.is_default === "1") ? optionIndex : null)
            .filter(index => index !== null);

        if (defaultOptions.length > 0) {
            if (addon.selection_type === 'single') {
                newSelections[addonIndex] = [defaultOptions[0]];
            } else {
                newSelections[addonIndex] = defaultOptions;
            }
        } else {
            newSelections[addonIndex] = [];
        }
    });
    selections.value = newSelections;
};

// Initialize on mount
initializeSelections();

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-AU', {
        style: 'currency',
        currency: 'AUD',
    }).format(amount);
};

const isOptionSelected = (addonIndex, optionIndex) => {
    return selections.value[addonIndex]?.includes(optionIndex) || false;
};

const getSelectedCount = (addonIndex) => {
    return selections.value[addonIndex]?.length || 0;
};

const canSelectMore = (addonIndex) => {
    const addon = props.addons[addonIndex];
    if (!addon.max_selections) return true;
    return getSelectedCount(addonIndex) < addon.max_selections;
};

const selectSingleOption = (addonIndex, optionIndex) => {
    selections.value[addonIndex] = [optionIndex];
    validateAddon(addonIndex);
    emitSelections();
};

const toggleMultipleOption = (addonIndex, optionIndex) => {
    if (!selections.value[addonIndex]) {
        selections.value[addonIndex] = [];
    }

    const index = selections.value[addonIndex].indexOf(optionIndex);
    if (index > -1) {
        selections.value[addonIndex].splice(index, 1);
    } else {
        if (canSelectMore(addonIndex)) {
            selections.value[addonIndex].push(optionIndex);
        }
    }
    validateAddon(addonIndex);
    emitSelections();
};

const validateAddon = (addonIndex) => {
    const addon = props.addons[addonIndex];
    const selectedCount = getSelectedCount(addonIndex);

    if (addon.is_required && selectedCount === 0) {
        validationErrors.value[addonIndex] = `Please select an option for ${addon.name}`;
        return false;
    }

    if (addon.min_selections && selectedCount < addon.min_selections) {
        validationErrors.value[addonIndex] = `Please select at least ${addon.min_selections} ${addon.min_selections === 1 ? 'option' : 'options'}`;
        return false;
    }

    if (addon.max_selections && selectedCount > addon.max_selections) {
        validationErrors.value[addonIndex] = `You can select up to ${addon.max_selections} ${addon.max_selections === 1 ? 'option' : 'options'}`;
        return false;
    }

    delete validationErrors.value[addonIndex];
    return true;
};

const validateAll = () => {
    let isValid = true;
    props.addons.forEach((addon, index) => {
        if (!validateAddon(index)) {
            isValid = false;
        }
    });
    return isValid;
};

const getAddonError = (addonIndex) => {
    return validationErrors.value[addonIndex] || null;
};

const totalAddonPrice = computed(() => {
    let total = 0;
    Object.keys(selections.value).forEach(addonIndex => {
        const addon = props.addons[addonIndex];
        if (addon) {
            selections.value[addonIndex].forEach(optionIndex => {
                const option = addon.options[optionIndex];
                if (option) {
                    total += Number(option.price_adjustment) || 0;
                }
            });
        }
    });
    return total;
});

const emitSelections = () => {
    const formattedSelections = [];
    Object.keys(selections.value).forEach(addonIndex => {
        const addon = props.addons[addonIndex];
        if (addon && selections.value[addonIndex].length > 0) {
            selections.value[addonIndex].forEach(optionIndex => {
                const option = addon.options[optionIndex];
                if (option) {
                    formattedSelections.push({
                        addon_name: addon.name,
                        addon_index: parseInt(addonIndex),
                        option_name: option.name,
                        option_index: optionIndex,
                        price_adjustment: option.price_adjustment || 0,
                    });
                }
            });
        }
    });

    console.log('ProductAddonSelector - Emitting selections:', formattedSelections);

    const isValid = validateAll();
    emit('update:selections', formattedSelections);
    emit('validation-change', { isValid, errors: validationErrors.value });
};

// Watch for addon changes and reinitialize
watch(() => props.addons, () => {
    initializeSelections();
    emitSelections();
}, { deep: true });

// Expose validate method for parent component
defineExpose({
    validateAll,
    isValid: computed(() => Object.keys(validationErrors.value).length === 0),
});

// Emit initial selections when component mounts
onMounted(() => {
    emitSelections();
});
</script>
