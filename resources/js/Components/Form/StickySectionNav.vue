<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
    sections: { type: Array, required: true },
});

const active = ref(props.sections[0]?.id ?? null);
let observer = null;

onMounted(() => {
    observer = new IntersectionObserver(
        (entries) => {
            const visible = entries.filter((entry) => entry.isIntersecting);

            if (visible.length) active.value = visible[0].target.id;
        },
        { rootMargin: '-15% 0px -70% 0px', threshold: 0 },
    );

    props.sections.forEach((section) => {
        const element = document.getElementById(section.id);

        if (element) observer.observe(element);
    });
});

onBeforeUnmount(() => observer?.disconnect());

function go(id) {
    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}
</script>

<template>
    <nav class="sticky top-24 space-y-0.5">
        <button
            v-for="section in sections"
            :key="section.id"
            type="button"
            :class="[
                'flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-left text-sm transition-colors',
                active === section.id
                    ? 'bg-accent-soft font-medium text-accent'
                    : 'text-fg-muted hover:bg-surface-2 hover:text-fg',
            ]"
            @click="go(section.id)"
        >
            <span class="text-xs tabular-nums opacity-60">{{ section.number }}</span>
            <span class="truncate">{{ section.label }}</span>
        </button>
    </nav>
</template>
