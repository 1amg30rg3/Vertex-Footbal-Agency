<script setup>
import { ref, watch } from 'vue';
import Icon from '@/Components/Ui/Icon.vue';

const props = defineProps({
    modelValue: { type: String, default: '' },
    placeholder: { type: String, default: 'Search…' },
    debounce: { type: Number, default: 300 },
    size: { type: String, default: 'md' },
});

const emit = defineEmits(['update:modelValue', 'search']);

const local = ref(props.modelValue ?? '');
let timer = null;

watch(
    () => props.modelValue,
    (value) => {
        if ((value ?? '') !== local.value) local.value = value ?? '';
    },
);

watch(local, (value) => {
    clearTimeout(timer);
    timer = setTimeout(() => {
        emit('update:modelValue', value);
        emit('search', value);
    }, props.debounce);
});

function clear() {
    local.value = '';
}
</script>

<template>
    <div class="relative">
        <Icon
            name="search"
            :size="size === 'sm' ? 14 : 16"
            class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-fg-subtle"
        />
        <input
            v-model="local"
            type="search"
            :placeholder="placeholder"
            :class="[
                'w-full rounded-lg border border-border bg-bg-elevated pl-9 pr-8 text-fg placeholder:text-fg-subtle',
                'transition-colors focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent-ring',
                size === 'sm' ? 'h-8 text-xs' : 'h-10 text-sm',
            ]"
        >
        <button
            v-if="local"
            type="button"
            class="absolute right-2.5 top-1/2 -translate-y-1/2 rounded p-0.5 text-fg-subtle transition-colors hover:text-fg"
            @click="clear"
        >
            <Icon name="close" :size="14" />
            <span class="sr-only">Clear</span>
        </button>
    </div>
</template>
