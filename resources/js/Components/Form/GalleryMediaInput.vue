<script setup>
import { computed, ref, watch } from 'vue';
import { useRoute } from '@/Support/ziggy';
import ImageUploader from '@/Components/Form/ImageUploader.vue';
import TextInput from '@/Components/Form/TextInput.vue';
import Icon from '@/Components/Ui/Icon.vue';

const props = defineProps({
    modelValue: { type: String, default: null },
    previewUrl: { type: String, default: null },
    kind: { type: String, default: null },
    error: { type: String, default: null },
});

const emit = defineEmits(['update:modelValue']);

const route = useRoute();

const EXTERNAL = /^(https?:)?\/\//i;
const VIDEO_FILE = /\.(mp4|webm|mov|m4v)(\?|$)/i;

const MAX_VIDEO_BYTES = 100 * 1024 * 1024;

const MODES = [
    { id: 'image', label: 'Image', icon: 'image' },
    { id: 'video', label: 'Video file', icon: 'upload' },
    { id: 'link', label: 'Video link', icon: 'externalLink' },
];

function detectMode(value, kind) {
    if (kind === 'embed') return 'link';
    if (!value) return 'image';
    if (EXTERNAL.test(value)) return 'link';
    if (value.startsWith('data:video') || VIDEO_FILE.test(value)) return 'video';

    return 'image';
}

const mode = ref(detectMode(props.modelValue, props.kind));
const link = ref(EXTERNAL.test(props.modelValue ?? '') ? props.modelValue : '');

const fileInput = ref(null);
const uploading = ref(false);
const progress = ref(0);
const uploadError = ref('');
const uploadedUrl = ref(null);

const videoPreview = computed(() => uploadedUrl.value ?? props.previewUrl);

watch(link, (value) => {
    if (mode.value === 'link') emit('update:modelValue', value?.trim() || null);
});

function setMode(next) {
    if (mode.value === next) return;

    mode.value = next;
    uploadError.value = '';
    uploadedUrl.value = null;
    link.value = '';

    emit('update:modelValue', null);
}

function onFileChosen(event) {
    const file = event.target.files?.[0];

    if (file && file.size > MAX_VIDEO_BYTES) {
        uploadError.value = `That video is ${(file.size / 1024 / 1024).toFixed(0)} MB. The limit is ${MAX_VIDEO_BYTES / 1024 / 1024} MB — use a YouTube or Vimeo link instead.`;
    } else if (file) {
        upload(file);
    }

    event.target.value = '';
}

function upload(file) {
    uploading.value = true;
    progress.value = 0;
    uploadError.value = '';

    const body = new FormData();
    body.append('file', file);

    const xhr = new XMLHttpRequest();

    xhr.open('POST', route('admin.media.video'));
    xhr.withCredentials = true;
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.setRequestHeader(
        'X-CSRF-TOKEN',
        document.querySelector('meta[name="csrf-token"]')?.content ?? '',
    );

    xhr.upload.onprogress = (event) => {
        if (event.lengthComputable) progress.value = Math.round((event.loaded / event.total) * 100);
    };

    xhr.onload = () => {
        uploading.value = false;

        let payload = null;

        try {
            payload = JSON.parse(xhr.responseText);
        } catch {
            payload = null;
        }

        if (xhr.status >= 200 && xhr.status < 300 && payload?.path) {
            uploadedUrl.value = payload.url;
            emit('update:modelValue', payload.path);
            return;
        }

        uploadError.value =
            payload?.errors?.file?.[0] ?? payload?.message ?? `Upload failed (${xhr.status}).`;
    };

    xhr.onerror = () => {
        uploading.value = false;
        uploadError.value = 'Upload failed. Check your connection and try again.';
    };

    xhr.send(body);
}

function clearVideo() {
    uploadedUrl.value = null;
    uploadError.value = '';
    emit('update:modelValue', null);
}
</script>

<template>
    <div class="space-y-2">
        <div class="flex flex-wrap items-center gap-1 rounded-xl border border-border bg-surface-2 p-1">
            <button
                v-for="item in MODES"
                :key="item.id"
                type="button"
                :class="[
                    'inline-flex h-7 flex-1 items-center justify-center gap-1.5 rounded-lg px-2 text-[11px] font-medium transition-colors',
                    mode === item.id
                        ? 'bg-accent text-accent-fg'
                        : 'text-fg-muted hover:bg-surface-3 hover:text-fg',
                ]"
                @click="setMode(item.id)"
            >
                <Icon :name="item.icon" :size="12" />
                {{ item.label }}
            </button>
        </div>

        <ImageUploader
            v-if="mode === 'image'"
            :model-value="modelValue"
            :preview-url="previewUrl"
            :aspect-ratio="4 / 3"
            height="h-32"
            :error="error"
            @update:model-value="emit('update:modelValue', $event)"
        />

        <div v-else-if="mode === 'video'" class="space-y-2">
            <video
                v-if="videoPreview && !uploading"
                :src="videoPreview"
                controls
                preload="metadata"
                class="h-32 w-full rounded-xl bg-black object-contain"
            />

            <div
                v-else-if="uploading"
                class="flex h-32 w-full flex-col items-center justify-center gap-2 rounded-xl border border-border bg-surface-2 px-4"
            >
                <div class="h-1.5 w-full overflow-hidden rounded-full bg-surface-3">
                    <div class="h-full bg-accent transition-all" :style="{ width: `${progress}%` }" />
                </div>
                <span class="text-[11px] text-fg-muted">Uploading… {{ progress }}%</span>
            </div>

            <button
                v-else
                type="button"
                class="flex h-32 w-full flex-col items-center justify-center gap-1.5 rounded-xl border border-dashed border-border text-fg-subtle transition-colors hover:border-accent hover:text-accent"
                @click="fileInput?.click()"
            >
                <Icon name="upload" :size="18" />
                <span class="text-[11px]">Choose a video</span>
                <span class="text-[10px] text-fg-subtle">MP4, WebM or MOV</span>
            </button>

            <input
                ref="fileInput"
                type="file"
                accept="video/mp4,video/webm,video/quicktime"
                class="hidden"
                @change="onFileChosen"
            >

            <button
                v-if="videoPreview && !uploading"
                type="button"
                class="inline-flex items-center gap-1 text-[11px] font-medium text-fg-subtle transition-colors hover:text-danger"
                @click="clearVideo"
            >
                <Icon name="trash" :size="12" />
                Remove video
            </button>

            <p v-if="uploadError" class="text-xs text-danger">{{ uploadError }}</p>
            <p v-else-if="error" class="text-xs text-danger">{{ error }}</p>
        </div>

        <div v-else class="space-y-2">
            <TextInput
                v-model="link"
                size="sm"
                placeholder="https://youtube.com/watch?v=…"
                :invalid="!!error"
            />

            <p v-if="error" class="text-xs text-danger">{{ error }}</p>
            <p v-else class="text-[11px] text-fg-subtle">
                YouTube or Vimeo. Nothing is stored on your server and the video streams from there.
            </p>
        </div>
    </div>
</template>
