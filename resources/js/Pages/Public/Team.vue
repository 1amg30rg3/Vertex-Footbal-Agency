<script setup>
import { Head } from '@inertiajs/vue3';
import { useI18n } from '@/Composables/useI18n';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import PageHero from '@/Components/Site/PageHero.vue';
import TeamMemberCard from '@/Components/Site/TeamMemberCard.vue';
import EmptyState from '@/Components/Ui/EmptyState.vue';

defineProps({
    members: { type: Array, default: () => [] },
});

const { t } = useI18n();
</script>

<template>
    <Head :title="t('team.title')">
        <meta name="description" :content="t('team.lead')">
    </Head>

    <PublicLayout>
        <PageHero :title="t('team.title')" :lead="t('team.lead')" />

        <div class="mx-auto max-w-6xl px-4 py-16 pb-24 sm:px-6 lg:px-8 lg:py-24 lg:pb-32">
            <div v-if="members.length" class="grid items-start gap-6 md:grid-cols-2 lg:gap-8">
                <div v-for="(member, i) in members" :key="member.id" v-reveal="(i % 2) * 90">
                    <TeamMemberCard :member="member" />
                </div>
            </div>

            <EmptyState v-else icon="users" :title="t('team.empty')" />
        </div>
    </PublicLayout>
</template>
