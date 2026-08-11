<script setup>
defineProps({
    modelValue: { type: Boolean, default: false },
    label: { type: String, default: null },
    hint: { type: String, default: null },
    disabled: { type: Boolean, default: false },
});

defineEmits(['update:modelValue']);
</script>

<template>
    <label :class="['flex items-center justify-between gap-4', disabled ? 'opacity-60' : 'cursor-pointer']">
        <span v-if="label || hint" class="min-w-0">
            <span v-if="label" class="block text-sm font-medium text-fg">{{ label }}</span>
            <span v-if="hint" class="mt-0.5 block text-xs text-fg-subtle">{{ hint }}</span>
        </span>

        <span class="relative inline-flex h-6 w-11 shrink-0 items-center">
            <input
                type="checkbox"
                class="peer sr-only"
                :checked="modelValue"
                :disabled="disabled"
                @change="$emit('update:modelValue', $event.target.checked)"
            >
            <span class="absolute inset-0 rounded-full border border-border bg-surface-3 transition-colors peer-checked:border-accent peer-checked:bg-accent peer-focus-visible:ring-2 peer-focus-visible:ring-accent-ring" />
            <span class="absolute left-0.5 h-5 w-5 rounded-full bg-bg-elevated shadow transition-transform peer-checked:translate-x-5" />
        </span>
    </label>
</template>
