<script setup>
import SectionHeading from '@/Components/Site/SectionHeading.vue';

defineProps({
    number: { type: String, default: '01' },
    title: { type: String, required: true },
    lead: { type: String, default: null },
    image: { type: String, default: null },
});
</script>

<template>
    <section class="relative isolate overflow-hidden border-b border-border">
        <div class="absolute inset-0 -z-10 bg-surface-2">
            <img v-if="image" :src="image" alt="" class="h-full w-full object-cover object-top">
            <div class="absolute inset-0 bg-gradient-to-t from-bg via-bg/85 to-bg/55" />
            <div class="texture-hatch absolute inset-0 opacity-[0.09]" />
            <div
                class="absolute inset-0"
                style="background: radial-gradient(46rem 26rem at 12% 0%, var(--accent-soft), transparent 66%)"
            />
        </div>

        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
            <div v-reveal>
                <SectionHeading :number="number" :title="title" :lead="lead" as="h1" size="lg" />
            </div>

            <div v-if="$slots.default" v-reveal="90" class="mt-10">
                <slot />
            </div>
        </div>
    </section>
</template>
