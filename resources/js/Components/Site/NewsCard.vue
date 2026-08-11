<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from '@/Composables/useI18n';
import { useRoute } from '@/Support/ziggy';
import Badge from '@/Components/Ui/Badge.vue';
import Icon from '@/Components/Ui/Icon.vue';

const props = defineProps({
    article: { type: Object, required: true },
    featured: { type: Boolean, default: false },
});

const { t, locale } = useI18n();
const route = useRoute();

const href = computed(() => route('public.news.show', { article: props.article.slug }));

const date = computed(() =>
    props.article.published_at
        ? new Date(props.article.published_at).toLocaleDateString(locale.value, {
              day: 'numeric',
              month: 'long',
              year: 'numeric',
          })
        : null,
);
</script>

<template>
    <Link
        :href="href"
        :class="[
            'group flex flex-col overflow-hidden rounded-2xl border border-border bg-surface transition-[border-color,transform] duration-300 hover:-translate-y-1 hover:border-accent/60',
            featured ? 'sm:flex-row' : '',
        ]"
    >
        <div :class="['relative overflow-hidden bg-surface-2', featured ? 'sm:w-1/2 aspect-[16/10]' : 'aspect-[16/10]']">
            <img
                v-if="article.cover"
                :src="article.cover"
                :alt="article.title"
                loading="lazy"
                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
            >
            <div
                v-else
                class="flex h-full w-full items-center justify-center bg-gradient-to-br from-accent/30 via-accent/12 to-accent/35 text-accent"
            >
                <Icon name="newspaper" :size="32" />
            </div>

            <Badge v-if="article.category" tone="accent" size="sm" class="absolute left-3 top-3">
                {{ article.category.name }}
            </Badge>
        </div>

        <div :class="['flex flex-1 flex-col p-5', featured ? 'sm:justify-center sm:p-7' : '']">
            <p class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-fg-subtle">
                <span v-if="date" class="inline-flex items-center gap-1">
                    <Icon name="calendar" :size="12" />
                    {{ date }}
                </span>
                <span class="inline-flex items-center gap-1">
                    <Icon name="clock" :size="12" />
                    {{ t('news.min_read', { count: article.reading_minutes }) }}
                </span>
            </p>

            <h3
                :class="[
                    'mt-2 font-semibold leading-snug tracking-tight text-balance text-fg transition-colors group-hover:text-accent',
                    featured ? 'text-xl sm:text-2xl' : 'text-lg',
                ]"
            >{{ article.title }}</h3>

            <p v-if="article.excerpt" class="mt-2 line-clamp-3 text-sm leading-relaxed text-fg-muted">
                {{ article.excerpt }}
            </p>

            <span class="mt-4 inline-flex items-center gap-1 text-xs font-medium text-accent transition-transform group-hover:translate-x-0.5">
                {{ t('common.read_more') }}
                <Icon name="arrowRight" :size="13" />
            </span>
        </div>
    </Link>
</template>
