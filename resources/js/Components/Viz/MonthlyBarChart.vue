<script setup>
import { computed, ref } from 'vue';
import { Bar } from 'vue-chartjs';
import {
    BarElement,
    CategoryScale,
    Chart as ChartJS,
    Legend,
    LinearScale,
    Tooltip,
} from 'chart.js';
import { useChartTheme } from '@/Support/chartTheme';
import { useI18n } from '@/Composables/useI18n';
import Icon from '@/Components/Ui/Icon.vue';

ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip, Legend);

const props = defineProps({
    months: { type: Array, required: true },
    title: { type: String, default: null },
    goalsLabel: { type: String, default: 'Goals' },
    assistsLabel: { type: String, default: 'Assists' },
    height: { type: Number, default: 260 },
});

const { palette, chrome } = useChartTheme();
const { t } = useI18n();
const showTable = ref(false);

const ordered = computed(() => [...props.months]);

const labels = computed(() => ordered.value.map((row) => t(`months.${row.month}`)));

const series = computed(() => [
    { label: props.goalsLabel, values: ordered.value.map((row) => row.goals ?? 0), color: palette.value[0] },
    { label: props.assistsLabel, values: ordered.value.map((row) => row.assists ?? 0), color: palette.value[1] },
]);

const data = computed(() => ({
    labels: labels.value,
    datasets: series.value.map((item) => ({
        label: item.label,
        data: item.values,
        backgroundColor: item.color,
        borderRadius: 4,
        borderSkipped: 'bottom',
        maxBarThickness: 22,
        barPercentage: 0.78,
        categoryPercentage: 0.72,
    })),
}));

const options = computed(() => ({
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
            usePointStyle: true,
            boxWidth: 8,
            boxHeight: 8,
            boxPadding: 4,
        },
    },
    scales: {
        x: {
            grid: { display: false },
            border: { color: chrome.value.grid },
            ticks: { color: chrome.value.subtle, font: { size: 11 } },
        },
        y: {
            beginAtZero: true,
            grid: { color: chrome.value.grid, drawTicks: false },
            border: { display: false },
            ticks: { color: chrome.value.subtle, font: { size: 11 }, precision: 0, maxTicksLimit: 5 },
        },
    },
}));

const hasData = computed(() =>
    ordered.value.some((row) => (row.goals ?? 0) > 0 || (row.assists ?? 0) > 0),
);
</script>

<template>
    <figure class="space-y-4">
        <figcaption class="flex flex-wrap items-center justify-between gap-3">
            <span v-if="title" class="text-sm font-semibold tracking-tight text-fg">{{ title }}</span>

            <div class="flex items-center gap-4">
                <ul class="flex items-center gap-3">
                    <li v-for="item in series" :key="item.label" class="flex items-center gap-1.5 text-xs text-fg-muted">
                        <span class="h-2.5 w-2.5 rounded-sm" :style="{ background: item.color }" />
                        {{ item.label }}
                    </li>
                </ul>

                <button
                    type="button"
                    class="inline-flex items-center gap-1 text-[11px] text-fg-subtle transition-colors hover:text-accent"
                    :aria-pressed="showTable"
                    @click="showTable = !showTable"
                >
                    <Icon :name="showTable ? 'chart' : 'page'" :size="12" />
                    {{ showTable ? 'Chart' : 'Table' }}
                </button>
            </div>
        </figcaption>

        <p
            v-if="!hasData"
            class="rounded-xl border border-dashed border-border px-4 py-10 text-center text-sm text-fg-subtle"
        >
            No monthly breakdown recorded.
        </p>

        <div v-else-if="!showTable" :style="{ height: `${height}px` }">
            <Bar :data="data" :options="options" />
        </div>

        <div v-else class="overflow-x-auto scrollbar-thin">
            <table class="w-full min-w-[24rem] text-sm">
                <thead>
                    <tr class="border-b border-border text-left text-xs uppercase tracking-wide text-fg-subtle">
                        <th class="py-2 font-semibold">Month</th>
                        <th class="py-2 text-right font-semibold">{{ goalsLabel }}</th>
                        <th class="py-2 text-right font-semibold">{{ assistsLabel }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, index) in ordered" :key="row.month" class="border-b border-border/60 last:border-0">
                        <td class="py-2 text-fg-muted">{{ labels[index] }}</td>
                        <td class="py-2 text-right tabular-nums text-fg">{{ row.goals ?? 0 }}</td>
                        <td class="py-2 text-right tabular-nums text-fg">{{ row.assists ?? 0 }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </figure>
</template>
