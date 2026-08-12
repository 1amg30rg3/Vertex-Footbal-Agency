<script setup>
import { computed } from 'vue';

const props = defineProps({
    as: { type: String, default: 'div' },
    padding: { type: String, default: 'md', validator: (v) => ['none', 'sm', 'md', 'lg'].includes(v) },
    hoverable: { type: Boolean, default: false },
    bordered: { type: Boolean, default: true },
    tone: { type: String, default: 'surface', validator: (v) => ['surface', 'raised', 'plain'].includes(v) },
});

const paddings = { none: '', sm: 'p-4', md: 'p-5 sm:p-6', lg: 'p-6 sm:p-8' };
const tones = {
    surface: 'bg-surface',
    raised: 'bg-surface-2',
    plain: 'bg-transparent',
};

const classes = computed(() => [
    'rounded-2xl theme-transition',
    tones[props.tone],
    props.bordered ? 'border border-border' : '',
    paddings[props.padding],
    props.hoverable ? 'transition-[border-color,translate,box-shadow] duration-200 hover:border-accent/50 hover:-translate-y-0.5' : '',
]);
</script>

<template>
    <component :is="as" :class="classes">
        <slot />
    </component>
</template>
