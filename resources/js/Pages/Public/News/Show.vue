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
        <div class="relative isolate overflow-hidden border-b border-border">
            <div class="absolute inset-0 -z-10 bg-surface-2">
                <div class="absolute inset-0 bg-gradient-to-t from-bg via-bg/85 to-bg/55" />
                <div class="texture-hatch absolute inset-0 opacity-[0.09]" />
                <div
                    class="absolute inset-0"
                    style="background: radial-gradient(46rem 26rem at 12% 0%, var(--accent-soft), transparent 66%)"
                />
            </div>

            <div class="mx-auto max-w-3xl px-4 pb-14 pt-12 sm:px-6 lg:pb-20 lg:pt-16">
                <Button :href="route('public.news.index')" variant="ghost" size="sm" icon="arrowLeft" class="mb-10">
                    {{ t('news.back_to_news') }}
                </Button>

                <div v-reveal>
                    <Badge v-if="article.category" tone="accent">{{ article.category.name }}</Badge>

                    <h1 class="mt-5 font-semibold leading-[1.03] tracking-[-0.03em] text-balance text-fg text-[clamp(2rem,5.2vw,3.75rem)]">
                        {{ article.title }}
                    </h1>
                </div>

                <div v-reveal="90" class="mt-7 flex flex-wrap items-center gap-x-5 gap-y-2 text-[11px] font-semibold uppercase tracking-[0.16em] text-fg-subtle">
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
            </div>
        </div>

        <article class="mx-auto max-w-3xl px-4 py-14 sm:px-6 lg:py-20">
            <figure v-if="article.cover" v-reveal class="overflow-hidden rounded-2xl border border-border">
                <img :src="article.cover" :alt="article.title" class="w-full object-cover">
            </figure>

            <p
                v-if="article.excerpt"
                v-reveal
                :class="[
                    'border-l-2 border-accent pl-6 text-xl font-medium leading-relaxed text-balance text-fg sm:text-2xl',
                    article.cover ? 'mt-12' : '',
                ]"
            >
                {{ article.excerpt }}
            </p>

            <Prose :html="article.body" class="mt-12" size="lg" />
        </article>

        <section v-if="related.length" class="border-t border-border bg-bg-elevated">
            <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
                <SectionHeading v-reveal :title="t('news.related')" size="lg" />
                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="(item, i) in related" :key="item.id" v-reveal="(i % 3) * 90">
                        <NewsCard :article="item" class="h-full" />
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
