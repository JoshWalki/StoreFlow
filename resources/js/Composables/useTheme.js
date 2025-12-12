import { computed } from 'vue';

/**
 * Theme composable for storefront theming
 * Provides theme-specific color classes based on the store's selected theme
 */
export function useTheme(themeKey = 'classic') {
    const theme = computed(() => themeKey || 'classic');

    // Theme configurations
    const themes = {
        classic: {
            // Primary colors
            primary: 'blue-600',
            primaryHover: 'blue-700',
            primaryLight: 'blue-50',
            primaryBorder: 'blue-600',

            // Accent colors
            accent: 'blue-500',
            accentHover: 'blue-600',

            // Button classes
            buttonPrimary: 'bg-blue-600 text-white hover:bg-blue-700',
            buttonSecondary: 'bg-gray-800 text-white hover:bg-gray-900',

            // Link colors
            link: 'text-blue-600 hover:text-blue-800',

            // Badge colors
            badge: 'bg-blue-100 text-blue-800',
            badgePickup: 'bg-orange-100 text-orange-800',
            badgeDelivery: 'bg-green-100 text-green-800',

            // Product card
            productPrice: 'text-blue-600',
            productCard: 'bg-white hover:shadow-xl',

            // Background
            background: 'bg-gray-50',
            cardBackground: 'bg-white',
        },
        modern: {
            // Primary colors
            primary: 'purple-600',
            primaryHover: 'purple-700',
            primaryLight: 'purple-50',
            primaryBorder: 'purple-600',

            // Accent colors with gradient
            accent: 'purple-500',
            accentHover: 'purple-600',

            // Button classes with gradients
            buttonPrimary: 'bg-gradient-to-r from-purple-600 to-pink-600 text-white hover:from-purple-700 hover:to-pink-700',
            buttonSecondary: 'bg-gradient-to-r from-gray-700 to-gray-900 text-white hover:from-gray-800 hover:to-gray-950',

            // Link colors
            link: 'text-purple-600 hover:text-purple-800',

            // Badge colors
            badge: 'bg-purple-100 text-purple-800',
            badgePickup: 'bg-orange-100 text-orange-800',
            badgeDelivery: 'bg-green-100 text-green-800',

            // Product card
            productPrice: 'bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent',
            productCard: 'bg-white hover:shadow-2xl rounded-2xl',

            // Background
            background: 'bg-gradient-to-br from-purple-50 via-pink-50 to-purple-50',
            cardBackground: 'bg-white',
        },
        bold: {
            // Primary colors
            primary: 'orange-500',
            primaryHover: 'orange-600',
            primaryLight: 'orange-50',
            primaryBorder: 'orange-500',

            // Accent colors
            accent: 'yellow-500',
            accentHover: 'yellow-600',

            // Button classes with bold colors
            buttonPrimary: 'bg-gradient-to-r from-orange-500 to-yellow-500 text-gray-900 font-bold hover:from-orange-600 hover:to-yellow-600',
            buttonSecondary: 'bg-gray-900 text-orange-500 font-bold hover:bg-gray-800 border-2 border-orange-500',

            // Link colors
            link: 'text-orange-500 hover:text-orange-600 font-semibold',

            // Badge colors
            badge: 'bg-orange-500 text-gray-900 font-bold',
            badgePickup: 'bg-orange-500 text-gray-900 font-bold',
            badgeDelivery: 'bg-green-500 text-gray-900 font-bold',

            // Product card
            productPrice: 'bg-gradient-to-r from-orange-500 to-yellow-500 bg-clip-text text-transparent font-bold',
            productCard: 'bg-gray-900 text-white hover:shadow-2xl hover:shadow-orange-500/20 border border-orange-500/20',

            // Background
            background: 'bg-gray-950',
            cardBackground: 'bg-gray-900',
        },
        monochrome: {
            // Primary colors
            primary: 'gray-900',
            primaryHover: 'gray-800',
            primaryLight: 'gray-50',
            primaryBorder: 'gray-300',

            // Accent colors
            accent: 'gray-800',
            accentHover: 'gray-900',

            // Button classes with glass effect (iOS-style)
            buttonPrimary: 'bg-gray-900/90 backdrop-blur-xl text-white hover:bg-gray-900 shadow-lg',
            buttonSecondary: 'bg-white/80 backdrop-blur-xl text-gray-900 hover:bg-white border border-gray-200 shadow-sm',

            // Link colors
            link: 'text-gray-900 hover:text-gray-700',

            // Badge colors
            badge: 'bg-gray-100 text-gray-900 border border-gray-200',
            badgePickup: 'bg-gray-100 text-gray-900 border border-gray-200',
            badgeDelivery: 'bg-gray-100 text-gray-900 border border-gray-200',

            // Product card
            productPrice: 'text-gray-900 font-semibold',
            productCard: 'bg-white hover:shadow-xl border border-gray-100',

            // Background
            background: 'bg-white',
            cardBackground: 'bg-white border border-gray-100',
        },
    };

    // Get theme configuration
    const config = computed(() => themes[theme.value] || themes.classic);

    // Helper to get class for a specific element
    const getClass = (element) => {
        return config.value[element] || '';
    };

    // Helper to get primary color
    const getPrimaryColor = () => {
        return config.value.primary;
    };

    return {
        theme,
        config,
        getClass,
        getPrimaryColor,
    };
}
