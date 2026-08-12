<script setup>
import { computed } from 'vue';

const props = defineProps({
    number: { type: String, default: null },
    title: { type: String, required: true },
    lead: { type: String, default: null },
    align: { type: String, default: 'left', validator: (v) => ['left', 'center'].includes(v) },
    as: { type: String, default: 'h2' },
    size: { type: String, default: 'md', validator: (v) => ['md', 'lg'].includes(v) },
});

const large = computed(() => props.size === 'lg');

const titleClasses = computed(() =>
    large.value
        ? 'text-3xl leading-[1.05] tracking-[-0.02em] sm:text-4xl lg:text-5xl'
        : 'text-2xl tracking-tight sm:text-3xl',
);

const leadClasses = computed(() =>
    large.value ? 'max-w-xl text-base sm:text-lg' : 'max-w-2xl text-sm sm:text-base',
);
</script>

<template>
    <div :class="['flex flex-col', large ? 'gap-3' : 'gap-2', align === 'center' ? 'items-center text-center' : '']">
        <div v-if="number" class="flex items-center gap-3">
            <span
                :class="[
                    'font-semibold tabular-nums tracking-[0.2em] text-accent',
                    large ? 'text-xs sm:text-sm' : 'text-sm',
                ]"
            >{{ number }}</span>
            <span :class="['h-px bg-accent/40', large ? 'w-14' : 'w-8']" />
        </div>

        <component
            :is="as"
            :class="['font-semibold text-balance text-fg', titleClasses]"
        >{{ title }}</component>

        <p
            v-if="lead"
            :class="['leading-relaxed text-fg-muted', leadClasses, align === 'center' ? 'mx-auto' : '']"
        >{{ lead }}</p>

        <slot />
    </div>
</template>
