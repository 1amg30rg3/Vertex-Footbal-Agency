<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import { useI18n } from '@/Composables/useI18n';
import { useRoute } from '@/Support/ziggy';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import SectionHeading from '@/Components/Site/SectionHeading.vue';
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
        <section class="relative isolate overflow-hidden border-b border-border">
            <div class="absolute inset-0 -z-10 bg-surface-2">
                <div class="absolute inset-0 bg-gradient-to-t from-bg via-bg/80 to-bg/50" />
                <div
                    class="absolute inset-0 opacity-[0.12]"
                    style="background: radial-gradient(50rem 28rem at 15% 10%, var(--accent), transparent 65%)"
                />
            </div>

            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
                <SectionHeading number="01" :title="t('about.title')" :lead="t('about.lead')" as="h1" />

                <dl class="mt-10 flex flex-wrap gap-x-12 gap-y-6">
                    <div v-for="fact in facts" :key="fact.label" class="flex items-start gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-accent-soft text-accent">
                            <Icon :name="fact.icon" :size="16" />
                        </span>
                        <div>
                            <dt class="text-[11px] font-semibold uppercase tracking-wide text-fg-subtle">
                                {{ fact.label }}
                            </dt>
                            <dd class="mt-0.5 text-sm font-medium text-fg">{{ fact.value }}</dd>
                        </div>
                    </div>
                </dl>
            </div>
        </section>

        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
            <div class="prose-content max-w-3xl text-base leading-relaxed sm:text-lg">
                <p v-for="(paragraph, index) in paragraphs" :key="index">{{ paragraph }}</p>
            </div>

            <figure class="mt-14 rounded-2xl border border-accent/30 bg-accent-soft px-6 py-12 text-center sm:px-12">
                <Icon name="quote" :size="26" class="mx-auto text-accent" />
                <blockquote class="mt-5 text-xl font-medium leading-snug text-balance text-fg sm:text-2xl">
                    {{ t('about.closing') }}
                </blockquote>
                <figcaption class="mt-4 text-sm text-fg-muted">— {{ site.name }}</figcaption>
            </figure>

            <div class="mt-12 flex flex-wrap gap-3">
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
