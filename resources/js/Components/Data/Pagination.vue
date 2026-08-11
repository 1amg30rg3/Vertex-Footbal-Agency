<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from '@/Composables/useI18n';
import Icon from '@/Components/Ui/Icon.vue';

const props = defineProps({
    meta: { type: Object, required: true },
    showSummary: { type: Boolean, default: true },
});

const { t } = useI18n();

const numbered = computed(() => (props.meta.links ?? []).slice(1, -1));

const summary = computed(() =>
    t('common.showing', {
        from: props.meta.from ?? 0,
        to: props.meta.to ?? 0,
        total: props.meta.total ?? 0,
    }),
);
</script>

<template>
    <nav v-if="meta.last_page > 1" class="flex flex-wrap items-center justify-between gap-4">
        <p v-if="showSummary" class="text-xs text-fg-subtle">{{ summary }}</p>

        <div class="ml-auto flex items-center gap-1">
            <component
                :is="meta.prev_url ? Link : 'span'"
                :href="meta.prev_url || undefined"
                preserve-scroll
                :class="[
                    'inline-flex h-9 w-9 items-center justify-center rounded-lg border border-border transition-colors',
                    meta.prev_url ? 'text-fg-muted hover:border-accent hover:text-accent' : 'pointer-events-none opacity-40',
                ]"
                :aria-label="t('common.previous')"
            >
                <Icon name="chevronLeft" :size="16" />
            </component>

            <component
                :is="link.url ? Link : 'span'"
                v-for="(link, index) in numbered"
                :key="index"
                :href="link.url || undefined"
                preserve-scroll
                :class="[
                    'inline-flex h-9 min-w-9 items-center justify-center rounded-lg border px-2 text-sm tabular-nums transition-colors',
                    link.active
                        ? 'border-accent bg-accent text-accent-fg font-semibold'
                        : link.url
                          ? 'border-border text-fg-muted hover:border-accent hover:text-accent'
                          : 'border-transparent text-fg-subtle pointer-events-none',
                ]"
                v-html="link.label"
            />

            <component
                :is="meta.next_url ? Link : 'span'"
                :href="meta.next_url || undefined"
                preserve-scroll
                :class="[
                    'inline-flex h-9 w-9 items-center justify-center rounded-lg border border-border transition-colors',
                    meta.next_url ? 'text-fg-muted hover:border-accent hover:text-accent' : 'pointer-events-none opacity-40',
                ]"
                :aria-label="t('common.next')"
            >
                <Icon name="chevronRight" :size="16" />
            </component>
        </div>
    </nav>
</template>
