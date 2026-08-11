<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import Icon from '@/Components/Ui/Icon.vue';

const props = defineProps({
    photos: { type: Array, default: () => [] },
    columns: { type: Number, default: 3 },
});

const openIndex = ref(null);

const current = computed(() =>
    openIndex.value === null ? null : (props.photos[openIndex.value] ?? null),
);

const gridClass = computed(
    () =>
        ({
            2: 'grid-cols-2',
            3: 'grid-cols-2 sm:grid-cols-3',
            4: 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-4',
        })[props.columns] ?? 'grid-cols-2 sm:grid-cols-3',
);

function open(index) {
    openIndex.value = index;
}

function close() {
    openIndex.value = null;
}

function step(delta) {
    if (openIndex.value === null || !props.photos.length) return;

    openIndex.value = (openIndex.value + delta + props.photos.length) % props.photos.length;
}

function onKeydown(event) {
    if (event.key === 'Escape') close();
    if (event.key === 'ArrowRight') step(1);
    if (event.key === 'ArrowLeft') step(-1);
}

watch(openIndex, (value) => {
    if (value === null) {
        document.removeEventListener('keydown', onKeydown);
        document.body.style.overflow = '';
    } else {
        document.addEventListener('keydown', onKeydown);
        document.body.style.overflow = 'hidden';
    }
});

onBeforeUnmount(() => {
    document.removeEventListener('keydown', onKeydown);
    document.body.style.overflow = '';
});
</script>

<template>
    <div>
        <ul :class="['grid gap-3', gridClass]">
            <li v-for="(photo, index) in photos" :key="photo.id ?? index">
                <button
                    type="button"
                    class="group relative block aspect-[4/3] w-full overflow-hidden rounded-xl border border-border bg-surface-2"
                    @click="open(index)"
                >
                    <img
                        :src="photo.url"
                        :alt="photo.caption || ''"
                        loading="lazy"
                        class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                    >
                    <span
                        v-if="photo.caption"
                        class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/75 to-transparent p-3 text-left text-xs font-medium text-white opacity-0 transition-opacity group-hover:opacity-100"
                    >{{ photo.caption }}</span>
                </button>
            </li>
        </ul>

        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-150"
                enter-from-class="opacity-0"
                leave-active-class="transition duration-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="current"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4"
                    role="dialog"
                    aria-modal="true"
                    @click.self="close"
                >
                    <button
                        type="button"
                        class="absolute right-4 top-4 rounded-lg p-2 text-white/70 transition hover:bg-white/10 hover:text-white"
                        @click="close"
                    >
                        <Icon name="close" :size="22" />
                        <span class="sr-only">Close</span>
                    </button>

                    <button
                        v-if="photos.length > 1"
                        type="button"
                        class="absolute left-3 rounded-full p-3 text-white/70 transition hover:bg-white/10 hover:text-white sm:left-6"
                        @click.stop="step(-1)"
                    >
                        <Icon name="chevronLeft" :size="24" />
                        <span class="sr-only">Previous</span>
                    </button>

                    <figure class="max-h-full max-w-5xl" @click.stop>
                        <img :src="current.url" :alt="current.caption || ''" class="max-h-[80vh] w-auto rounded-xl object-contain">
                        <figcaption v-if="current.caption" class="mt-3 text-center text-sm text-white/75">
                            {{ current.caption }}
                        </figcaption>
                        <p class="mt-1 text-center text-xs tabular-nums text-white/45">
                            {{ openIndex + 1 }} / {{ photos.length }}
                        </p>
                    </figure>

                    <button
                        v-if="photos.length > 1"
                        type="button"
                        class="absolute right-3 rounded-full p-3 text-white/70 transition hover:bg-white/10 hover:text-white sm:right-6"
                        @click.stop="step(1)"
                    >
                        <Icon name="chevronRight" :size="24" />
                        <span class="sr-only">Next</span>
                    </button>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
