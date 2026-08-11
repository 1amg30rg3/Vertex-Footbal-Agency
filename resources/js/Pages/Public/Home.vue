<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import { useI18n } from '@/Composables/useI18n';
import { useRoute } from '@/Support/ziggy';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import SectionHeading from '@/Components/Site/SectionHeading.vue';
import NewsCard from '@/Components/Site/NewsCard.vue';
import PlayerCard from '@/Components/Site/PlayerCard.vue';
import Button from '@/Components/Ui/Button.vue';
import HeroVideo from '@/Components/Site/HeroVideo.vue';

defineProps({
    featuredNews: { type: Array, default: () => [] },
    players: { type: Array, default: () => [] },
});

const { t, list } = useI18n();
const route = useRoute();
const page = usePage();

const site = computed(() => page.props.site ?? {});
const infoBody = computed(() => list('home.info_body'));
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
            <div class="max-w-4xl">
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-accent sm:text-sm">
                    {{ site.name }}
                </p>

                <h1 class="mt-5 text-3xl font-semibold leading-[1.1] tracking-tight text-balance text-white drop-shadow-[0_2px_12px_rgba(0,0,0,0.45)] sm:text-5xl lg:text-6xl">
                    {{ t('home.info_heading') }}
                </h1>

                <p class="mt-7 max-w-2xl text-base leading-relaxed text-white/80 sm:text-lg">
                    {{ infoBody[0] }}
                </p>

                <div class="mt-9 flex flex-wrap items-center gap-3">
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

        <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
            <div class="grid gap-12 lg:grid-cols-[minmax(0,22rem)_1fr] lg:gap-20">
                <SectionHeading number="01" :title="t('home.info_eyebrow')" />

                <div class="prose-content max-w-2xl text-base leading-relaxed sm:text-lg">
                    <p v-for="(paragraph, index) in infoBody" :key="index">{{ paragraph }}</p>

                    <p class="not-prose mt-8">
                        <Button :href="route('public.about')" variant="link" icon-right="arrowRight">
                            {{ t('common.read_more') }}
                        </Button>
                    </p>
                </div>
            </div>
        </section>

        <section
            v-if="featuredNews.length"
            class="relative border-y border-accent/25 bg-gradient-to-br from-accent/15 via-accent/5 to-accent/20"
        >
            <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-24">
                <div class="flex flex-wrap items-end justify-between gap-6">
                    <SectionHeading
                        number="02"
                        :title="t('home.featured_news')"
                        :lead="t('home.featured_news_lead')"
                    />
                    <Button :href="route('public.news.index')" variant="outline" icon-right="arrowRight">
                        {{ t('common.view_all') }}
                    </Button>
                </div>

                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <NewsCard v-for="article in featuredNews" :key="article.id" :article="article" />
                </div>
            </div>
        </section>

        <section v-if="players.length" class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-24">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <SectionHeading number="03" :title="t('home.meet_players')" :lead="t('players.lead')" />
                <Button :href="route('public.players.index')" variant="outline" icon-right="arrowRight">
                    {{ t('common.view_all') }}
                </Button>
            </div>

            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <PlayerCard v-for="player in players.slice(0, 6)" :key="player.id" :player="player" />
            </div>
        </section>

        <!-- Primary band: the one full-strength colour moment on the page. -->
        <section class="relative mt-20 overflow-hidden bg-gradient-to-br from-accent-hover via-accent to-[#96690f]">
            <div
                class="pointer-events-none absolute inset-0 opacity-35"
                style="background: radial-gradient(50rem 24rem at 50% -10%, rgba(255,255,255,0.85), transparent 70%)"
            />
            <div
                class="pointer-events-none absolute inset-0 opacity-20"
                style="background: radial-gradient(30rem 18rem at 85% 110%, #000, transparent 70%)"
            />

            <div class="relative mx-auto max-w-7xl px-4 py-16 text-center sm:px-6 lg:px-8 lg:py-20">
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-accent-fg/70">
                    {{ site.name }}
                </p>

                <h2 class="mx-auto mt-4 max-w-3xl text-2xl font-semibold leading-snug tracking-tight text-balance text-accent-fg sm:text-4xl">
                    {{ t('about.closing') }}
                </h2>

                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <Button
                        :href="route('public.contacts')"
                        size="lg"
                        icon-right="arrowUpRight"
                        class="!bg-accent-fg !text-accent hover:!bg-accent-fg/90"
                    >
                        {{ t('nav.contacts') }}
                    </Button>
                    <Button
                        :href="route('public.team')"
                        size="lg"
                        variant="outline"
                        class="!border-accent-fg/40 !text-accent-fg hover:!border-accent-fg hover:!bg-accent-fg/10"
                    >
                        {{ t('nav.team') }}
                    </Button>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
