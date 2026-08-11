<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
    src: { type: String, default: '/cover.mp4' },
    poster: { type: String, default: '/cover-poster.jpg' },
    /** How much slower than the page the footage travels. 0 = pinned, 1 = static. */
    depth: { type: Number, default: 0.4 },
    minHeight: { type: String, default: 'min-h-[100svh]' },
});

const root = ref(null);
const media = ref(null);
const content = ref(null);
const video = ref(null);

const reduced = ref(false);
const paused = ref(false);
const ready = ref(false);

let frame = null;
let visible = true;
let observer = null;

function apply() {
    frame = null;

    if (!root.value || reduced.value) return;

    const height = root.value.offsetHeight || 1;
    const scrolled = Math.min(Math.max(-root.value.getBoundingClientRect().top, 0), height);
    const progress = scrolled / height;

    // Footage drifts down as the page moves up, so it reads as further away.
    if (media.value) {
        media.value.style.transform = `translate3d(0, ${scrolled * props.depth}px, 0) scale(${1 + progress * 0.06})`;
    }

    // Copy travels a little faster than the footage and fades before it leaves.
    if (content.value) {
        content.value.style.transform = `translate3d(0, ${scrolled * 0.15}px, 0)`;
        content.value.style.opacity = String(Math.max(1 - progress * 1.35, 0));
    }
}

function onScroll() {
    if (frame === null && visible) frame = requestAnimationFrame(apply);
}

/** Autoplay can fire before onMounted runs; keep it honest about the preference. */
function onPlay() {
    if (reduced.value) {
        video.value?.pause();
        paused.value = true;
    } else {
        paused.value = false;
    }
}

function togglePlayback() {
    if (!video.value) return;

    if (video.value.paused) {
        video.value.play().catch(() => {});
        paused.value = false;
    } else {
        video.value.pause();
        paused.value = true;
    }
}

onMounted(() => {
    const query = window.matchMedia('(prefers-reduced-motion: reduce)');
    reduced.value = query.matches;

    const onPreferenceChange = (event) => {
        reduced.value = event.matches;

        if (reduced.value) {
            video.value?.pause();
            paused.value = true;
            if (media.value) media.value.style.transform = '';
            if (content.value) {
                content.value.style.transform = '';
                content.value.style.opacity = '1';
            }
        }
    };

    query.addEventListener('change', onPreferenceChange);

    // Only spend frames on the parallax while the hero is actually on screen.
    observer = new IntersectionObserver(
        ([entry]) => {
            visible = entry.isIntersecting;
            if (visible) onScroll();
        },
        { threshold: 0 },
    );
    observer.observe(root.value);

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
    apply();

    if (reduced.value) {
        // `autoplay` has already started it by now, so stop it outright.
        video.value?.pause();
        paused.value = true;
    } else {
        video.value?.play().catch(() => {});
    }

    onBeforeUnmount(() => query.removeEventListener('change', onPreferenceChange));
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', onScroll);
    window.removeEventListener('resize', onScroll);
    if (frame !== null) cancelAnimationFrame(frame);
    observer?.disconnect();
});
</script>

<template>
    <section
        ref="root"
        :class="['relative isolate flex items-center overflow-hidden', minHeight]"
    >
        <div ref="media" class="absolute inset-x-0 -top-[8%] -z-10 h-[116%] will-change-transform">
            <video
                ref="video"
                class="h-full w-full object-cover"
                :poster="poster"
                autoplay
                muted
                loop
                playsinline
                preload="metadata"
                aria-hidden="true"
                tabindex="-1"
                @loadeddata="ready = true"
                @play="onPlay"
            >
                <source :src="src" type="video/mp4">
            </video>

            <!--
                Layered scrim: a light overall knock-back keeps the pitch legible on
                the right, while the left stays dark enough to carry white copy.
            -->
            <div class="absolute inset-0 bg-black/20" />
            <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/35 to-transparent" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/25 to-transparent" />
            <div
                class="absolute inset-0 opacity-30"
                style="background: radial-gradient(70% 60% at 12% 42%, rgba(205,148,44,0.35), transparent 70%)"
            />
            <div
                class="absolute inset-0"
                style="box-shadow: inset 0 0 16rem 3rem rgba(0,0,0,0.42)"
            />
        </div>

        <div ref="content" class="mx-auto w-full max-w-7xl px-4 py-20 will-change-transform sm:px-6 lg:px-8">
            <slot />
        </div>

        <button
            type="button"
            class="absolute bottom-5 right-4 z-10 inline-flex h-9 w-9 items-center justify-center rounded-full border border-white/25 bg-black/35 text-white/80 backdrop-blur transition hover:border-white/50 hover:text-white sm:right-6"
            :aria-label="paused ? 'Play background video' : 'Pause background video'"
            @click="togglePlayback"
        >
            <svg v-if="paused" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M8 5v14l11-7z" />
            </svg>
            <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M7 5h3.5v14H7zM13.5 5H17v14h-3.5z" />
            </svg>
        </button>

        <div class="pointer-events-none absolute inset-x-0 bottom-6 flex justify-center">
            <span class="flex h-9 w-6 items-start justify-center rounded-full border border-white/25 p-1.5">
                <span class="h-1.5 w-1 animate-bounce rounded-full bg-white/70" />
            </span>
        </div>
    </section>
</template>
