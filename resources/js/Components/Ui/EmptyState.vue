<script setup>
import Icon from './Icon.vue';

defineProps({
    icon: { type: String, default: 'search' },
    title: { type: String, required: true },
    description: { type: String, default: null },
});
</script>

<template>
    <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-border px-6 py-14 text-center">
        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-surface-2 text-fg-subtle">
            <Icon :name="icon" :size="22" />
        </div>
        <p class="text-base font-semibold text-fg">{{ title }}</p>
        <p v-if="description" class="mt-1.5 max-w-sm text-sm text-fg-muted">{{ description }}</p>
        <div class="mt-5 empty:hidden"><slot /></div>
    </div>
</template>
