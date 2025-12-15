<template>
    <div class="flex items-center">
        <button
            type="button"
            @click="toggle"
            :disabled="disabled"
            :class="[
                'relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2',
                disabled ? 'opacity-50 cursor-not-allowed' : '',
                modelValue ? 'bg-blue-600' : 'bg-gray-200 dark:bg-gray-700'
            ]"
            role="switch"
            :aria-checked="modelValue"
            :aria-disabled="disabled"
        >
            <span
                :class="[
                    'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                    modelValue ? 'translate-x-6' : 'translate-x-1'
                ]"
            />
        </button>
        <label
            v-if="label"
            @click="!disabled && toggle()"
            :class="[
                'ml-3 text-sm font-medium select-none',
                disabled ? 'text-gray-400 dark:text-gray-500 cursor-not-allowed' : 'text-gray-700 dark:text-gray-300 cursor-pointer'
            ]"
        >
            {{ label }}
        </label>
    </div>
</template>

<script setup>
const props = defineProps({
    modelValue: {
        type: Boolean,
        required: true
    },
    label: {
        type: String,
        default: ''
    },
    disabled: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:modelValue']);

const toggle = () => {
    if (!props.disabled) {
        emit('update:modelValue', !props.modelValue);
    }
};
</script>
