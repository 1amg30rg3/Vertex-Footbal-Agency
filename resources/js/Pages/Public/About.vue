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

const socialIcons = {
    instagram: 'instagram',
    facebook: 'facebook',
    linkedin: 'linkedin',
    twitter: 'twitter',
    x: 'twitter',
    youtube: 'youtube',
};

const socials = computed(() => site.value.socials ?? []);
</script>

<template>
    <Head :title="t('about.title')">
        <meta name="description" :content="t('about.lead')">
    </Head>

    <PublicLayout>
        <PageHero :title="t('about.title')" :lead="t('about.lead')" />

        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
            <div class="grid gap-12 lg:grid-cols-[minmax(0,1fr)_15rem] lg:gap-16">
                <div class="min-w-0">
                    <p
                        v-reveal
                        class="max-w-3xl text-balance text-xl font-medium leading-[1.35] tracking-[-0.015em] text-fg sm:text-2xl lg:text-[1.75rem]"
                    >
                        {{ paragraphs[0] }}
                    </p>

                    <div class="prose-content mt-8 max-w-3xl text-base leading-relaxed sm:text-lg">
                        <p v-for="(paragraph, index) in paragraphs.slice(1)" :key="index" v-reveal="index * 60">
                            {{ paragraph }}
                        </p>
                    </div>

                    <div class="mt-14 flex max-w-3xl items-center gap-4">
                        <span class="h-px flex-1 bg-border" />
                        <Icon name="quote" :size="16" class="text-accent" />
                        <span class="h-px flex-1 bg-border" />
                    </div>
                </div>

                <aside v-reveal="90" class="lg:sticky lg:top-28 lg:self-start">
                    <dl class="space-y-5 border-t border-border pt-6 lg:border-t-0 lg:pt-0">
                        <div v-for="fact in facts" :key="fact.label" class="flex items-start gap-3">
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-accent-soft text-accent">
                                <Icon :name="fact.icon" :size="16" />
                            </span>
                            <div class="min-w-0">
                                <dt class="text-[11px] font-semibold uppercase tracking-[0.18em] text-fg-subtle">
                                    {{ fact.label }}
                                </dt>
                                <dd class="mt-1 text-sm font-medium text-fg">{{ fact.value }}</dd>
                            </div>
                        </div>
                    </dl>

                    <div v-if="socials.length" class="mt-8 border-t border-border pt-6">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-fg-subtle">
                            {{ t('contacts.follow') }}
                        </p>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <a
                                v-for="link in socials"
                                :key="link.url"
                                :href="link.url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex size-10 items-center justify-center rounded-xl border border-border text-fg-muted transition-colors hover:border-accent hover:text-accent"
                                :aria-label="link.platform"
                            >
                                <Icon :name="socialIcons[link.platform] ?? 'link'" :size="16" />
                            </a>
                        </div>
                    </div>
                </aside>
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
