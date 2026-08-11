<script setup>
import Modal from './Modal.vue';
import Button from './Button.vue';

defineProps({
    open: { type: Boolean, default: false },
    title: { type: String, default: 'Are you sure?' },
    message: { type: String, default: 'This action cannot be undone.' },
    confirmLabel: { type: String, default: 'Delete' },
    cancelLabel: { type: String, default: 'Cancel' },
    tone: { type: String, default: 'danger' },
    processing: { type: Boolean, default: false },
});

defineEmits(['confirm', 'cancel']);
</script>

<template>
    <Modal :open="open" :title="title" size="sm" @close="$emit('cancel')">
        <p class="text-sm text-fg-muted">{{ message }}</p>

        <template #footer>
            <Button variant="ghost" @click="$emit('cancel')">{{ cancelLabel }}</Button>
            <Button :variant="tone" :loading="processing" autofocus @click="$emit('confirm')">
                {{ confirmLabel }}
            </Button>
        </template>
    </Modal>
</template>
