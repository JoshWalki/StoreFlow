<template>
    <div class="turnstile-container">
        <!-- Turnstile widget (always visible so it can initialize properly) -->
        <div :id="containerId" class="turnstile-widget"></div>

        <!-- Error message -->
        <div v-if="error" class="turnstile-error">
            <span class="error-text">{{ error }}</span>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useTurnstile } from '@/composables/useTurnstile';

const props = defineProps({
    siteKey: {
        type: String,
        required: true,
    },
    theme: {
        type: String,
        default: 'light',
        validator: (value) => ['light', 'dark', 'auto'].includes(value),
    },
    modelValue: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(['update:modelValue', 'error', 'ready']);

const containerId = ref(`turnstile-${Math.random().toString(36).substr(2, 9)}`);
const { turnstileToken, isLoading, error, scriptLoaded, requiresInteraction, renderTurnstile, reset, isValid } = useTurnstile(props.siteKey, props.theme);

watch(turnstileToken, (newToken) => {
    emit('update:modelValue', newToken);
});

watch(error, (newError) => {
    if (newError) {
        emit('error', newError);
    }
});

watch(scriptLoaded, (loaded) => {
    if (loaded) {
        renderTurnstile(containerId.value);
        emit('ready');
    }
});

onMounted(() => {
    if (scriptLoaded.value) {
        renderTurnstile(containerId.value);
    }
});

defineExpose({
    reset,
    isValid,
});
</script>

<style scoped>
.turnstile-container {
    margin: 1rem 0;
    width: 100%;
}

.turnstile-widget {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 0.5rem;
    width: 100%;
    min-height: 65px;
}

.turnstile-widget :deep(iframe) {
    margin: 0 auto;
}

.turnstile-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem;
    color: #666;
}

.loading-spinner {
    width: 1.25rem;
    height: 1.25rem;
    animation: spin 1s linear infinite;
}

.loading-text {
    font-size: 0.875rem;
    color: #666;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.turnstile-error {
    margin-top: 0.5rem;
    padding: 0.75rem;
    background-color: #fee;
    border: 1px solid #fcc;
    border-radius: 0.25rem;
}

.error-text {
    color: #c33;
    font-size: 0.875rem;
}
</style>
