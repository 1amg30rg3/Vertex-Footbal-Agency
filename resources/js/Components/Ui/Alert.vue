<script setup>
import { computed } from 'vue';
import Icon from './Icon.vue';

const props = defineProps({
    tone: { type: String, default: 'info', validator: (v) => ['info', 'success', 'warning', 'danger'].includes(v) },
    title: { type: String, default: null },
    dismissible: { type: Boolean, default: false },
});

defineEmits(['dismiss']);

const tones = {
    info: { wrap: 'bg-info-soft border-info/30 text-info', icon: 'info' },
    success: { wrap: 'bg-success-soft border-success/30 text-success', icon: 'check' },
    warning: { wrap: 'bg-warning-soft border-warning/30 text-warning', icon: 'warning' },
    danger: { wrap: 'bg-danger-soft border-danger/30 text-danger', icon: 'alert' },
};

const tone = computed(() => tones[props.tone]);
</script>

<template>
    <div :class="['flex gap-3 rounded-xl border px-4 py-3 text-sm', tone.wrap]" role="alert">
        <Icon :name="tone.icon" :size="18" class="mt-0.5" />
        <div class="min-w-0 flex-1">
            <p v-if="title" class="font-semibold">{{ title }}</p>
            <div :class="title ? 'mt-0.5 opacity-90' : ''"><slot /></div>
        </div>
        <button
            v-if="dismissible"
            type="button"
            class="-m-1 shrink-0 self-start rounded p-1 opacity-70 transition hover:opacity-100"
            @click="$emit('dismiss')"
        >
            <Icon name="close" :size="16" />
            <span class="sr-only">Close</span>
        </button>
    </div>
</template>
