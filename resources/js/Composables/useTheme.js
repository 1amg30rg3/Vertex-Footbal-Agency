import { computed, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const STORAGE_KEY = 'ab-theme';
const THEMES = ['dark', 'light'];

const theme = ref('dark');

function systemPreference() {
    if (typeof window === 'undefined' || !window.matchMedia) {
        return 'dark';
    }

    return window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
}

function readStored() {
    try {
        const stored = window.localStorage.getItem(STORAGE_KEY);

        return THEMES.includes(stored) ? stored : null;
    } catch {
        return null;
    }
}

function apply(value) {
    theme.value = value;
    document.documentElement.dataset.theme = value;
    document.documentElement.style.colorScheme = value;
}

export function initTheme() {
    if (typeof document === 'undefined') {
        return;
    }

    apply(document.documentElement.dataset.theme || readStored() || systemPreference());

    window.requestAnimationFrame(() => document.documentElement.classList.add('theme-ready'));
}

export function useTheme() {
    const page = usePage();

    function setTheme(value, { persistForUser = true } = {}) {
        if (!THEMES.includes(value)) {
            return;
        }

        apply(value);

        try {
            window.localStorage.setItem(STORAGE_KEY, value);
        } catch {
        }

        if (persistForUser && page.props?.auth?.user) {
            router.patch(
                '/admin/preferences/theme',
                { theme: value },
                { preserveScroll: true, preserveState: true, only: [] },
            );
        }
    }

    function toggleTheme() {
        setTheme(theme.value === 'dark' ? 'light' : 'dark');
    }

    return {
        theme: computed(() => theme.value),
        isDark: computed(() => theme.value === 'dark'),
        setTheme,
        toggleTheme,
    };
}
