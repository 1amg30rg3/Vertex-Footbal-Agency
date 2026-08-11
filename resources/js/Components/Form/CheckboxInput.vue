<script setup>
import Icon from '@/Components/Ui/Icon.vue';

defineProps({
    modelValue: { type: Boolean, default: false },
    label: { type: String, default: null },
    hint: { type: String, default: null },
    disabled: { type: Boolean, default: false },
});

defineEmits(['update:modelValue']);
</script>

<template>
    <label :class="['group flex items-start gap-2.5', disabled ? 'cursor-not-allowed opacity-60' : 'cursor-pointer']">
        <span class="relative mt-0.5 flex h-[18px] w-[18px] shrink-0 items-center justify-center">
            <input
                type="checkbox"
                class="peer sr-only"
                :checked="modelValue"
                :disabled="disabled"
                @change="$emit('update:modelValue', $event.target.checked)"
            >
            <span
                class="absolute inset-0 rounded-[5px] border border-border bg-bg-elevated transition-colors peer-checked:border-accent peer-checked:bg-accent peer-focus-visible:ring-2 peer-focus-visible:ring-accent-ring"
            />
            <Icon
                name="check"
                :size="12"
                :stroke-width="3"
                class="relative text-accent-fg opacity-0 transition-opacity peer-checked:opacity-100"
            />
        </span>
        <span v-if="label || hint" class="min-w-0">
            <span v-if="label" class="block text-sm text-fg">{{ label }}</span>
            <span v-if="hint" class="block text-xs text-fg-subtle">{{ hint }}</span>
        </span>
        <slot />
    </label>
</template>
