<script setup>
defineProps({ size: { type: Number, default: 20 } });
</script>

<template>
    <svg class="animate-spin text-accent" :width="size" :height="size" viewBox="0 0 24 24" fill="none" role="status">
        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2.5" opacity="0.25" />
        <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
    </svg>
</template>
