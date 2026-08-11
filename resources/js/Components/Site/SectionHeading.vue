<script setup>
defineProps({
    number: { type: String, default: null },
    title: { type: String, required: true },
    lead: { type: String, default: null },
    align: { type: String, default: 'left', validator: (v) => ['left', 'center'].includes(v) },
    as: { type: String, default: 'h2' },
});
</script>

<template>
    <div :class="['flex flex-col gap-2', align === 'center' ? 'items-center text-center' : '']">
        <div class="flex items-center gap-3">
            <span
                v-if="number"
                class="text-sm font-semibold tabular-nums tracking-[0.2em] text-accent"
            >{{ number }}</span>
            <span v-if="number" class="h-px w-8 bg-accent/40" />
        </div>

        <component :is="as" class="text-2xl font-semibold tracking-tight text-balance text-fg sm:text-3xl">
            {{ title }}
        </component>

        <p v-if="lead" :class="['max-w-2xl text-sm leading-relaxed text-fg-muted sm:text-base', align === 'center' ? 'mx-auto' : '']">
            {{ lead }}
        </p>

        <slot />
    </div>
</template>
