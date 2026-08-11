<script setup>
import { computed } from 'vue';
import Icon from '@/Components/Ui/Icon.vue';

const props = defineProps({
    modelValue: { type: [String, Number, null], default: null },
    options: { type: Array, default: () => [] },
    placeholder: { type: String, default: null },
    disabled: { type: Boolean, default: false },
    invalid: { type: Boolean, default: false },
    size: { type: String, default: 'md', validator: (v) => ['sm', 'md'].includes(v) },
    id: { type: String, default: undefined },
});

const emit = defineEmits(['update:modelValue']);

const normalized = computed(() =>
    props.options.map((option) =>
        typeof option === 'object' && option !== null
            ? { value: option.value, label: option.label }
            : { value: option, label: String(option) },
    ),
);

const classes = computed(() => [
    'w-full appearance-none rounded-lg border bg-bg-elevated pr-9 text-fg',
    'transition-colors focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent-ring',
    'disabled:cursor-not-allowed disabled:opacity-60',
    props.size === 'sm' ? 'h-8 text-xs pl-2.5' : 'h-10 text-sm pl-3',
    props.invalid ? 'border-danger' : 'border-border',
    props.modelValue === null || props.modelValue === '' ? 'text-fg-subtle' : '',
]);

function onChange(event) {
    const raw = event.target.value;

    if (raw === '') return emit('update:modelValue', null);

    const match = normalized.value.find((option) => String(option.value) === raw);

    emit('update:modelValue', match ? match.value : raw);
}
</script>

<template>
    <div class="relative">
        <select
            :id="id"
            :value="modelValue === null ? '' : String(modelValue)"
            :disabled="disabled"
            :aria-invalid="invalid || undefined"
            :class="classes"
            @change="onChange"
        >
            <option v-if="placeholder" value="">{{ placeholder }}</option>
            <option v-for="option in normalized" :key="String(option.value)" :value="String(option.value)">
                {{ option.label }}
            </option>
        </select>
        <Icon
            name="chevronDown"
            :size="15"
            class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-fg-subtle"
        />
    </div>
</template>
