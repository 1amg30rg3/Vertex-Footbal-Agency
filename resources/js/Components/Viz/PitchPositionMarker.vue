<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
    positions: { type: Array, default: () => [] },
    editable: { type: Boolean, default: false },
    label: { type: String, default: null },
    max: { type: Number, default: 11 },
});

const emit = defineEmits(['update:positions']);

const surface = ref(null);
const dragging = ref(false);
const active = ref(0);

const spots = computed(() => props.positions ?? []);
const atLimit = computed(() => spots.value.length >= props.max);

watch(
    () => spots.value.length,
    (length) => {
        if (active.value > length - 1) active.value = Math.max(0, length - 1);
    },
);

const clamp = (value) => Math.round(Math.max(0, Math.min(100, value)) * 10) / 10;

function pointFrom(event) {
    const rect = surface.value.getBoundingClientRect();
    const point = event.touches?.[0] ?? event;

    return {
        x: clamp(((point.clientX - rect.left) / rect.width) * 100),
        y: clamp(((point.clientY - rect.top) / rect.height) * 100),
    };
}

function replace(index, spot) {
    emit('update:positions', spots.value.map((item, i) => (i === index ? spot : item)));
}

function onPointerDown(event) {
    if (!props.editable || !surface.value) return;

    const spot = pointFrom(event);

    // An empty pitch, or a click with room left, drops a new marker; dragging
    // then moves whichever marker is active.
    if (!spots.value.length || (event.shiftKey && !atLimit.value)) {
        emit('update:positions', [...spots.value, spot]);
        active.value = spots.value.length;
    } else {
        replace(active.value, spot);
    }

    dragging.value = true;

    const move = (moveEvent) => dragging.value && replace(active.value, pointFrom(moveEvent));
    const up = () => {
        dragging.value = false;
        window.removeEventListener('pointermove', move);
        window.removeEventListener('pointerup', up);
    };

    window.addEventListener('pointermove', move);
    window.addEventListener('pointerup', up);
}

function addSpot() {
    if (atLimit.value) return;

    emit('update:positions', [...spots.value, { x: 50, y: 50 }]);
    active.value = spots.value.length;
}

function removeSpot(index) {
    emit('update:positions', spots.value.filter((_, i) => i !== index));
}

function nudge(dx, dy) {
    const spot = spots.value[active.value];

    if (!props.editable || !spot) return;

    replace(active.value, { x: clamp(spot.x + dx), y: clamp(spot.y + dy) });
}
</script>

<template>
    <div class="space-y-2">
        <div
            ref="surface"
            :class="[
                'relative aspect-[2/3] w-full select-none overflow-hidden rounded-xl border border-border bg-surface-2',
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
                v-for="(spot, index) in spots"
                :key="index"
                :class="[
                    'absolute z-10 -translate-x-1/2 -translate-y-1/2',
                    editable ? 'cursor-pointer' : 'pointer-events-none',
                ]"
                :style="{ left: `${spot.x}%`, top: `${spot.y}%` }"
                @pointerdown.stop="editable && (active = index)"
            >
                <span v-if="!editable" class="absolute inset-0 -m-3 animate-ping rounded-full bg-accent/25" />
                <span
                    :class="[
                        'relative block h-5 w-5 rounded-full border-2 shadow-lg',
                        editable && index === active
                            ? 'border-fg bg-accent ring-2 ring-accent-ring'
                            : 'border-accent-fg bg-accent',
                    ]"
                />

                <button
                    v-if="editable"
                    type="button"
                    class="absolute -right-2 -top-2 flex h-4 w-4 items-center justify-center rounded-full bg-danger text-[10px] font-bold leading-none text-white shadow"
                    :aria-label="`Remove position ${index + 1}`"
                    @pointerdown.stop
                    @click.stop="removeSpot(index)"
                >×</button>

                <span
                    v-if="label && !editable && spots.length === 1"
                    class="absolute left-1/2 top-full mt-1.5 -translate-x-1/2 whitespace-nowrap rounded-md bg-bg-elevated px-1.5 py-0.5 text-[10px] font-semibold text-fg shadow"
                >{{ label }}</span>
            </div>

            <p
                v-if="editable && !spots.length"
                class="absolute inset-0 flex items-center justify-center px-6 text-center text-xs text-fg-subtle"
            >
                Click anywhere on the pitch to place a position.
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

            <button
                type="button"
                class="rounded-lg border border-border px-2.5 py-1 text-xs font-medium text-fg-muted transition-colors hover:border-accent hover:text-accent disabled:opacity-40"
                :disabled="atLimit"
                @click="addSpot"
            >Add position</button>

            <span class="text-[11px] text-fg-subtle">
                {{ spots.length }}/{{ max }} — click to move the selected one, shift-click to add
            </span>
        </div>
    </div>
</template>
