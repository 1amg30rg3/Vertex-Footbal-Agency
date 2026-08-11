<script setup>
import { computed, useId } from 'vue';

const props = defineProps({
    variant: { type: String, default: 'full', validator: (v) => ['full', 'mark'].includes(v) },
    title: { type: String, default: 'Vertex Football Agency' },
});

const uid = useId();
const gold = computed(() => `vertex-gold-${uid}`);
const sub = computed(() => `vertex-gold-sub-${uid}`);
</script>

<template>
    <svg
        v-if="variant === 'mark'"
        viewBox="0 0 100 100"
        xmlns="http://www.w3.org/2000/svg"
        role="img"
        :aria-label="title"
    >
        <defs>
            <linearGradient :id="gold" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#EAC873" />
                <stop offset="45%" stop-color="#C9973F" />
                <stop offset="100%" stop-color="#8A6420" />
            </linearGradient>
        </defs>
        <path :fill="`url(#${gold})`" d="M 50.46 90.0 L 11.16 10.92 L 27.57 11.62 L 52.77 68.27 L 82.14 34.28 Z" />
        <path :fill="`url(#${gold})`" d="M 53.24 57.63 L 75.66 10.69 L 88.84 10.0 L 78.44 29.65 Z" />
    </svg>

    <svg
        v-else
        viewBox="30 39 437 105"
        xmlns="http://www.w3.org/2000/svg"
        role="img"
        :aria-label="title"
    >
        <defs>
            <linearGradient :id="gold" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#EAC873" />
                <stop offset="45%" stop-color="#C9973F" />
                <stop offset="100%" stop-color="#8A6420" />
            </linearGradient>
            <linearGradient :id="sub" x1="0%" y1="0%" x2="100%" y2="0%">
                <stop offset="0%" stop-color="#DDB661" />
                <stop offset="100%" stop-color="#9E7326" />
            </linearGradient>
        </defs>

        <path :fill="`url(#${gold})`" d="M 58.99 104.0 L 30.0 45.68 L 42.11 46.19 L 60.69 87.97 L 82.35 62.9 Z" />
        <path :fill="`url(#${gold})`" d="M 61.03 80.13 L 77.58 45.51 L 87.29 45.0 L 79.62 59.49 Z" />

        <text
            x="108"
            y="104"
            font-family="'Century Gothic', 'Futura', 'Trebuchet MS', sans-serif"
            font-size="88"
            font-weight="500"
            letter-spacing="12"
            text-anchor="start"
            :fill="`url(#${gold})`"
        >ERTEX</text>

        <text
            x="255"
            y="139"
            font-family="'Century Gothic', 'Futura', 'Trebuchet MS', sans-serif"
            font-size="28"
            font-weight="400"
            letter-spacing="11"
            text-anchor="middle"
            :fill="`url(#${sub})`"
        >FOOTBALL AGENCY</text>
    </svg>
</template>
