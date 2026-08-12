<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { useI18n } from '@/Composables/useI18n';
import { useRoute } from '@/Support/ziggy';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import SectionHeading from '@/Components/Site/SectionHeading.vue';
import TrainerCard from '@/Components/Site/TrainerCard.vue';
import Prose from '@/Components/Site/Prose.vue';
import CareerTimeline from '@/Components/Viz/CareerTimeline.vue';
import Badge from '@/Components/Ui/Badge.vue';
import Button from '@/Components/Ui/Button.vue';
import Icon from '@/Components/Ui/Icon.vue';

const props = defineProps({
    trainer: { type: Object, required: true },
    related: { type: Array, default: () => [] },
});

const { t } = useI18n();
const route = useRoute();

const contactLinks = computed(() => {
    const contact = props.trainer.contact ?? {};

    return [
        contact.email && { icon: 'mail', label: contact.email, href: `mailto:${contact.email}` },
        contact.phone && { icon: 'phone', label: contact.phone, href: `tel:${contact.phone}` },
        contact.instagram && {
            icon: 'instagram',
            label: 'Instagram',
            href: contact.instagram.startsWith('http')
                ? contact.instagram
                : `https://instagram.com/${contact.instagram.replace(/^@/, '')}`,
        },
        contact.linkedin && { icon: 'linkedin', label: 'LinkedIn', href: contact.linkedin },
    ].filter(Boolean);
});
</script>

<template>
    <Head :title="trainer.seo?.title">
        <meta v-if="trainer.seo?.description" name="description" :content="trainer.seo.description">
    </Head>

    <PublicLayout>
        <section class="relative isolate overflow-hidden border-b border-border">
            <div class="absolute inset-0 -z-10">
                <img v-if="trainer.cover" :src="trainer.cover" alt="" class="h-full w-full object-cover">
                <div v-else class="h-full w-full bg-surface-2" />
                <div class="absolute inset-0 bg-gradient-to-t from-bg via-bg/85 to-bg/60" />
                <div class="texture-hatch absolute inset-0 opacity-[0.09]" />
            </div>

            <div class="mx-auto max-w-7xl px-4 pb-14 pt-14 sm:px-6 lg:px-8 lg:pb-20 lg:pt-20">
                <Button :href="route('public.trainers.index')" variant="ghost" size="sm" icon="arrowLeft" class="mb-10">
                    {{ t('trainers.title') }}
                </Button>

                <div class="flex flex-col gap-6 sm:flex-row sm:items-end">
                    <div
                        v-if="trainer.photo"
                        v-reveal
                        class="h-28 w-28 shrink-0 overflow-hidden rounded-2xl border-2 border-accent/40 bg-surface-2 sm:h-40 sm:w-40"
                    >
                        <img :src="trainer.photo" :alt="trainer.full_name" class="h-full w-full object-cover">
                    </div>

                    <div v-reveal="80" class="min-w-0">
                        <Badge v-if="trainer.role" tone="accent">{{ trainer.role }}</Badge>
                        <h1 class="mt-4 font-semibold leading-[0.9] tracking-[-0.035em] text-fg text-[clamp(2.25rem,5.5vw,4rem)]">
                            {{ trainer.full_name }}
                        </h1>
                        <p v-if="trainer.nationality" class="mt-4 flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.2em] text-fg-muted">
                            <Icon name="globe" :size="14" />
                            {{ trainer.nationality }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <div class="mx-auto max-w-7xl space-y-20 px-4 py-20 sm:px-6 lg:px-8 lg:space-y-28 lg:py-28">
            <section v-if="trainer.bio">
                <SectionHeading v-reveal number="01" :title="t('trainers.about')" size="lg" />
                <div v-reveal="80" class="mt-8 max-w-3xl">
                    <Prose :html="trainer.bio" size="lg" />
                </div>
            </section>

            <section v-if="trainer.career?.length">
                <SectionHeading v-reveal number="02" :title="t('trainers.work_history')" size="lg" />
                <div v-reveal="80" class="mt-10 max-w-3xl">
                    <CareerTimeline :entries="trainer.career" />
                </div>
            </section>

            <section v-if="contactLinks.length">
                <SectionHeading v-reveal number="03" :title="t('players.contact')" size="lg" />
                <ul v-reveal="80" class="mt-8 flex flex-wrap gap-3">
                    <li v-for="link in contactLinks" :key="link.href">
                        <a
                            :href="link.href"
                            :target="link.href.startsWith('http') ? '_blank' : undefined"
                            :rel="link.href.startsWith('http') ? 'noopener noreferrer' : undefined"
                            class="inline-flex items-center gap-2 rounded-xl border border-border bg-surface px-4 py-2.5 text-sm text-fg-muted transition-colors hover:border-accent hover:text-accent"
                        >
                            <Icon :name="link.icon" :size="15" />
                            {{ link.label }}
                        </a>
                    </li>
                </ul>
            </section>

            <section v-if="related.length">
                <SectionHeading v-reveal :title="t('home.meet_trainers')" size="lg" />
                <div class="mt-10 grid gap-5 lg:grid-cols-2">
                    <div v-for="(item, i) in related" :key="item.id" v-reveal="(i % 2) * 90">
                        <TrainerCard :trainer="item" class="h-full" />
                    </div>
                </div>
            </section>
        </div>
    </PublicLayout>
</template>
