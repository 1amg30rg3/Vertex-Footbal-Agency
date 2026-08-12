<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from '@/Composables/useI18n';
import { useRoute } from '@/Support/ziggy';
import Badge from '@/Components/Ui/Badge.vue';
import Icon from '@/Components/Ui/Icon.vue';

const props = defineProps({
    player: { type: Object, required: true },
});

const { t } = useI18n();
const route = useRoute();

const href = computed(() => route('public.players.show', { player: props.player.slug }));
const position = computed(() =>
    props.player.specific_position || (props.player.position ? t(`players.positions.${props.player.position}`) : null),
);
const image = computed(() => props.player.photo || props.player.cover);
</script>

<template>
    <Link
        :href="href"
        class="group relative block overflow-hidden rounded-2xl border border-border bg-surface transition-[border-color,translate] duration-300 hover:-translate-y-1 hover:border-accent/60"
    >
        <div class="relative aspect-[3/4] overflow-hidden bg-surface-2">
            <img
                v-if="image"
                :src="image"
                :alt="player.full_name"
                loading="lazy"
                class="h-full w-full object-cover object-top transition-transform duration-500 group-hover:scale-105"
            >
            <div
                v-else
                class="flex h-full w-full items-center justify-center bg-gradient-to-br from-accent/25 via-accent/10 to-accent/30 text-accent"
            >
                <Icon name="users" :size="40" />
            </div>

            <div class="absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-black/85 via-black/35 to-transparent" />

            <Badge v-if="player.is_featured" tone="accent" size="sm" class="absolute left-3 top-3">
                <Icon name="star" :size="11" filled />
                {{ t('news.featured') }}
            </Badge>

            <div class="absolute inset-x-0 bottom-0 p-4">
                <p v-if="position" class="text-[11px] font-semibold uppercase tracking-wider text-accent">
                    {{ position }}
                </p>
                <h3 class="mt-0.5 text-lg font-semibold leading-tight tracking-tight text-white">
                    {{ player.full_name }}
                </h3>
                <p v-if="player.current_club" class="mt-1 flex items-center gap-1.5 text-xs text-white/70">
                    <Icon name="flag" :size="12" />
                    {{ player.current_club }}
                </p>
            </div>
        </div>

        <div class="flex items-center justify-between gap-3 px-4 py-3">
            <dl class="flex items-center gap-4 text-xs text-fg-muted">
                <div v-if="player.age">
                    <dt class="sr-only">{{ t('players.age') }}</dt>
                    <dd class="tabular-nums"><span class="font-semibold text-fg">{{ player.age }}</span> {{ t('players.age').toLowerCase() }}</dd>
                </div>
                <div v-if="player.nationality">
                    <dt class="sr-only">{{ t('players.nationality') }}</dt>
                    <dd>{{ player.nationality }}</dd>
                </div>
            </dl>

            <span class="inline-flex items-center gap-1 text-xs font-medium text-accent transition-transform group-hover:translate-x-0.5">
                {{ t('common.view_profile') }}
                <Icon name="arrowRight" :size="13" />
            </span>
        </div>
    </Link>
</template>
