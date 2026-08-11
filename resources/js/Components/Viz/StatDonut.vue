<script setup>
import { computed, ref } from 'vue';
import { Doughnut } from 'vue-chartjs';
import { ArcElement, Chart as ChartJS, Legend, Tooltip } from 'chart.js';
import { useChartTheme } from '@/Support/chartTheme';
import Icon from '@/Components/Ui/Icon.vue';

ChartJS.register(ArcElement, Tooltip, Legend);

const props = defineProps({
    slices: { type: Array, required: true },
    title: { type: String, default: null },
    centerLabel: { type: String, default: null },
    centerValue: { type: [String, Number], default: null },
    height: { type: Number, default: 220 },
});

const { palette, chrome } = useChartTheme();
const showTable = ref(false);

const rows = computed(() =>
    props.slices.map((slice, index) => ({
        ...slice,
        color: palette.value[index % palette.value.length],
        display: `${Math.round(slice.value)}%`,
    })),
);

const total = computed(() => rows.value.reduce((sum, row) => sum + Number(row.value || 0), 0));

const data = computed(() => ({
    labels: rows.value.map((row) => row.label),
    datasets: [
        {
            data: rows.value.map((row) => Number(row.value || 0)),
            backgroundColor: rows.value.map((row) => row.color),
            borderColor: chrome.value.surface,
            borderWidth: 2,
            hoverOffset: 6,
        },
    ],
}));

const options = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    cutout: '68%',
    animation: { duration: 400 },
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
            callbacks: {
                label: (context) => ` ${context.label}: ${Math.round(context.parsed)}%`,
            },
        },
    },
}));

const hasData = computed(() => total.value > 0);
</script>

<template>
    <figure class="space-y-4">
        <figcaption v-if="title" class="flex items-center justify-between gap-3">
            <span class="text-sm font-semibold tracking-tight text-fg">{{ title }}</span>
            <button
                type="button"
                class="inline-flex items-center gap-1 text-[11px] text-fg-subtle transition-colors hover:text-accent"
                :aria-pressed="showTable"
                @click="showTable = !showTable"
            >
                <Icon :name="showTable ? 'chart' : 'page'" :size="12" />
                {{ showTable ? 'Chart' : 'Table' }}
            </button>
        </figcaption>

        <p v-if="!hasData" class="rounded-xl border border-dashed border-border px-4 py-8 text-center text-sm text-fg-subtle">
            No data recorded.
        </p>

        <template v-else-if="!showTable">
            <div class="relative mx-auto" :style="{ height: `${height}px` }">
                <Doughnut :data="data" :options="options" />

                <div
                    v-if="centerValue !== null || centerLabel"
                    class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center"
                >
                    <span class="text-2xl font-semibold tabular-nums tracking-tight text-fg">{{ centerValue }}</span>
                    <span v-if="centerLabel" class="mt-0.5 text-[11px] uppercase tracking-wide text-fg-subtle">
                        {{ centerLabel }}
                    </span>
                </div>
            </div>

            <ul class="space-y-2">
                <li v-for="row in rows" :key="row.label" class="flex items-center gap-2.5 text-sm">
                    <span class="h-2.5 w-2.5 shrink-0 rounded-sm" :style="{ background: row.color }" />
                    <span class="min-w-0 flex-1 truncate text-fg-muted">{{ row.label }}</span>
                    <span class="tabular-nums font-medium text-fg">{{ row.display }}</span>
                </li>
            </ul>
        </template>

        <table v-else class="w-full text-sm">
            <thead>
                <tr class="border-b border-border text-left text-xs uppercase tracking-wide text-fg-subtle">
                    <th class="py-2 font-semibold">Category</th>
                    <th class="py-2 text-right font-semibold">Share</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="row in rows" :key="row.label" class="border-b border-border/60 last:border-0">
                    <td class="py-2 text-fg-muted">{{ row.label }}</td>
                    <td class="py-2 text-right tabular-nums text-fg">{{ row.display }}</td>
                </tr>
            </tbody>
        </table>
    </figure>
</template>
