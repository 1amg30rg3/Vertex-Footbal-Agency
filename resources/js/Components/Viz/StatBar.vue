<script setup>
import { computed, onMounted, ref } from 'vue';

const props = defineProps({
    label: { type: String, required: true },
    value: { type: Number, required: true },
    max: { type: Number, default: 100 },
    size: { type: String, default: 'md', validator: (v) => ['sm', 'md'].includes(v) },
    showValue: { type: Boolean, default: true },
    animate: { type: Boolean, default: true },
});

const mounted = ref(!props.animate);

const percent = computed(() => {
    const ratio = Math.max(0, Math.min(1, props.value / props.max));

    return Math.round(ratio * 100);
});

const width = computed(() => (mounted.value ? `${percent.value}%` : '0%'));

onMounted(() => {
    if (props.animate) window.requestAnimationFrame(() => (mounted.value = true));
});
</script>

<template>
    <div class="space-y-1.5">
        <div class="flex items-baseline justify-between gap-3">
            <span :class="['font-medium text-fg', size === 'sm' ? 'text-xs' : 'text-sm']">{{ label }}</span>
            <span
                v-if="showValue"
                :class="['tabular-nums font-semibold text-accent', size === 'sm' ? 'text-xs' : 'text-sm']"
            >{{ value }}</span>
        </div>

        <div
            :class="['w-full overflow-hidden rounded-full bg-surface-3', size === 'sm' ? 'h-1.5' : 'h-2']"
            role="meter"
            :aria-valuenow="value"
            aria-valuemin="0"
            :aria-valuemax="max"
            :aria-label="label"
        >
            <div
                class="h-full rounded-full bg-accent transition-[width] duration-700 ease-out"
                :style="{ width }"
            />
        </div>
    </div>
</template>
