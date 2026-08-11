<script setup>
import { computed } from 'vue';
import { useI18n } from '@/Composables/useI18n';
import Badge from '@/Components/Ui/Badge.vue';
import Icon from '@/Components/Ui/Icon.vue';

const props = defineProps({
    entries: { type: Array, default: () => [] },
    compact: { type: Boolean, default: false },
});

const { t, locale } = useI18n();

function year(value) {
    return value ? new Date(value).getFullYear() : null;
}

function monthYear(value) {
    if (!value) return null;

    return new Date(value).toLocaleDateString(locale.value, { month: 'short', year: 'numeric' });
}

const rows = computed(() =>
    props.entries.map((entry) => ({
        ...entry,
        range: [
            props.compact ? year(entry.started_on) : monthYear(entry.started_on),
            entry.is_current || !entry.ended_on
                ? t('common.present')
                : props.compact
                  ? year(entry.ended_on)
                  : monthYear(entry.ended_on),
        ]
            .filter(Boolean)
            .join(' — '),
    })),
);
</script>

<template>
    <ol class="relative space-y-6 border-l border-border pl-6">
        <li v-for="entry in rows" :key="entry.id" class="relative">
            <span
                :class="[
                    'absolute -left-[1.6875rem] top-1 flex h-3.5 w-3.5 items-center justify-center rounded-full border-2',
                    entry.is_current ? 'border-accent bg-accent' : 'border-border-strong bg-bg',
                ]"
            />

            <div class="flex flex-wrap items-start gap-x-3 gap-y-1.5">
                <img
                    v-if="entry.club_logo"
                    :src="entry.club_logo"
                    :alt="entry.club_name"
                    class="h-8 w-8 shrink-0 rounded-md bg-surface-2 object-contain p-0.5"
                    loading="lazy"
                >

                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h4 class="text-base font-semibold tracking-tight text-fg">{{ entry.club_name }}</h4>
                        <Badge v-if="entry.is_current" tone="accent" size="sm" dot>{{ t('common.present') }}</Badge>
                    </div>

                    <p v-if="entry.category" class="mt-0.5 text-sm text-accent">{{ entry.category }}</p>

                    <p class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-fg-subtle">
                        <span v-if="entry.range" class="inline-flex items-center gap-1">
                            <Icon name="calendar" :size="12" />
                            {{ entry.range }}
                        </span>
                        <span v-if="entry.league" class="inline-flex items-center gap-1">
                            <Icon name="flag" :size="12" />
                            {{ entry.league }}
                        </span>
                    </p>

                    <p v-if="entry.notes" class="mt-2 text-sm leading-relaxed text-fg-muted">{{ entry.notes }}</p>
                </div>
            </div>
        </li>
    </ol>
</template>
