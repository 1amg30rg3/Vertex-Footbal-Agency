<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
    value: { type: Number, required: true },
    duration: { type: Number, default: 1400 },
    delay: { type: Number, default: 0 },
});

const el = ref(null);
const shown = ref(0);

let frame = null;
let observer = null;
let timer = null;

const ease = (t) => 1 - (1 - t) ** 4;

function run() {
    const start = performance.now();

    const step = (now) => {
        const progress = Math.min((now - start) / props.duration, 1);
        shown.value = Math.round(ease(progress) * props.value);

        frame = progress < 1 ? requestAnimationFrame(step) : null;
    };

    frame = requestAnimationFrame(step);
}

function stop() {
    if (frame !== null) cancelAnimationFrame(frame);
    if (timer !== null) clearTimeout(timer);
    frame = null;
    timer = null;
}

onMounted(() => {
    if (window.matchMedia?.('(prefers-reduced-motion: reduce)').matches) {
        shown.value = props.value;

        return;
    }

    observer = new IntersectionObserver(
        ([entry]) => {
            if (!entry.isIntersecting) return;

            observer.disconnect();

            if (props.delay) {
                timer = setTimeout(run, props.delay);
            } else {
                run();
            }
        },
        { rootMargin: '0px 0px -12% 0px', threshold: 0 },
    );

    observer.observe(el.value);
});

watch(
    () => props.value,
    (next) => {
        if (frame === null && timer === null) shown.value = next;
    },
);

onBeforeUnmount(() => {
    stop();
    observer?.disconnect();
});
</script>

<template>
    <span ref="el" class="tabular-nums">{{ shown }}</span>
</template>
