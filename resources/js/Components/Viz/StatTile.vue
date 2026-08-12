<script setup>
import { computed } from 'vue';
import Icon from '@/Components/Ui/Icon.vue';
import CountUp from '@/Components/Site/CountUp.vue';

const props = defineProps({
    label: { type: String, required: true },
    value: { type: [String, Number], required: true },
    sublabel: { type: String, default: null },
    icon: { type: String, default: null },
    tone: { type: String, default: 'default', validator: (v) => ['default', 'accent', 'figure'].includes(v) },
    size: { type: String, default: 'md', validator: (v) => ['md', 'lg'].includes(v) },
    animate: { type: Boolean, default: false },
});

const tones = {
    default: 'text-fg',
    accent: 'text-accent',
    figure: 'text-accent-2',
};

const valueClasses = computed(() =>
    props.size === 'lg'
        ? 'font-heading text-[clamp(2.25rem,4.5vw,3.25rem)] leading-[0.85] tracking-[-0.045em]'
        : 'text-2xl tracking-tight sm:text-3xl',
);

const counts = computed(() => props.animate && Number.isFinite(Number(props.value)));
</script>

<template>
    <div class="rounded-xl border border-border bg-surface p-4">
        <div class="flex items-center justify-between gap-2">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-fg-subtle">{{ label }}</p>
            <Icon v-if="icon" :name="icon" :size="15" class="text-fg-subtle" />
        </div>
        <p :class="['mt-2 font-semibold tabular-nums', valueClasses, tones[tone]]">
            <CountUp v-if="counts" :value="Number(value)" />
            <template v-else>{{ value }}</template>
        </p>
        <p v-if="sublabel" class="mt-1 text-xs text-fg-muted">{{ sublabel }}</p>
    </div>
</template>
