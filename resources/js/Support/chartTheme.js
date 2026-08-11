import { computed } from 'vue';
import { useTheme } from '@/Composables/useTheme';

export const CATEGORICAL = {
    dark: ['#AC7D03', '#3D8AD4', '#C3608A'],
    light: ['#9D7206', '#1F7DCF', '#BB4E7F'],
};

function token(name, fallback) {
    if (typeof window === 'undefined') return fallback;

    const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();

    return value || fallback;
}

export function useChartTheme() {
    const { theme } = useTheme();

    const palette = computed(() => CATEGORICAL[theme.value] ?? CATEGORICAL.dark);

    const chrome = computed(() => {
        void theme.value;

        return {
            surface: token('--surface', '#212125'),
            grid: token('--border', '#3a3a40'),
            text: token('--fg-muted', '#b4b4bb'),
            textStrong: token('--fg', '#f5f5f7'),
            subtle: token('--fg-subtle', '#8a8a92'),
            accent: token('--accent', '#cd942c'),
        };
    });

    const baseOptions = computed(() => ({
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 350 },
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: chrome.value.surface,
                titleColor: chrome.value.textStrong,
                bodyColor: chrome.value.text,
                borderColor: chrome.value.grid,
                borderWidth: 1,
                padding: 10,
                cornerRadius: 8,
                displayColors: true,
                boxWidth: 8,
                boxHeight: 8,
                boxPadding: 4,
                usePointStyle: true,
            },
        },
    }));

    return { theme, palette, chrome, baseOptions };
}
