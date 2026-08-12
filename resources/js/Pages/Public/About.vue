<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import { useI18n } from '@/Composables/useI18n';
import { useRoute } from '@/Support/ziggy';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import PageHero from '@/Components/Site/PageHero.vue';
import Button from '@/Components/Ui/Button.vue';
import Icon from '@/Components/Ui/Icon.vue';

const { t, list } = useI18n();
const route = useRoute();
const page = usePage();

const site = computed(() => page.props.site ?? {});
const paragraphs = computed(() => list('about.body'));

const facts = computed(() => [
    { icon: 'calendar', label: t('about.founded_label'), value: t('about.founded_value') },
    { icon: 'users', label: t('about.founder_label'), value: t('about.founder_value') },
]);
</script>

<template>
    <Head :title="t('about.title')">
        <meta name="description" :content="t('about.lead')">
    </Head>

    <PublicLayout>
        <PageHero :title="t('about.title')" :lead="t('about.lead')">
            <dl class="flex flex-wrap gap-x-12 gap-y-6">
                <div v-for="fact in facts" :key="fact.label" class="flex items-start gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-accent-soft text-accent">
                        <Icon :name="fact.icon" :size="16" />
                    </span>
                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-[0.18em] text-fg-subtle">
                            {{ fact.label }}
                        </dt>
                        <dd class="mt-1 text-sm font-medium text-fg">{{ fact.value }}</dd>
                    </div>
                </div>
            </dl>
        </PageHero>

        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
            <p
                v-reveal
                class="max-w-4xl text-balance text-xl font-medium leading-[1.35] tracking-[-0.015em] text-fg sm:text-2xl lg:text-[1.75rem]"
            >
                {{ paragraphs[0] }}
            </p>

            <div class="prose-content mt-8 max-w-3xl text-base leading-relaxed sm:text-lg">
                <p v-for="(paragraph, index) in paragraphs.slice(1)" :key="index" v-reveal="index * 60">
                    {{ paragraph }}
                </p>
            </div>

            <figure
                v-reveal
                class="mt-16 rounded-2xl border border-accent/30 bg-accent-soft px-6 py-14 text-center sm:px-12"
            >
                <Icon name="quote" :size="26" class="mx-auto text-accent" />
                <blockquote class="mx-auto mt-6 max-w-3xl font-semibold leading-[1.15] tracking-[-0.02em] text-balance text-fg text-[clamp(1.5rem,3.4vw,2.5rem)]">
                    {{ t('about.closing') }}
                </blockquote>
                <figcaption class="mt-6 text-[11px] font-semibold uppercase tracking-[0.28em] text-fg-muted">
                    {{ site.name }}
                </figcaption>
            </figure>

            <div v-reveal class="mt-14 flex flex-wrap gap-3">
                <Button :href="route('public.team')" size="lg" icon-right="arrowRight">
                    {{ t('nav.team') }}
                </Button>
                <Button :href="route('public.contacts')" size="lg" variant="outline">
                    {{ t('nav.contacts') }}
                </Button>
            </div>
        </div>
    </PublicLayout>
</template>
