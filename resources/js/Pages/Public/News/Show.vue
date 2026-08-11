<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { useI18n } from '@/Composables/useI18n';
import { useRoute } from '@/Support/ziggy';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import SectionHeading from '@/Components/Site/SectionHeading.vue';
import NewsCard from '@/Components/Site/NewsCard.vue';
import Prose from '@/Components/Site/Prose.vue';
import Badge from '@/Components/Ui/Badge.vue';
import Button from '@/Components/Ui/Button.vue';
import Icon from '@/Components/Ui/Icon.vue';

const props = defineProps({
    article: { type: Object, required: true },
    related: { type: Array, default: () => [] },
});

const { t, locale } = useI18n();
const route = useRoute();

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
    <Head :title="article.seo?.title">
        <meta v-if="article.seo?.description" name="description" :content="article.seo.description">
        <meta v-if="article.seo?.image" property="og:image" :content="article.seo.image">
        <meta property="og:type" content="article">
    </Head>

    <PublicLayout>
        <article class="mx-auto max-w-3xl px-4 py-14 sm:px-6 lg:py-20">
            <Button :href="route('public.news.index')" variant="ghost" size="sm" icon="arrowLeft" class="mb-8">
                {{ t('news.back_to_news') }}
            </Button>

            <Badge v-if="article.category" tone="accent">{{ article.category.name }}</Badge>

            <h1 class="mt-4 text-3xl font-semibold leading-tight tracking-tight text-balance text-fg sm:text-4xl lg:text-5xl">
                {{ article.title }}
            </h1>

            <div class="mt-5 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-fg-subtle">
                <span v-if="date" class="inline-flex items-center gap-1.5">
                    <Icon name="calendar" :size="14" />
                    {{ date }}
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <Icon name="clock" :size="14" />
                    {{ t('news.min_read', { count: article.reading_minutes }) }}
                </span>
                <span v-if="article.author" class="inline-flex items-center gap-1.5">
                    <Icon name="users" :size="14" />
                    {{ article.author.name }}
                </span>
            </div>

            <figure v-if="article.cover" class="mt-9 overflow-hidden rounded-2xl border border-border">
                <img :src="article.cover" :alt="article.title" class="w-full object-cover">
            </figure>

            <p v-if="article.excerpt" class="mt-9 border-l-2 border-accent pl-5 text-lg leading-relaxed text-fg">
                {{ article.excerpt }}
            </p>

            <Prose :html="article.body" class="mt-9" size="lg" />
        </article>

        <section v-if="related.length" class="border-t border-border bg-bg-elevated">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <SectionHeading :title="t('news.related')" />
                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <NewsCard v-for="item in related" :key="item.id" :article="item" />
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
