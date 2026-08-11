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
    <article class="group flex flex-col overflow-hidden rounded-2xl border border-border bg-surface transition-[border-color] duration-300 hover:border-accent/50">
        <div class="aspect-[4/5] overflow-hidden bg-surface-2">
            <img
                v-if="member.photo"
                :src="member.photo"
                :alt="member.name"
                loading="lazy"
                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.04]"
            >
            <div v-else class="flex h-full w-full items-center justify-center text-fg-subtle">
                <Icon name="users" :size="34" />
            </div>
        </div>

        <div class="flex flex-1 flex-col p-5">
            <p v-if="member.role" class="text-[11px] font-semibold uppercase tracking-wider text-accent">
                {{ member.role }}
            </p>
            <h3 class="mt-1 text-lg font-semibold tracking-tight text-fg">{{ member.name }}</h3>

            <Prose v-if="member.bio" :html="member.bio" size="sm" class="mt-2.5 line-clamp-4" />

            <div class="mt-auto flex flex-wrap items-center gap-3 pt-4">
                <a
                    v-if="member.email"
                    :href="`mailto:${member.email}`"
                    class="inline-flex items-center gap-1.5 text-xs text-fg-muted transition-colors hover:text-accent"
                >
                    <Icon name="mail" :size="13" />
                    {{ member.email }}
                </a>

                <div v-if="member.socials?.length" class="ml-auto flex items-center gap-1">
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
        </div>
    </article>
</template>
