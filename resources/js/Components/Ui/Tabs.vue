<script setup>
import { computed } from 'vue';
import Icon from './Icon.vue';

const props = defineProps({
    modelValue: { type: [String, Number], required: true },
    tabs: { type: Array, required: true },
    variant: { type: String, default: 'pill', validator: (v) => ['pill', 'underline'].includes(v) },
    size: { type: String, default: 'md', validator: (v) => ['sm', 'md'].includes(v) },
});

const emit = defineEmits(['update:modelValue']);

const wrapper = computed(() =>
    props.variant === 'pill'
        ? 'inline-flex items-center gap-1 rounded-xl border border-border bg-surface-2 p-1'
        : 'flex items-center gap-1 border-b border-border',
);

function tabClass(active) {
    const base = props.size === 'sm' ? 'text-xs h-7 px-2.5' : 'text-sm h-9 px-3.5';

    if (props.variant === 'pill') {
        return [
            base,
            'inline-flex items-center gap-1.5 rounded-lg font-medium transition-colors',
            active ? 'bg-accent text-accent-fg' : 'text-fg-muted hover:text-fg hover:bg-surface-3',
        ];
    }

    return [
        base,
        'relative inline-flex items-center gap-1.5 font-medium transition-colors -mb-px border-b-2',
        active ? 'border-accent text-fg' : 'border-transparent text-fg-muted hover:text-fg',
    ];
}
</script>

<template>
    <div :class="wrapper" role="tablist">
        <button
            v-for="tab in tabs"
            :key="tab.value"
            type="button"
            role="tab"
            :aria-selected="modelValue === tab.value"
            :class="tabClass(modelValue === tab.value)"
            @click="emit('update:modelValue', tab.value)"
        >
            <Icon v-if="tab.icon" :name="tab.icon" :size="size === 'sm' ? 13 : 15" />
            <span>{{ tab.label }}</span>
            <span
                v-if="tab.dot"
                class="h-1.5 w-1.5 rounded-full"
                :class="modelValue === tab.value ? 'bg-current' : 'bg-accent'"
            />
            <span
                v-else-if="tab.badge != null"
                class="rounded-full bg-surface px-1.5 text-[10px] font-semibold text-fg-muted"
            >{{ tab.badge }}</span>
        </button>
    </div>
</template>
