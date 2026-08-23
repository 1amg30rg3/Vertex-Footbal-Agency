<script setup>
import { computed } from 'vue';

const props = defineProps({
    items: { type: Array, default: () => [] },
    pace: { type: Number, default: 3.4 },
});

const entries = computed(() => props.items.filter((item) => !!item));

const pass = computed(() => {
    if (!entries.value.length) return [];

    const filled = [...entries.value];

    while (filled.length < 4) filled.push(...entries.value);

    return filled;
});

const duration = computed(() => `${Math.max(30, Math.round(pass.value.length * props.pace))}s`);
</script>

<template>
    <div v-if="entries.length" class="marquee relative flex overflow-hidden">
        <div
            class="marquee-track flex w-max shrink-0 items-center"
            :style="{ '--marquee-duration': duration }"
            aria-hidden="true"
        >
            <template v-for="copy in 2" :key="copy">
                <span v-for="(item, index) in pass" :key="`${copy}-${index}`" class="flex items-center">
                    <span class="whitespace-nowrap font-heading text-sm font-semibold uppercase tracking-[0.16em] text-fg-muted">
                        {{ item }}
                    </span>
                    <span class="mx-7 size-1.5 shrink-0 rotate-45 bg-accent/70" />
                </span>
            </template>
        </div>

        <ul class="sr-only">
            <li v-for="item in entries" :key="item">{{ item }}</li>
        </ul>
    </div>
</template>
