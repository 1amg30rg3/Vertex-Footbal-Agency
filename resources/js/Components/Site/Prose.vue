<script setup>
defineProps({
    html: { type: String, default: '' },
    size: { type: String, default: 'md', validator: (v) => ['sm', 'md', 'lg'].includes(v) },
});
</script>

<template>
    <div
        v-if="html"
        class="prose-content"
        :class="{ 'text-sm': size === 'sm', 'sm:text-lg': size === 'lg' }"
        v-html="html"
    />
</template>
