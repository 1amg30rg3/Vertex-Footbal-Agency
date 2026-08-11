<script setup>
import { computed, ref, watch } from 'vue';
import { CircleStencil, Cropper } from 'vue-advanced-cropper';
import 'vue-advanced-cropper/dist/style.css';
import Icon from '@/Components/Ui/Icon.vue';
import Modal from '@/Components/Ui/Modal.vue';
import Button from '@/Components/Ui/Button.vue';

const props = defineProps({
    modelValue: { type: String, default: null },
    previewUrl: { type: String, default: null },
    label: { type: String, default: null },
    hint: { type: String, default: null },
    error: { type: String, default: null },
    aspectRatio: { type: Number, default: null },
    maxSize: { type: Number, default: 1600 },
    shape: { type: String, default: 'rect', validator: (v) => ['rect', 'circle'].includes(v) },
    height: { type: String, default: 'h-44' },
    croppable: { type: Boolean, default: true },
});

const emit = defineEmits(['update:modelValue']);

const input = ref(null);
const cropperRef = ref(null);
const cropModal = ref(false);
const sourceImage = ref(null);
const sourceType = ref('image/jpeg');
const localPreview = ref(null);
const dragging = ref(false);

const preview = computed(() => {
    if (localPreview.value) return localPreview.value;
    if (props.modelValue?.startsWith('data:')) return props.modelValue;
    if (props.modelValue && props.previewUrl) return props.previewUrl;
    if (!props.modelValue) return null;

    return props.previewUrl;
});

watch(
    () => props.modelValue,
    (value) => {
        if (!value) localPreview.value = null;
    },
);

function pick() {
    input.value?.click();
}

function onFile(event) {
    const file = event.target.files?.[0];

    if (file) read(file);
    event.target.value = '';
}

function onDrop(event) {
    dragging.value = false;
    const file = event.dataTransfer?.files?.[0];

    if (file?.type?.startsWith('image/')) read(file);
}

function read(file) {
    sourceType.value = file.type === 'image/png' || file.type === 'image/webp' ? file.type : 'image/jpeg';

    const reader = new FileReader();

    reader.onload = () => {
        if (file.type === 'image/svg+xml') {
            commit(reader.result);

            return;
        }

        sourceImage.value = reader.result;

        if (props.croppable) {
            cropModal.value = true;
        } else {
            downscale(reader.result).then(commit);
        }
    };

    reader.readAsDataURL(file);
}

function downscale(dataUri) {
    return new Promise((resolve) => {
        const image = new window.Image();

        image.onload = () => {
            const scale = Math.min(1, props.maxSize / Math.max(image.width, image.height));

            if (scale === 1 && dataUri.length < 900_000) return resolve(dataUri);

            const canvas = document.createElement('canvas');
            canvas.width = Math.round(image.width * scale);
            canvas.height = Math.round(image.height * scale);
            canvas.getContext('2d').drawImage(image, 0, 0, canvas.width, canvas.height);

            resolve(canvas.toDataURL(sourceType.value, 0.88));
        };

        image.onerror = () => resolve(dataUri);
        image.src = dataUri;
    });
}

function applyCrop() {
    const { canvas } = cropperRef.value?.getResult() ?? {};

    if (!canvas) return;

    const scale = Math.min(1, props.maxSize / Math.max(canvas.width, canvas.height));
    let output = canvas;

    if (scale < 1) {
        output = document.createElement('canvas');
        output.width = Math.round(canvas.width * scale);
        output.height = Math.round(canvas.height * scale);
        output.getContext('2d').drawImage(canvas, 0, 0, output.width, output.height);
    }

    commit(output.toDataURL(sourceType.value, 0.88));
    closeCrop();
}

function useOriginal() {
    downscale(sourceImage.value).then(commit);
    closeCrop();
}

function commit(dataUri) {
    localPreview.value = dataUri;
    emit('update:modelValue', dataUri);
}

function closeCrop() {
    cropModal.value = false;
    sourceImage.value = null;
}

function clear() {
    localPreview.value = null;
    emit('update:modelValue', null);
}
</script>

<template>
    <div class="space-y-1.5">
        <label v-if="label" class="block text-sm font-medium text-fg">{{ label }}</label>

        <div
            :class="[
                'group relative flex w-full items-center justify-center overflow-hidden rounded-xl border-2 border-dashed transition-colors',
                height,
                error ? 'border-danger' : dragging ? 'border-accent bg-accent-soft' : 'border-border hover:border-accent/60',
                shape === 'circle' && !preview ? 'rounded-full' : '',
            ]"
            @dragover.prevent="dragging = true"
            @dragleave.prevent="dragging = false"
            @drop.prevent="onDrop"
        >
            <img
                v-if="preview"
                :src="preview"
                alt=""
                :class="['h-full w-full', shape === 'circle' ? 'rounded-full object-cover' : 'object-contain']"
            >

            <button
                v-else
                type="button"
                class="flex h-full w-full flex-col items-center justify-center gap-2 text-fg-subtle transition-colors hover:text-accent"
                @click="pick"
            >
                <Icon name="upload" :size="22" />
                <span class="text-xs font-medium">Click or drop an image</span>
            </button>

            <div
                v-if="preview"
                class="absolute inset-0 flex items-center justify-center gap-2 bg-overlay opacity-0 backdrop-blur-[2px] transition-opacity group-hover:opacity-100"
            >
                <Button size="sm" variant="secondary" icon="upload" @click="pick">Replace</Button>
                <Button size="sm" variant="danger" icon="trash" square @click="clear">
                    <span class="sr-only">Remove</span>
                </Button>
            </div>
        </div>

        <p v-if="error" class="text-xs text-danger">{{ error }}</p>
        <p v-else-if="hint" class="text-xs text-fg-subtle">{{ hint }}</p>

        <input ref="input" type="file" accept="image/*" class="hidden" @change="onFile">

        <Modal :open="cropModal" title="Crop image" size="xl" @close="closeCrop">
            <div class="overflow-hidden rounded-xl bg-surface-3" style="max-height: 60vh">
                <Cropper
                    v-if="sourceImage"
                    ref="cropperRef"
                    :src="sourceImage"
                    :stencil-props="aspectRatio ? { aspectRatio } : {}"
                    :stencil-component="shape === 'circle' ? CircleStencil : undefined"
                    class="max-h-[60vh]"
                />
            </div>

            <template #footer>
                <Button variant="ghost" @click="closeCrop">Cancel</Button>
                <Button variant="secondary" @click="useOriginal">Use original</Button>
                <Button icon="check" @click="applyCrop">Apply crop</Button>
            </template>
        </Modal>
    </div>
</template>
