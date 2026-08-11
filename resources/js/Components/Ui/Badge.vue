<script setup>
import { computed } from 'vue';
import Icon from './Icon.vue';

const props = defineProps({
    tone: {
        type: String,
        default: 'neutral',
        validator: (v) => ['neutral', 'accent', 'success', 'warning', 'danger', 'info'].includes(v),
    },
    size: { type: String, default: 'md', validator: (v) => ['sm', 'md'].includes(v) },
    icon: { type: String, default: null },
    dot: { type: Boolean, default: false },
});

const tones = {
    neutral: 'bg-surface-2 text-fg-muted border-border',
    accent: 'bg-accent-soft text-accent border-accent/30',
    success: 'bg-success-soft text-success border-success/30',
    warning: 'bg-warning-soft text-warning border-warning/30',
    danger: 'bg-danger-soft text-danger border-danger/30',
    info: 'bg-info-soft text-info border-info/30',
};

const classes = computed(() => [
    'inline-flex items-center gap-1.5 rounded-full border font-medium whitespace-nowrap',
    props.size === 'sm' ? 'text-[11px] px-2 py-0.5' : 'text-xs px-2.5 py-1',
    tones[props.tone],
]);
</script>

<template>
    <span :class="classes">
        <span v-if="dot" class="h-1.5 w-1.5 rounded-full bg-current" />
        <Icon v-else-if="icon" :name="icon" :size="size === 'sm' ? 12 : 13" />
        <slot />
    </span>
</template>
