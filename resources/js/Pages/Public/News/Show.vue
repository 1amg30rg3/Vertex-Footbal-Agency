<script setup>
import { computed, onBeforeUnmount, ref } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
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
const page = usePage();

const date = computed(() =>
    props.article.published_at
        ? new Date(props.article.published_at).toLocaleDateString(locale.value, {
              day: 'numeric',
              month: 'long',
              year: 'numeric',
          })
        : null,
);

const meta = computed(() =>
    [
        date.value && { icon: 'calendar', value: date.value },
        { icon: 'clock', value: t('news.min_read', { count: props.article.reading_minutes }) },
        props.article.author && { icon: 'users', value: props.article.author.name },
    ].filter(Boolean),
);

const shareUrl = computed(() => new URL(page.url, window.location.origin).href);

const shareLinks = computed(() => {
    const url = encodeURIComponent(shareUrl.value);
    const text = encodeURIComponent(props.article.title);

    return [
        { icon: 'twitter', label: 'X', href: `https://twitter.com/intent/tweet?url=${url}&text=${text}` },
        { icon: 'facebook', label: 'Facebook', href: `https://www.facebook.com/sharer/sharer.php?u=${url}` },
        { icon: 'linkedin', label: 'LinkedIn', href: `https://www.linkedin.com/sharing/share-offsite/?url=${url}` },
    ];
});

const copied = ref(false);
let copyTimer = null;

function copyLink() {
    navigator.clipboard?.writeText(shareUrl.value).then(() => {
        copied.value = true;
        clearTimeout(copyTimer);
        copyTimer = setTimeout(() => (copied.value = false), 2000);
    }).catch(() => {});
}

onBeforeUnmount(() => clearTimeout(copyTimer));
</script>

<template>
    <Head :title="article.seo?.title">
        <meta property="og:type" content="article">
    </Head>

    <PublicLayout>
        <section class="relative isolate flex items-end overflow-hidden border-b border-border lg:min-h-[32rem]">
            <div class="absolute inset-0 -z-10">
                <img
                    v-if="article.cover"
                    :src="article.cover"
                    :alt="article.title"
                    fetchpriority="high"
                    class="h-full w-full object-cover"
                >
                <div v-else class="h-full w-full bg-surface-2" />
                <div class="absolute inset-0 bg-gradient-to-t from-bg via-bg/88 to-bg/55" />
                <div class="texture-hatch absolute inset-0 opacity-[0.09]" />
                <div
                    class="absolute inset-0"
                    style="background: radial-gradient(46rem 26rem at 12% 0%, var(--accent-soft), transparent 66%)"
                />
            </div>

            <div class="mx-auto w-full max-w-7xl px-4 pb-14 pt-12 sm:px-6 lg:px-8 lg:pb-20 lg:pt-24">
                <Button :href="route('public.news.index')" variant="ghost" size="sm" icon="arrowLeft" class="mb-10">
                    {{ t('news.back_to_news') }}
                </Button>

                <div v-reveal class="max-w-4xl">
                    <Badge v-if="article.category" tone="accent">{{ article.category.name }}</Badge>

                    <h1 class="mt-5 font-semibold leading-[1.03] tracking-[-0.03em] text-balance text-fg text-[clamp(2rem,5.2vw,3.75rem)]">
                        {{ article.title }}
                    </h1>
                </div>
            </div>
        </section>

        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-20">
            <div class="grid gap-12 lg:grid-cols-[minmax(0,1fr)_15rem] lg:gap-16">
                <article class="min-w-0">
                    <p
                        v-if="article.excerpt"
                        v-reveal
                        class="max-w-3xl border-l-2 border-accent pl-6 text-xl font-medium leading-relaxed text-balance text-fg sm:text-2xl"
                    >
                        {{ article.excerpt }}
                    </p>

                    <Prose
                        :html="article.body"
                        :class="['max-w-3xl text-justify hyphens-auto', article.excerpt ? 'mt-12' : '']"
                        size="lg"
                    />

                    <div class="mt-14 flex max-w-3xl items-center gap-4">
                        <span class="h-px flex-1 bg-border" />
                        <Icon name="quote" :size="16" class="text-accent" />
                        <span class="h-px flex-1 bg-border" />
                    </div>
                </article>

                <aside v-reveal="90" class="lg:sticky lg:top-28 lg:self-start">
                    <ul class="space-y-4 border-t border-border pt-6 lg:border-t-0 lg:pt-0">
                        <li v-for="row in meta" :key="row.value" class="flex items-center gap-2.5">
                            <Icon :name="row.icon" :size="15" class="text-accent" />
                            <span class="text-sm text-fg-muted">{{ row.value }}</span>
                        </li>
                    </ul>

                    <div class="mt-8 border-t border-border pt-6">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-fg-subtle">
                            {{ t('news.share') }}
                        </p>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <a
                                v-for="link in shareLinks"
                                :key="link.label"
                                :href="link.href"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex size-10 items-center justify-center rounded-xl border border-border text-fg-muted transition-colors hover:border-accent hover:text-accent"
                                :aria-label="link.label"
                            >
                                <Icon :name="link.icon" :size="16" />
                            </a>

                            <button
                                type="button"
                                :class="[
                                    'inline-flex size-10 items-center justify-center rounded-xl border transition-colors',
                                    copied
                                        ? 'border-accent bg-accent-soft text-accent'
                                        : 'border-border text-fg-muted hover:border-accent hover:text-accent',
                                ]"
                                :aria-label="t('news.share')"
                                @click="copyLink"
                            >
                                <Icon :name="copied ? 'check' : 'link'" :size="16" />
                            </button>
                        </div>
                    </div>
                </aside>
            </div>
        </div>

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
