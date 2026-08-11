<script setup>
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { useI18n } from '@/Composables/useI18n';
import { useRoute } from '@/Support/ziggy';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import SectionHeading from '@/Components/Site/SectionHeading.vue';
import TrainerCard from '@/Components/Site/TrainerCard.vue';
import Pagination from '@/Components/Data/Pagination.vue';
import SearchInput from '@/Components/Data/SearchInput.vue';
import EmptyState from '@/Components/Ui/EmptyState.vue';

const props = defineProps({
    trainers: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const { t } = useI18n();
const route = useRoute();

const search = ref(props.filters.search ?? '');

watch(search, () => {
    router.get(
        route('public.trainers.index'),
        { search: search.value || undefined },
        { preserveState: true, preserveScroll: true, replace: true },
    );
});
</script>

<template>
    <Head :title="t('trainers.title')">
        <meta name="description" :content="t('trainers.lead')">
    </Head>

    <PublicLayout>
        <div class="mx-auto max-w-7xl px-4 py-14 pb-24 sm:px-6 lg:px-8 lg:py-20 lg:pb-28">
            <SectionHeading number="01" :title="t('trainers.title')" :lead="t('trainers.lead')" as="h1" />

            <div class="mt-10 max-w-xs">
                <SearchInput v-model="search" :placeholder="t('common.search_placeholder')" />
            </div>

            <div v-if="trainers.data.length" class="mt-10 grid gap-5 lg:grid-cols-2">
                <TrainerCard v-for="trainer in trainers.data" :key="trainer.id" :trainer="trainer" />
            </div>

            <EmptyState
                v-else
                class="mt-10"
                icon="whistle"
                :title="t('common.no_results')"
                :description="search ? t('common.no_results_hint') : t('trainers.empty')"
            />

            <div v-if="trainers.data.length" class="mt-12">
                <Pagination :meta="trainers.meta" />
            </div>
        </div>
    </PublicLayout>
</template>
