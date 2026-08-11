<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    x: { type: Number, default: null },
    y: { type: Number, default: null },
    editable: { type: Boolean, default: false },
    label: { type: String, default: null },
});

const emit = defineEmits(['update:x', 'update:y']);

const surface = ref(null);
const dragging = ref(false);

const hasMarker = computed(() => props.x !== null && props.y !== null);
const left = computed(() => `${Math.max(0, Math.min(100, props.x ?? 50))}%`);
const top = computed(() => `${Math.max(0, Math.min(100, props.y ?? 50))}%`);

function setFromEvent(event) {
    if (!props.editable || !surface.value) return;

    const rect = surface.value.getBoundingClientRect();
    const point = event.touches?.[0] ?? event;
    const nextX = ((point.clientX - rect.left) / rect.width) * 100;
    const nextY = ((point.clientY - rect.top) / rect.height) * 100;

    emit('update:x', Math.round(Math.max(0, Math.min(100, nextX)) * 10) / 10);
    emit('update:y', Math.round(Math.max(0, Math.min(100, nextY)) * 10) / 10);
}

function onPointerDown(event) {
    if (!props.editable) return;

    dragging.value = true;
    setFromEvent(event);

    const move = (moveEvent) => dragging.value && setFromEvent(moveEvent);
    const up = () => {
        dragging.value = false;
        window.removeEventListener('pointermove', move);
        window.removeEventListener('pointerup', up);
    };

    window.addEventListener('pointermove', move);
    window.addEventListener('pointerup', up);
}

function nudge(dx, dy) {
    if (!props.editable) return;

    emit('update:x', Math.round(Math.max(0, Math.min(100, (props.x ?? 50) + dx)) * 10) / 10);
    emit('update:y', Math.round(Math.max(0, Math.min(100, (props.y ?? 50) + dy)) * 10) / 10);
}
</script>

<template>
    <div class="space-y-2">
        <div
            ref="surface"
            :class="[
                'relative aspect-[2/3] w-full overflow-hidden rounded-xl border border-border bg-surface-2 select-none',
                editable ? 'cursor-crosshair' : '',
            ]"
            @pointerdown="onPointerDown"
        >
            <svg viewBox="0 0 100 150" class="absolute inset-0 h-full w-full" preserveAspectRatio="none" aria-hidden="true">
                <g fill="none" stroke="currentColor" class="text-border-strong" stroke-width="0.6" vector-effect="non-scaling-stroke">
                    <rect x="2" y="2" width="96" height="146" rx="1" />
                    <line x1="2" y1="75" x2="98" y2="75" />
                    <circle cx="50" cy="75" r="14" />
                    <circle cx="50" cy="75" r="0.9" fill="currentColor" stroke="none" />
                    <rect x="21" y="2" width="58" height="24" />
                    <rect x="36" y="2" width="28" height="10" />
                    <rect x="21" y="124" width="58" height="24" />
                    <rect x="36" y="138" width="28" height="10" />
                    <circle cx="50" cy="18" r="0.9" fill="currentColor" stroke="none" />
                    <circle cx="50" cy="132" r="0.9" fill="currentColor" stroke="none" />
                </g>
            </svg>

            <div
                v-if="hasMarker"
                class="pointer-events-none absolute z-10 -translate-x-1/2 -translate-y-1/2"
                :style="{ left, top }"
            >
                <span class="absolute inset-0 -m-3 animate-ping rounded-full bg-accent/25" v-if="!editable" />
                <span class="relative block h-5 w-5 rounded-full border-2 border-accent-fg bg-accent shadow-lg" />
                <span
                    v-if="label"
                    class="absolute left-1/2 top-full mt-1.5 -translate-x-1/2 whitespace-nowrap rounded-md bg-bg-elevated px-1.5 py-0.5 text-[10px] font-semibold text-fg shadow"
                >{{ label }}</span>
            </div>

            <p
                v-else-if="editable"
                class="absolute inset-0 flex items-center justify-center px-6 text-center text-xs text-fg-subtle"
            >
                Click anywhere on the pitch to place the marker.
            </p>
        </div>

        <div v-if="editable" class="flex flex-wrap items-center gap-2">
            <div class="inline-grid grid-cols-3 gap-0.5" role="group" aria-label="Nudge marker">
                <span />
                <button type="button" class="rounded bg-surface-2 px-2 py-0.5 text-xs text-fg-muted hover:bg-surface-3" @click="nudge(0, -1)">↑</button>
                <span />
                <button type="button" class="rounded bg-surface-2 px-2 py-0.5 text-xs text-fg-muted hover:bg-surface-3" @click="nudge(-1, 0)">←</button>
                <button type="button" class="rounded bg-surface-2 px-2 py-0.5 text-xs text-fg-muted hover:bg-surface-3" @click="nudge(0, 1)">↓</button>
                <button type="button" class="rounded bg-surface-2 px-2 py-0.5 text-xs text-fg-muted hover:bg-surface-3" @click="nudge(1, 0)">→</button>
            </div>
            <p class="text-xs tabular-nums text-fg-subtle">
                x {{ (x ?? 50).toFixed(1) }}% · y {{ (y ?? 50).toFixed(1) }}%
            </p>
        </div>
    </div>
</template>
