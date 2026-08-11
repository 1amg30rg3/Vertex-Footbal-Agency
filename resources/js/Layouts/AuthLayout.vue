<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import ThemeToggle from '@/Components/Ui/ThemeToggle.vue';
import Logo from '@/Components/Site/Logo.vue';

defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, default: null },
});

const page = usePage();
const site = computed(() => page.props.site ?? {});
</script>

<template>
    <div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-bg px-4 py-12">
        <div
            class="pointer-events-none absolute inset-0 opacity-[0.09]"
            style="background: radial-gradient(60rem 40rem at 50% -10%, var(--accent), transparent 65%)"
        />

        <div class="absolute right-4 top-4 sm:right-6 sm:top-6">
            <ThemeToggle />
        </div>

        <div class="relative w-full max-w-sm">
            <div class="mb-8 flex flex-col items-center text-center">
                <img v-if="site.logo" :src="site.logo" :alt="site.name" class="h-12 w-auto">
                <Logo v-else :title="site.name" class="h-12 w-auto" />

                <h1 class="mt-5 text-xl font-semibold tracking-tight text-fg">{{ title }}</h1>
                <p v-if="subtitle" class="mt-1.5 text-sm text-fg-muted">{{ subtitle }}</p>
            </div>

            <div class="rounded-2xl border border-border bg-surface p-6 shadow-xl sm:p-7">
                <slot />
            </div>

            <p class="mt-6 text-center text-xs text-fg-subtle">
                {{ site.name }} — admin panel
            </p>
        </div>
    </div>
</template>
