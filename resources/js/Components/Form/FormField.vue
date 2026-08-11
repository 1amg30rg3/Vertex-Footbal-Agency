<script setup>
import { computed, useId } from 'vue';

const props = defineProps({
    label: { type: String, default: null },
    hint: { type: String, default: null },
    error: { type: String, default: null },
    required: { type: Boolean, default: false },
    inline: { type: Boolean, default: false },
    for: { type: String, default: null },
});

const generated = useId();
const id = computed(() => props.for ?? generated);
</script>

<template>
    <div :class="inline ? 'flex items-center gap-3' : 'space-y-1.5'">
        <label v-if="label" :for="id" class="block text-sm font-medium text-fg">
            {{ label }}
            <span v-if="required" class="text-danger">*</span>
        </label>

        <div :class="inline ? 'flex-1' : ''">
            <slot :id="id" :invalid="!!error" />
            <p v-if="error" class="mt-1.5 text-xs text-danger">{{ error }}</p>
            <p v-else-if="hint" class="mt-1.5 text-xs text-fg-subtle">{{ hint }}</p>
        </div>
    </div>
</template>
