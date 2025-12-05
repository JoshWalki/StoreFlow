import { ref, watch, onMounted } from 'vue';

const isDark = ref(false);

export function useDarkMode() {
    const toggleDarkMode = () => {
        isDark.value = !isDark.value;
        updateDarkMode();
    };

    const setDarkMode = (value) => {
        isDark.value = value;
        updateDarkMode();
    };

    const updateDarkMode = () => {
        if (isDark.value) {
            document.documentElement.classList.add('dark');
            localStorage.setItem('darkMode', 'true');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('darkMode', 'false');
        }
    };

    const initializeDarkMode = () => {
        // Check localStorage first
        const savedDarkMode = localStorage.getItem('darkMode');

        if (savedDarkMode !== null) {
            isDark.value = savedDarkMode === 'true';
        } else {
            // Check system preference
            isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
        }

        updateDarkMode();

        // Listen for system preference changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (localStorage.getItem('darkMode') === null) {
                isDark.value = e.matches;
                updateDarkMode();
            }
        });
    };

    return {
        isDark,
        toggleDarkMode,
        setDarkMode,
        initializeDarkMode
    };
}
