<script setup>
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { useI18n } from '@/Composables/useI18n';
import { useRoute } from '@/Support/ziggy';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import SectionHeading from '@/Components/Site/SectionHeading.vue';
import PlayerCard from '@/Components/Site/PlayerCard.vue';
import Pagination from '@/Components/Data/Pagination.vue';
import SearchInput from '@/Components/Data/SearchInput.vue';
import SelectInput from '@/Components/Form/SelectInput.vue';
import EmptyState from '@/Components/Ui/EmptyState.vue';

const props = defineProps({
    players: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    positions: { type: Array, default: () => [] },
});

const { t } = useI18n();
const route = useRoute();

const search = ref(props.filters.search ?? '');
const position = ref(props.filters.position ?? null);

function apply() {
    router.get(
        route('public.players.index'),
        { search: search.value || undefined, position: position.value || undefined },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

watch([search, position], apply);
</script>

<template>
    <Head :title="t('players.title')">
        <meta name="description" :content="t('players.lead')">
    </Head>

    <PublicLayout>
        <div class="mx-auto max-w-7xl px-4 py-14 pb-24 sm:px-6 lg:px-8 lg:py-20 lg:pb-28">
            <SectionHeading number="01" :title="t('players.title')" :lead="t('players.lead')" as="h1" />

            <div class="mt-10 flex flex-wrap gap-3">
                <div class="min-w-0 flex-1 sm:max-w-xs">
                    <SearchInput v-model="search" :placeholder="t('players.filters.search')" />
                </div>
                <div class="w-full sm:w-56">
                    <SelectInput
                        v-model="position"
                        :options="positions.map((value) => ({ value, label: t(`players.positions.${value}`) }))"
                        :placeholder="t('players.filters.position')"
                    />
                </div>
            </div>

            <div v-if="players.data.length" class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <PlayerCard v-for="player in players.data" :key="player.id" :player="player" />
            </div>

            <EmptyState
                v-else
                class="mt-10"
                icon="users"
                :title="t('common.no_results')"
                :description="search || position ? t('common.no_results_hint') : t('players.empty')"
            />

            <div v-if="players.data.length" class="mt-12">
                <Pagination :meta="players.meta" />
            </div>
        </div>
    </PublicLayout>
</template>
