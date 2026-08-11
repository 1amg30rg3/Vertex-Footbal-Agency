<script setup>
defineProps({
    id: { type: String, required: true },
    number: { type: String, default: null },
    title: { type: String, required: true },
    description: { type: String, default: null },
});
</script>

<template>
    <section :id="id" class="scroll-mt-28 rounded-2xl border border-border bg-surface">
        <header class="flex flex-wrap items-start justify-between gap-3 border-b border-border px-5 py-4 sm:px-6">
            <div class="min-w-0">
                <div class="flex items-center gap-2.5">
                    <span v-if="number" class="text-xs font-semibold tabular-nums tracking-[0.2em] text-accent">
                        {{ number }}
                    </span>
                    <h2 class="text-base font-semibold tracking-tight text-fg">{{ title }}</h2>
                </div>
                <p v-if="description" class="mt-1 text-xs text-fg-muted">{{ description }}</p>
            </div>
            <slot name="actions" />
        </header>

        <div class="space-y-5 px-5 py-5 sm:px-6 sm:py-6">
            <slot />
        </div>
    </section>
</template>
