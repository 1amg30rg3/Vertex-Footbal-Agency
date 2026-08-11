<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: { type: String, default: '' },
    placeholder: { type: String, default: null },
    rows: { type: Number, default: 4 },
    disabled: { type: Boolean, default: false },
    invalid: { type: Boolean, default: false },
    id: { type: String, default: undefined },
    maxlength: { type: Number, default: undefined },
});

defineEmits(['update:modelValue']);

const classes = computed(() => [
    'w-full rounded-lg border bg-bg-elevated px-3 py-2.5 text-sm text-fg placeholder:text-fg-subtle',
    'transition-colors focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent-ring',
    'disabled:cursor-not-allowed disabled:opacity-60 resize-y',
    props.invalid ? 'border-danger' : 'border-border',
]);
</script>

<template>
    <textarea
        :id="id"
        :value="modelValue"
        :rows="rows"
        :placeholder="placeholder"
        :disabled="disabled"
        :maxlength="maxlength"
        :aria-invalid="invalid || undefined"
        :class="classes"
        @input="$emit('update:modelValue', $event.target.value)"
    />
</template>
