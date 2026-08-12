<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { useI18n } from '@/Composables/useI18n';
import { useRoute } from '@/Support/ziggy';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import SectionHeading from '@/Components/Site/SectionHeading.vue';
import PlayerCard from '@/Components/Site/PlayerCard.vue';
import Prose from '@/Components/Site/Prose.vue';
import CountUp from '@/Components/Site/CountUp.vue';
import StatBar from '@/Components/Viz/StatBar.vue';
import StatDonut from '@/Components/Viz/StatDonut.vue';
import StatTile from '@/Components/Viz/StatTile.vue';
import MonthlyBarChart from '@/Components/Viz/MonthlyBarChart.vue';
import PitchPositionMarker from '@/Components/Viz/PitchPositionMarker.vue';
import CareerTimeline from '@/Components/Viz/CareerTimeline.vue';
import PhotoGallery from '@/Components/Viz/PhotoGallery.vue';
import Badge from '@/Components/Ui/Badge.vue';
import Button from '@/Components/Ui/Button.vue';
import Icon from '@/Components/Ui/Icon.vue';
import Tabs from '@/Components/Ui/Tabs.vue';

const props = defineProps({
    player: { type: Object, required: true },
    related: { type: Array, default: () => [] },
});

const { t, locale } = useI18n();
const route = useRoute();

const activeSeasonId = ref(
    props.player.seasons?.find((season) => season.is_current)?.id ?? props.player.seasons?.[0]?.id ?? null,
);

const season = computed(
    () => props.player.seasons?.find((item) => item.id === activeSeasonId.value) ?? null,
);

const seasonTabs = computed(() =>
    (props.player.seasons ?? []).map((item) => ({ value: item.id, label: item.label })),
);

const positionLabel = computed(
    () => props.player.specific_position || (props.player.position ? t(`players.positions.${props.player.position}`) : null),
);

const sections = computed(() =>
    [
        { id: 'personal', number: '01', label: t('players.sections.personal'), show: true },
        {
            id: 'profile',
            number: '02',
            label: t('players.sections.profile'),
            show: !!(props.player.playing_style || props.player.skills?.length || props.player.pitch),
        },
        {
            id: 'career',
            number: '03',
            label: t('players.sections.career'),
            show: !!(props.player.career?.length || props.player.achievements?.length),
        },
        { id: 'statistics', number: '04', label: t('players.sections.statistics'), show: !!props.player.seasons?.length },
        { id: 'photos', number: '05', label: t('players.sections.photos'), show: !!props.player.photos?.length },
        {
            id: 'goals',
            number: '06',
            label: t('players.sections.goals'),
            show: Object.keys(props.player.goals ?? {}).length > 0 || !!props.player.quote,
        },
    ].filter((section) => section.show),
);

const activeSection = ref(sections.value[0]?.id ?? null);
let observer = null;

onMounted(() => {
    observer = new IntersectionObserver(
        (entries) => {
            const visible = entries.filter((entry) => entry.isIntersecting);

            if (visible.length) activeSection.value = visible[0].target.id;
        },
        { rootMargin: '-30% 0px -60% 0px', threshold: 0 },
    );

    sections.value.forEach((section) => {
        const element = document.getElementById(section.id);

        if (element) observer.observe(element);
    });
});

onBeforeUnmount(() => observer?.disconnect());

function scrollTo(id) {
    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

const personalRows = computed(() =>
    [
        { icon: 'calendar', label: t('players.date_of_birth'), value: formatDate(props.player.date_of_birth) },
        { icon: 'clock', label: t('players.age'), value: props.player.age ? `${props.player.age}` : null },
        { icon: 'globe', label: t('players.nationality'), value: props.player.nationality },
        { icon: 'ruler', label: t('players.height'), value: props.player.height_cm ? `${props.player.height_cm} cm` : null },
        { icon: 'scale', label: t('players.weight'), value: props.player.weight_kg ? `${props.player.weight_kg} kg` : null },
        { icon: 'target', label: t('players.position'), value: positionLabel.value },
        {
            icon: 'flag',
            label: t('players.foot'),
            value: props.player.preferred_foot ? t(`players.feet.${props.player.preferred_foot}`) : null,
        },
        { icon: 'trophy', label: t('players.current_club'), value: props.player.current_club },
        { icon: 'calendar', label: t('players.contract_until'), value: formatDate(props.player.contract_until) },
    ].filter((row) => !!row.value),
);

function formatDate(value) {
    if (!value) return null;

    return new Date(value).toLocaleDateString(locale.value, { day: 'numeric', month: 'long', year: 'numeric' });
}

const contactLinks = computed(() => {
    const contact = props.player.contact ?? {};

    return [
        contact.phone && { icon: 'phone', label: contact.phone, href: `tel:${contact.phone}` },
        contact.email && { icon: 'mail', label: contact.email, href: `mailto:${contact.email}` },
        contact.instagram && {
            icon: 'instagram',
            label: contact.instagram.replace(/^https?:\/\/(www\.)?instagram\.com\//, '@'),
            href: contact.instagram.startsWith('http')
                ? contact.instagram
                : `https://instagram.com/${contact.instagram.replace(/^@/, '')}`,
        },
        (contact.city || contact.country) && {
            icon: 'location',
            label: [contact.city, contact.country].filter(Boolean).join(', '),
            href: null,
        },
    ].filter(Boolean);
});

const goalBlocks = computed(() =>
    [
        { key: 'short_term', label: t('players.short_term'), html: props.player.goals?.short_term },
        { key: 'mid_term', label: t('players.mid_term'), html: props.player.goals?.mid_term },
        { key: 'long_term', label: t('players.long_term'), html: props.player.goals?.long_term },
    ].filter((block) => !!block.html),
);
</script>

<template>
    <Head :title="player.seo?.title">
        <meta v-if="player.seo?.description" name="description" :content="player.seo.description">
        <meta v-if="player.seo?.image" property="og:image" :content="player.seo.image">
    </Head>

    <PublicLayout>
        <section class="relative isolate overflow-hidden border-b border-border">
            <div class="absolute inset-0 -z-10">
                <img v-if="player.cover" :src="player.cover" alt="" class="h-full w-full object-cover object-top">
                <div v-else class="h-full w-full bg-surface-2" />
                <div class="absolute inset-0 bg-gradient-to-t from-bg via-bg/85 to-bg/50" />
                <div class="texture-hatch absolute inset-0 opacity-[0.09]" />
            </div>

            <div class="mx-auto max-w-7xl px-4 pb-14 pt-14 sm:px-6 lg:px-8 lg:pb-20 lg:pt-20">
                <Button :href="route('public.players.index')" variant="ghost" size="sm" icon="arrowLeft" class="mb-10">
                    {{ t('players.title') }}
                </Button>

                <div class="flex flex-col gap-8 sm:flex-row sm:items-end">
                    <div
                        v-if="player.photo"
                        v-reveal
                        class="h-32 w-32 shrink-0 overflow-hidden rounded-2xl border-2 border-accent/40 bg-surface-2 sm:h-44 sm:w-44"
                    >
                        <img :src="player.photo" :alt="player.full_name" class="h-full w-full object-cover">
                    </div>

                    <div v-reveal="80" class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <Badge v-if="positionLabel" tone="accent">{{ positionLabel }}</Badge>
                            <Badge v-if="player.nationality" tone="neutral" icon="globe">{{ player.nationality }}</Badge>
                        </div>

                        <h1 class="mt-4 font-semibold leading-[0.88] tracking-[-0.04em] text-fg text-[clamp(2.5rem,7vw,5rem)]">
                            <span class="block text-fg-muted">{{ player.first_name }}</span>
                            <span class="block">{{ player.last_name }}</span>
                        </h1>

                        <div v-if="player.current_club" class="mt-5 flex items-center gap-2.5">
                            <img
                                v-if="player.current_club_logo"
                                :src="player.current_club_logo"
                                alt=""
                                class="h-7 w-7 rounded object-contain"
                            >
                            <span class="text-[11px] font-semibold uppercase tracking-[0.2em] text-fg-muted">
                                {{ player.current_club }}
                            </span>
                        </div>
                    </div>

                    <dl
                        v-if="player.totals"
                        v-reveal="160"
                        class="grid shrink-0 grid-cols-4 gap-x-6 gap-y-5 sm:grid-cols-2 lg:grid-cols-4"
                    >
                        <div v-for="(key, i) in ['matches', 'goals', 'assists', 'minutes']" :key="key">
                            <dd class="font-heading font-semibold leading-[0.85] tracking-[-0.04em] text-accent-2 text-[clamp(1.75rem,3.4vw,2.75rem)]">
                                <CountUp :value="Number(player.totals[key])" :delay="160 + i * 80" />
                            </dd>
                            <dt class="mt-2 text-[10px] font-semibold uppercase tracking-[0.18em] text-fg-subtle">
                                {{ t(key === 'goals' ? 'players.goals_scored' : `players.${key}`) }}
                            </dt>
                        </div>
                    </dl>
                </div>
            </div>
        </section>

        <div class="sticky top-16 z-20 border-b border-border bg-bg/90 backdrop-blur-lg lg:top-[4.5rem]">
            <div class="mx-auto max-w-7xl overflow-x-auto no-scrollbar px-4 sm:px-6 lg:px-8">
                <nav class="flex items-center gap-1 py-2">
                    <button
                        v-for="section in sections"
                        :key="section.id"
                        type="button"
                        :class="[
                            'inline-flex shrink-0 items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                            activeSection === section.id
                                ? 'bg-accent-soft text-accent'
                                : 'text-fg-muted hover:bg-surface-2 hover:text-fg',
                        ]"
                        @click="scrollTo(section.id)"
                    >
                        <span class="text-xs tabular-nums opacity-70">{{ section.number }}</span>
                        {{ section.label }}
                    </button>
                </nav>
            </div>
        </div>

        <div class="mx-auto max-w-7xl space-y-20 px-4 py-16 sm:px-6 lg:px-8 lg:space-y-28 lg:py-20">
            <section id="personal" class="scroll-mt-32">
                <SectionHeading v-reveal number="01" :title="t('players.sections.personal')" size="lg" />

                <div class="mt-8 grid gap-6 lg:grid-cols-3">
                    <dl class="grid gap-x-8 gap-y-5 rounded-2xl border border-border bg-surface p-6 sm:grid-cols-2 lg:col-span-2">
                        <div v-for="row in personalRows" :key="row.label" class="flex items-start gap-3">
                            <Icon :name="row.icon" :size="16" class="mt-0.5 text-accent" />
                            <div class="min-w-0">
                                <dt class="text-[11px] font-semibold uppercase tracking-wide text-fg-subtle">
                                    {{ row.label }}
                                </dt>
                                <dd class="mt-0.5 text-sm font-medium text-fg">{{ row.value }}</dd>
                            </div>
                        </div>
                    </dl>

                    <div v-if="contactLinks.length" class="rounded-2xl border border-border bg-surface p-6">
                        <h3 class="text-[11px] font-semibold uppercase tracking-wide text-fg-subtle">
                            {{ t('players.contact') }}
                        </h3>
                        <ul class="mt-4 space-y-3">
                            <li v-for="link in contactLinks" :key="link.label">
                                <component
                                    :is="link.href ? 'a' : 'span'"
                                    :href="link.href || undefined"
                                    :target="link.href?.startsWith('http') ? '_blank' : undefined"
                                    :rel="link.href?.startsWith('http') ? 'noopener noreferrer' : undefined"
                                    class="flex items-center gap-2.5 text-sm text-fg-muted transition-colors hover:text-accent"
                                >
                                    <Icon :name="link.icon" :size="15" class="text-fg-subtle" />
                                    <span class="truncate">{{ link.label }}</span>
                                </component>
                            </li>
                        </ul>
                    </div>
                </div>
            </section>

            <section
                v-if="player.playing_style || player.skills?.length || player.pitch"
                id="profile"
                class="scroll-mt-32"
            >
                <SectionHeading v-reveal number="02" :title="t('players.sections.profile')" size="lg" />

                <div class="mt-8 grid gap-8 lg:grid-cols-[1.4fr_1fr] lg:gap-12">
                    <div class="space-y-10">
                        <div v-if="player.playing_style">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-accent">
                                {{ t('players.playing_style') }}
                            </h3>
                            <Prose :html="player.playing_style" class="mt-4" />
                        </div>

                        <div v-if="player.skills?.length">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-accent">
                                {{ t('players.strengths') }}
                            </h3>
                            <div class="mt-5 grid gap-x-10 gap-y-5 sm:grid-cols-2">
                                <StatBar
                                    v-for="skill in player.skills"
                                    :key="skill.id"
                                    :label="skill.label"
                                    :value="skill.value"
                                />
                            </div>
                        </div>
                    </div>

                    <div v-if="player.pitch">
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-accent">
                            {{ t('players.field_position') }}
                        </h3>
                        <div class="mt-5 mx-auto max-w-[16rem]">
                            <PitchPositionMarker :x="player.pitch.x" :y="player.pitch.y" :label="positionLabel" />
                        </div>
                    </div>
                </div>
            </section>

            <section v-if="player.career?.length || player.achievements?.length" id="career" class="scroll-mt-32">
                <SectionHeading v-reveal number="03" :title="t('players.sections.career')" size="lg" />

                <div class="mt-8 grid gap-10 lg:grid-cols-[1.4fr_1fr] lg:gap-12">
                    <div v-if="player.career?.length">
                        <h3 class="mb-6 text-sm font-semibold uppercase tracking-wide text-accent">
                            {{ t('players.career_timeline') }}
                        </h3>
                        <CareerTimeline :entries="player.career" />
                    </div>

                    <div v-if="player.achievements?.length">
                        <h3 class="mb-6 text-sm font-semibold uppercase tracking-wide text-accent">
                            {{ t('players.achievements') }}
                        </h3>
                        <ul class="space-y-3">
                            <li
                                v-for="item in player.achievements"
                                :key="item.id"
                                class="flex items-start gap-3 rounded-xl border border-border bg-surface p-4"
                            >
                                <Icon name="trophy" :size="16" class="mt-0.5 shrink-0 text-accent" />
                                <div class="min-w-0">
                                    <p class="text-sm leading-relaxed text-fg">{{ item.text }}</p>
                                    <p v-if="item.year" class="mt-1 text-xs tabular-nums text-fg-subtle">{{ item.year }}</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </section>

            <section v-if="player.seasons?.length" id="statistics" class="scroll-mt-32">
                <SectionHeading v-reveal number="04" :title="t('players.sections.statistics')" size="lg" />

                <Tabs
                    v-if="seasonTabs.length > 1"
                    v-model="activeSeasonId"
                    :tabs="seasonTabs"
                    class="mt-6"
                />

                <div v-if="season" class="mt-8 space-y-8">
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <StatTile v-reveal :label="t('players.matches')" :value="season.matches_played" icon="calendar" tone="figure" size="lg" animate />
                        <StatTile v-reveal="80" :label="t('players.goals_scored')" :value="season.goals" icon="target" tone="figure" size="lg" animate />
                        <StatTile v-reveal="160" :label="t('players.assists')" :value="season.assists" icon="users" tone="figure" size="lg" animate />
                        <StatTile v-reveal="240" :label="t('players.minutes')" :value="season.minutes_played" icon="clock" tone="figure" size="lg" animate />
                    </div>

                    <div class="grid gap-6 lg:grid-cols-[1fr_1.6fr]">
                        <div class="rounded-2xl border border-border bg-surface p-6">
                            <StatDonut
                                :title="t('players.playing_time')"
                                :slices="[
                                    { label: t('players.starting_xi'), value: season.playing_time.starting },
                                    { label: t('players.substitute'), value: season.playing_time.substitute },
                                    { label: t('players.not_in_squad'), value: season.playing_time.not_in_squad },
                                ]"
                                :center-value="`${Math.round(season.playing_time.starting)}%`"
                                :center-label="t('players.starting_xi')"
                            />
                        </div>

                        <div class="rounded-2xl border border-border bg-surface p-6">
                            <MonthlyBarChart
                                :months="season.months"
                                :title="t('players.monthly_breakdown')"
                                :goals-label="t('players.goals_scored')"
                                :assists-label="t('players.assists')"
                            />
                        </div>
                    </div>
                </div>
            </section>

            <section v-if="player.photos?.length" id="photos" class="scroll-mt-32">
                <SectionHeading v-reveal number="05" :title="t('players.sections.photos')" size="lg" />
                <PhotoGallery :photos="player.photos" class="mt-8" :columns="3" />
            </section>

            <section v-if="goalBlocks.length || player.quote" id="goals" class="scroll-mt-32">
                <SectionHeading v-reveal number="06" :title="t('players.sections.goals')" size="lg" />

                <div v-if="goalBlocks.length" class="mt-10 grid gap-6 lg:grid-cols-3">
                    <div
                        v-for="(block, index) in goalBlocks"
                        :key="block.key"
                        v-reveal="index * 90"
                        class="rounded-2xl border border-border bg-surface p-6"
                    >
                        <p class="text-xs font-semibold tabular-nums tracking-[0.2em] text-accent">
                            {{ String(index + 1).padStart(2, '0') }}
                        </p>
                        <h3 class="mt-3 text-base font-semibold tracking-tight text-fg">{{ block.label }}</h3>
                        <Prose :html="block.html" size="sm" class="mt-3" />
                    </div>
                </div>

                <figure
                    v-if="player.quote"
                    v-reveal
                    class="mt-12 rounded-2xl border border-accent/30 bg-accent-soft px-6 py-14 text-center sm:px-12"
                >
                    <Icon name="quote" :size="26" class="mx-auto text-accent" />
                    <blockquote class="mx-auto mt-6 max-w-3xl font-semibold leading-[1.15] tracking-[-0.02em] text-balance text-fg text-[clamp(1.375rem,3vw,2.25rem)]">
                        “{{ player.quote }}”
                    </blockquote>
                    <figcaption class="mt-6 text-[11px] font-semibold uppercase tracking-[0.28em] text-fg-muted">
                        {{ player.full_name }}
                    </figcaption>
                </figure>
            </section>

            <section v-if="related.length">
                <SectionHeading v-reveal :title="t('home.meet_players')" size="lg" />
                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="(item, i) in related" :key="item.id" v-reveal="(i % 3) * 90">
                        <PlayerCard :player="item" />
                    </div>
                </div>
            </section>
        </div>
    </PublicLayout>
</template>
