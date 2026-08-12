<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { useI18n } from '@/Composables/useI18n';
import { useRoute } from '@/Support/ziggy';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import SectionHeading from '@/Components/Site/SectionHeading.vue';
import NewsCard from '@/Components/Site/NewsCard.vue';
import PlayerCard from '@/Components/Site/PlayerCard.vue';
import Button from '@/Components/Ui/Button.vue';
import HeroVideo from '@/Components/Site/HeroVideo.vue';
import Marquee from '@/Components/Site/Marquee.vue';
import CountUp from '@/Components/Site/CountUp.vue';
import Icon from '@/Components/Ui/Icon.vue';

const props = defineProps({
    featuredNews: { type: Array, default: () => [] },
    players: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({}) },
    clubs: { type: Array, default: () => [] },
});

const { t, list } = useI18n();
const route = useRoute();
const page = usePage();

const site = computed(() => page.props.site ?? {});
const infoBody = computed(() => list('home.info_body'));

const headline = computed(() => {
    const text = t('home.info_heading').trim();
    const parts = text.match(/^(.+?[.!?])\s+(.+)$/s);

    return parts ? { claim: parts[1], promise: parts[2] } : { claim: text, promise: null };
});

const manifesto = computed(() => {
    const body = infoBody.value;
    const rest = body.length > 1 ? body.slice(1) : body;

    return { lead: rest[0] ?? '', body: rest.slice(1) };
});

const figures = computed(() =>
    [
        { key: 'players', label: t('home.stats_players') },
        { key: 'clubs', label: t('home.stats_clubs') },
        { key: 'countries', label: t('home.stats_countries') },
        { key: 'trainers', label: t('home.stats_trainers') },
    ].filter((figure) => Number(props.stats?.[figure.key]) > 0),
);

const index = computed(() => {
    const present = ['info'];

    if (figures.value.length) present.push('stats');
    if (props.featuredNews.length) present.push('news');
    if (props.players.length) present.push('players');

    return Object.fromEntries(present.map((key, i) => [key, String(i + 1).padStart(2, '0')]));
});

const leadArticle = computed(() => (props.featuredNews.length >= 3 ? props.featuredNews[0] : null));
const restArticles = computed(() =>
    leadArticle.value ? props.featuredNews.slice(1) : props.featuredNews,
);
const restColumns = computed(() =>
    restArticles.value.length >= 3 ? 'sm:grid-cols-2 lg:grid-cols-3' : 'sm:grid-cols-2',
);

const shownPlayers = computed(() => props.players.slice(0, 6));

const wordmark = computed(() => (site.value.name ?? 'VERTEX').split(' ')[0]);
</script>

<template>
    <Head :title="null">
        <meta name="description" :content="t('home.info_heading')">
    </Head>

    <PublicLayout>
        <!--
            The hero sits on footage in both themes, so its copy is pinned to white
            rather than following the theme tokens — the scrim guarantees contrast.
        -->
        <HeroVideo min-height="min-h-[100svh]">
            <div class="max-w-5xl">
                <p v-reveal class="flex items-center gap-4">
                    <span class="h-px w-10 bg-accent sm:w-16" />
                    <span class="text-[11px] font-semibold uppercase tracking-[0.32em] text-accent sm:text-xs">
                        {{ site.name }}
                    </span>
                </p>

                <h1
                    v-reveal="90"
                    class="mt-7 text-balance font-semibold leading-[0.98] tracking-[-0.03em] text-white drop-shadow-[0_2px_14px_rgba(0,0,0,0.5)] text-[clamp(2.1rem,6.2vw,4.75rem)]"
                >
                    {{ headline.claim }}
                    <span v-if="headline.promise" class="block text-white/55">{{ headline.promise }}</span>
                </h1>

                <p v-reveal="180" class="mt-8 max-w-2xl text-base leading-relaxed text-white/75 sm:text-lg">
                    {{ infoBody[0] }}
                </p>

                <div v-reveal="270" class="mt-10 flex flex-wrap items-center gap-3">
                    <Button :href="route('public.players.index')" size="lg" icon-right="arrowRight">
                        {{ t('home.meet_players') }}
                    </Button>

                    <Button
                        :href="route('public.about')"
                        size="lg"
                        variant="outline"
                        class="!border-white/40 !text-white hover:!border-white hover:!bg-white/10"
                    >
                        {{ t('nav.about') }}
                    </Button>
                </div>
            </div>
        </HeroVideo>

        <section v-if="clubs.length" class="border-b border-border bg-bg-elevated">
            <div class="flex items-center gap-6 py-5 lg:gap-10">
                <h2 class="shrink-0 border-r border-border pl-4 pr-6 text-[11px] font-semibold uppercase leading-tight tracking-[0.22em] text-fg-subtle sm:pl-6 lg:pl-8">
                    {{ t('home.clubs_eyebrow') }}
                </h2>

                <Marquee :items="clubs" class="min-w-0 flex-1 pr-4" />
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8 lg:py-36">
            <div class="grid gap-14 lg:grid-cols-[minmax(0,26rem)_1fr] lg:gap-24">
                <div v-reveal class="lg:sticky lg:top-32 lg:self-start">
                    <SectionHeading :number="index.info" :title="t('home.info_eyebrow')" size="lg" />

                    <p class="mt-10 hidden text-[11px] font-semibold uppercase tracking-[0.22em] text-fg-subtle lg:block">
                        {{ site.tagline || t('footer.tagline') }}
                    </p>
                </div>

                <div>
                    <p
                        v-reveal="80"
                        class="text-balance text-xl font-medium leading-[1.35] tracking-[-0.015em] text-fg sm:text-2xl lg:text-[1.75rem]"
                    >
                        {{ manifesto.lead }}
                    </p>

                    <div class="prose-content mt-8 max-w-2xl text-base leading-relaxed sm:text-lg">
                        <p v-for="(paragraph, i) in manifesto.body" :key="i" v-reveal="120 + i * 60">
                            {{ paragraph }}
                        </p>
                    </div>

                    <div v-reveal class="mt-10">
                        <Button :href="route('public.about')" size="lg" variant="outline" icon-right="arrowRight">
                            {{ t('common.read_more') }}
                        </Button>
                    </div>
                </div>
            </div>
        </section>

        <section v-if="figures.length" class="relative isolate overflow-hidden border-y border-border bg-bg-elevated">
            <div class="texture-hatch pointer-events-none absolute inset-0 -z-10 opacity-[0.07]" />
            <div
                class="pointer-events-none absolute inset-0 -z-10"
                style="background: radial-gradient(46rem 26rem at 78% 0%, var(--accent-soft), transparent 68%)"
            />

            <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
                <div v-reveal>
                    <SectionHeading
                        :number="index.stats"
                        :title="t('home.stats_title')"
                        :lead="t('home.stats_lead')"
                        size="lg"
                    />
                </div>

                <dl class="mt-14 grid gap-px overflow-hidden rounded-2xl border border-border bg-border sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="(figure, i) in figures"
                        :key="figure.key"
                        v-reveal="i * 90"
                        class="bg-bg-elevated px-6 py-9 lg:py-11"
                    >
                        <dd class="font-heading font-semibold leading-[0.85] tracking-[-0.05em] text-accent-2 text-[clamp(3.25rem,6.5vw,4.75rem)]">
                            <CountUp :value="Number(stats[figure.key])" :delay="i * 90" />
                        </dd>
                        <dt class="mt-5 flex items-center gap-3 text-[11px] font-semibold uppercase tracking-[0.2em] text-fg-muted">
                            <span v-reveal="i * 90 + 260" class="reveal-rule h-px w-6 shrink-0 bg-accent" />
                            {{ figure.label }}
                        </dt>
                    </div>
                </dl>
            </div>
        </section>

        <section v-if="featuredNews.length" class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8 lg:py-32">
            <div v-reveal class="flex flex-wrap items-end justify-between gap-6">
                <SectionHeading
                    :number="index.news"
                    :title="t('home.featured_news')"
                    :lead="t('home.featured_news_lead')"
                    size="lg"
                />
                <Button :href="route('public.news.index')" variant="outline" icon-right="arrowRight">
                    {{ t('common.view_all') }}
                </Button>
            </div>

            <div v-if="leadArticle" v-reveal="80" class="mt-14">
                <NewsCard :article="leadArticle" featured />
            </div>

            <div
                v-if="restArticles.length"
                :class="['grid gap-6', leadArticle ? 'mt-6' : 'mt-14', restColumns]"
            >
                <div v-for="(article, i) in restArticles" :key="article.id" v-reveal="i * 90">
                    <NewsCard :article="article" class="h-full" />
                </div>
            </div>
        </section>

        <section
            v-if="shownPlayers.length"
            class="border-t border-border bg-bg-elevated"
        >
            <div class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8 lg:py-32">
                <div v-reveal class="flex flex-wrap items-end justify-between gap-6">
                    <SectionHeading
                        :number="index.players"
                        :title="t('home.meet_players')"
                        :lead="t('players.lead')"
                        size="lg"
                    />
                    <Button :href="route('public.players.index')" variant="outline" icon-right="arrowRight">
                        {{ t('common.view_all') }}
                    </Button>
                </div>

                <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 lg:pb-14">
                    <div
                        v-for="(player, i) in shownPlayers"
                        :key="player.id"
                        v-reveal="(i % 3) * 90"
                        :class="i % 3 === 1 ? 'lg:mt-14' : ''"
                    >
                        <PlayerCard :player="player" />
                    </div>
                </div>
            </div>
        </section>

        <section class="relative isolate overflow-hidden border-t border-border bg-bg-elevated">
            <div class="texture-hatch pointer-events-none absolute inset-0 -z-10 opacity-[0.07]" />
            <div
                class="pointer-events-none absolute inset-0 -z-10"
                style="background: radial-gradient(44rem 24rem at 8% 0%, var(--accent-soft), transparent 66%)"
            />

            <div class="relative mx-auto max-w-7xl px-4 pb-36 pt-24 sm:px-6 lg:px-8 lg:pb-56 lg:pt-32">
                <span
                    class="pointer-events-none absolute inset-x-0 bottom-0 translate-y-[18%] select-none text-center font-heading font-bold uppercase leading-none tracking-[-0.05em] text-accent/[0.08] text-[clamp(4.5rem,19vw,16rem)]"
                    aria-hidden="true"
                >{{ wordmark }}</span>

                <div class="relative grid gap-14 lg:grid-cols-[1.35fr_1fr] lg:items-end lg:gap-20">
                    <div v-reveal>
                        <p class="flex items-center gap-4">
                            <span class="h-px w-10 bg-accent sm:w-16" />
                            <span class="text-[11px] font-semibold uppercase tracking-[0.32em] text-accent">
                                {{ site.name }}
                            </span>
                        </p>

                        <h2 class="mt-8 max-w-3xl font-semibold leading-[1.05] tracking-[-0.03em] text-balance text-fg text-[clamp(1.875rem,4.4vw,3.5rem)]">
                            {{ t('about.closing') }}
                        </h2>
                    </div>

                    <div v-reveal="110" class="space-y-3">
                        <Link
                            v-for="(action, i) in [
                                { href: route('public.contacts'), label: t('nav.contacts'), lead: t('contacts.lead') },
                                { href: route('public.team'), label: t('nav.team'), lead: t('team.lead') },
                            ]"
                            :key="i"
                            :href="action.href"
                            class="group flex items-center justify-between gap-6 rounded-2xl border border-border bg-surface px-6 py-5 transition-[border-color,background-color] duration-300 hover:border-accent hover:bg-accent-soft"
                        >
                            <span class="min-w-0">
                                <span class="block truncate text-[11px] font-semibold uppercase tracking-[0.2em] text-fg-subtle">
                                    {{ action.lead }}
                                </span>
                                <span class="mt-1.5 block text-lg font-semibold tracking-tight text-fg transition-colors group-hover:text-accent">
                                    {{ action.label }}
                                </span>
                            </span>
                            <span class="flex size-11 shrink-0 items-center justify-center rounded-full border border-border text-fg-muted transition-colors duration-300 group-hover:border-accent group-hover:bg-accent group-hover:text-accent-fg">
                                <Icon name="arrowUpRight" :size="18" />
                            </span>
                        </Link>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
