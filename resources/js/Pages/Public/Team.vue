<script setup>
import { Head } from '@inertiajs/vue3';
import { useI18n } from '@/Composables/useI18n';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import SectionHeading from '@/Components/Site/SectionHeading.vue';
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
        <div class="mx-auto max-w-7xl px-4 py-14 pb-24 sm:px-6 lg:px-8 lg:py-20 lg:pb-28">
            <SectionHeading number="01" :title="t('team.title')" :lead="t('team.lead')" as="h1" />

            <div
                v-if="members.length"
                class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
            >
                <TeamMemberCard v-for="member in members" :key="member.id" :member="member" />
            </div>

            <EmptyState v-else class="mt-10" icon="users" :title="t('team.empty')" />
        </div>
    </PublicLayout>
</template>
