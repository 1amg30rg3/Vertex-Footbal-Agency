<script setup>
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { useI18n } from '@/Composables/useI18n';
import { useRoute } from '@/Support/ziggy';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import PageHero from '@/Components/Site/PageHero.vue';
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
    <Head :title="t('trainers.title')" />

    <PublicLayout>
        <PageHero :title="t('trainers.title')" :lead="t('trainers.lead')">
            <div class="max-w-xs">
                <SearchInput v-model="search" :placeholder="t('common.search_placeholder')" />
            </div>
        </PageHero>

        <div class="mx-auto max-w-7xl px-4 py-16 pb-24 sm:px-6 lg:px-8 lg:py-24 lg:pb-32">
            <div v-if="trainers.data.length" class="grid gap-5 lg:grid-cols-2">
                <div v-for="(trainer, i) in trainers.data" :key="trainer.id" v-reveal="(i % 2) * 90">
                    <TrainerCard :trainer="trainer" class="h-full" />
                </div>
            </div>

            <EmptyState
                v-else
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
