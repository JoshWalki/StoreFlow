import { ref, onMounted, onBeforeUnmount } from 'vue';

export function useTurnstile(siteKey, theme = 'light') {
    const turnstileToken = ref(null);
    const turnstileWidgetId = ref(null);
    const isLoading = ref(true);
    const error = ref(null);
    const scriptLoaded = ref(false);
    const requiresInteraction = ref(false);

    const loadTurnstileScript = () => {
        return new Promise((resolve, reject) => {
            if (window.turnstile) {
                scriptLoaded.value = true;
                resolve();
                return;
            }

            const script = document.createElement('script');
            script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js';
            script.async = true;
            script.defer = true;

            script.onload = () => {
                scriptLoaded.value = true;
                resolve();
            };

            script.onerror = () => {
                error.value = 'Failed to load Turnstile script';
                reject(new Error('Failed to load Turnstile script'));
            };

            document.head.appendChild(script);
        });
    };

    const renderTurnstile = (containerId) => {
        if (!window.turnstile) {
            error.value = 'Turnstile not loaded';
            isLoading.value = false;
            return;
        }

        try {
            console.log('Rendering Turnstile with site key:', siteKey);

            turnstileWidgetId.value = window.turnstile.render(`#${containerId}`, {
                sitekey: siteKey,
                theme: theme,
                size: 'normal',
                callback: (token) => {
                    console.log(' Turnstile verification successful, token:', token?.substring(0, 20) + '...');
                    turnstileToken.value = token;
                    isLoading.value = false;
                    requiresInteraction.value = false;
                    error.value = null;
                },
                'error-callback': (errorCode) => {
                    console.error('Turnstile error callback triggered:', errorCode);
                    error.value = 'Security verification failed. Please try again.';
                    isLoading.value = false;
                    requiresInteraction.value = false;
                    turnstileToken.value = null;
                },
                'expired-callback': () => {
                    console.warn('Turnstile token expired');
                    turnstileToken.value = null;
                    error.value = 'Verification expired, please try again';
                    isLoading.value = false;
                    requiresInteraction.value = false;
                },
                'timeout-callback': () => {
                    console.warn('Turnstile verification timed out');
                    error.value = 'Verification timed out. Please try again.';
                    isLoading.value = false;
                    requiresInteraction.value = false;
                    turnstileToken.value = null;
                },
                'before-interactive-callback': () => {
                    console.log('Turnstile requires manual verification - showing widget');
                    requiresInteraction.value = true;
                    isLoading.value = false;
                },
                'after-interactive-callback': () => {
                    console.log('Turnstile manual verification completed');
                },
            });

            // Safety timeout - if verification doesn't complete in 15 seconds, show error
            setTimeout(() => {
                if (isLoading.value && !turnstileToken.value) {
                    console.error('Turnstile verification timeout - no response after 15 seconds');
                    error.value = 'Security verification is taking too long. Please check your internet connection and Turnstile configuration.';
                    isLoading.value = false;
                }
            }, 15000);

        } catch (e) {
            error.value = 'Failed to initialize security verification';
            console.error('Turnstile render error:', e);
            isLoading.value = false;
        }
    };

    const reset = () => {
        if (window.turnstile && turnstileWidgetId.value !== null) {
            window.turnstile.reset(turnstileWidgetId.value);
            turnstileToken.value = null;
            isLoading.value = true;
            requiresInteraction.value = false;
            error.value = null;
        }
    };

    const isValid = () => {
        return turnstileToken.value !== null && !error.value;
    };

    onMounted(async () => {
        try {
            await loadTurnstileScript();
        } catch (e) {
            console.error('Failed to load Turnstile:', e);
        }
    });

    onBeforeUnmount(() => {
        if (window.turnstile && turnstileWidgetId.value !== null) {
            window.turnstile.remove(turnstileWidgetId.value);
        }
    });

    return {
        turnstileToken,
        isLoading,
        error,
        scriptLoaded,
        requiresInteraction,
        renderTurnstile,
        reset,
        isValid,
    };
}
