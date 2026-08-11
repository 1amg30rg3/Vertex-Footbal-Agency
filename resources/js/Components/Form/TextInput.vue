<script setup>
import { computed } from 'vue';
import Icon from '@/Components/Ui/Icon.vue';

const props = defineProps({
    modelValue: { type: [String, Number], default: '' },
    type: { type: String, default: 'text' },
    placeholder: { type: String, default: null },
    disabled: { type: Boolean, default: false },
    invalid: { type: Boolean, default: false },
    icon: { type: String, default: null },
    suffix: { type: String, default: null },
    size: { type: String, default: 'md', validator: (v) => ['sm', 'md'].includes(v) },
    id: { type: String, default: undefined },
    min: { type: [String, Number], default: undefined },
    max: { type: [String, Number], default: undefined },
    step: { type: [String, Number], default: undefined },
    autocomplete: { type: String, default: undefined },
});

const emit = defineEmits(['update:modelValue']);

const classes = computed(() => [
    'w-full rounded-lg border bg-bg-elevated text-fg placeholder:text-fg-subtle',
    'transition-colors focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent-ring',
    'disabled:cursor-not-allowed disabled:opacity-60',
    props.size === 'sm' ? 'h-8 text-xs px-2.5' : 'h-10 text-sm px-3',
    props.icon ? (props.size === 'sm' ? 'pl-8' : 'pl-9.5') : '',
    props.suffix ? 'pr-12' : '',
    props.invalid ? 'border-danger' : 'border-border',
]);

function onInput(event) {
    const value = event.target.value;

    emit('update:modelValue', props.type === 'number' && value !== '' ? Number(value) : value);
}
</script>

<template>
    <div class="relative">
        <Icon
            v-if="icon"
            :name="icon"
            :size="size === 'sm' ? 14 : 16"
            :class="['pointer-events-none absolute top-1/2 -translate-y-1/2 text-fg-subtle', size === 'sm' ? 'left-2.5' : 'left-3']"
        />
        <input
            :id="id"
            :value="modelValue"
            :type="type"
            :placeholder="placeholder"
            :disabled="disabled"
            :min="min"
            :max="max"
            :step="step"
            :autocomplete="autocomplete"
            :aria-invalid="invalid || undefined"
            :class="classes"
            @input="onInput"
        >
        <span
            v-if="suffix"
            class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-fg-subtle"
        >{{ suffix }}</span>
    </div>
</template>
