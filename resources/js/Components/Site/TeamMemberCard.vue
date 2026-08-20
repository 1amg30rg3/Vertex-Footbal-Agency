<script setup>
import Icon from '@/Components/Ui/Icon.vue';
import Prose from './Prose.vue';

defineProps({ member: { type: Object, required: true } });

const socialIcons = {
    instagram: 'instagram',
    facebook: 'facebook',
    linkedin: 'linkedin',
    twitter: 'twitter',
    x: 'twitter',
    youtube: 'youtube',
};
</script>

<template>
    <article class="group relative flex flex-col rounded-2xl border border-border bg-surface p-7 transition-[border-color] duration-300 hover:border-accent/50 sm:p-8">
        <div class="relative h-32 w-32 sm:h-36 sm:w-36">
            <span
                aria-hidden="true"
                class="pointer-events-none absolute -inset-5 rounded-full opacity-0 blur-2xl transition-opacity duration-700 group-hover:opacity-100"
                style="background: radial-gradient(closest-side, var(--accent-soft), transparent)"
            />

            <div class="relative h-full w-full overflow-hidden rounded-full bg-surface-2 ring-1 ring-border transition duration-500 group-hover:ring-accent/60">
                <img
                    v-if="member.photo"
                    :src="member.photo"
                    :alt="member.name"
                    loading="lazy"
                    class="h-full w-full object-cover grayscale transition-transform duration-700 group-hover:scale-[1.07]"
                >
                <div v-else class="flex h-full w-full items-center justify-center text-fg-subtle">
                    <Icon name="users" :size="32" />
                </div>
            </div>
        </div>

        <h3 class="mt-7 text-balance font-heading text-[1.75rem] font-medium leading-[1.15] tracking-[-0.02em] text-fg sm:text-[2rem]">
            {{ member.name }}
        </h3>

        <p v-if="member.role" class="mt-2.5 text-[11px] font-bold uppercase tracking-[0.18em] text-fg-muted">
            {{ member.role }}
        </p>

        <Prose v-if="member.bio" :html="member.bio" class="mt-5 max-w-prose" />

        <div v-if="member.email || member.socials?.length" class="mt-7 flex flex-wrap items-center gap-x-5 gap-y-2">
            <a
                v-if="member.email"
                :href="`mailto:${member.email}`"
                class="inline-flex items-center gap-1.5 text-sm font-semibold text-accent transition-colors hover:text-accent-hover"
            >
                {{ member.email }}
                <Icon name="arrowRight" :size="13" class="transition-transform duration-300 group-hover:translate-x-1" />
            </a>

            <div v-if="member.socials?.length" class="flex items-center gap-1">
                <a
                    v-for="link in member.socials"
                    :key="link.url"
                    :href="link.url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="rounded-lg p-1.5 text-fg-subtle transition-colors hover:bg-surface-2 hover:text-accent"
                    :aria-label="link.platform"
                >
                    <Icon :name="socialIcons[link.platform] ?? 'link'" :size="15" />
                </a>
            </div>
        </div>
    </article>
</template>
