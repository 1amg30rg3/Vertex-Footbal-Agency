<script setup>
import Icon from '@/Components/Ui/Icon.vue';

defineProps({
    label: { type: String, required: true },
    value: { type: [String, Number], required: true },
    sublabel: { type: String, default: null },
    icon: { type: String, default: null },
    tone: { type: String, default: 'default', validator: (v) => ['default', 'accent'].includes(v) },
});
</script>

<template>
    <div class="rounded-xl border border-border bg-surface p-4">
        <div class="flex items-center justify-between gap-2">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-fg-subtle">{{ label }}</p>
            <Icon v-if="icon" :name="icon" :size="15" class="text-fg-subtle" />
        </div>
        <p
            :class="[
                'mt-2 text-2xl font-semibold tabular-nums tracking-tight sm:text-3xl',
                tone === 'accent' ? 'text-accent' : 'text-fg',
            ]"
        >{{ value }}</p>
        <p v-if="sublabel" class="mt-1 text-xs text-fg-muted">{{ sublabel }}</p>
    </div>
</template>
