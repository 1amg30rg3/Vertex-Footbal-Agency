<script setup>
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { useI18n } from '@/Composables/useI18n';
import { useRoute } from '@/Support/ziggy';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import PageHero from '@/Components/Site/PageHero.vue';
import NewsCard from '@/Components/Site/NewsCard.vue';
import Pagination from '@/Components/Data/Pagination.vue';
import SearchInput from '@/Components/Data/SearchInput.vue';
import EmptyState from '@/Components/Ui/EmptyState.vue';

const props = defineProps({
    articles: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    categories: { type: Array, default: () => [] },
});

const { t } = useI18n();
const route = useRoute();

const search = ref(props.filters.search ?? '');
const category = ref(props.filters.category ?? null);

function apply() {
    router.get(
        route('public.news.index'),
        { search: search.value || undefined, category: category.value || undefined },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

watch([search, category], apply);

function selectCategory(slug) {
    category.value = category.value === slug ? null : slug;
}
</script>

<template>
    <Head :title="t('news.title')" />

    <PublicLayout>
        <PageHero :title="t('news.title')" :lead="t('news.lead')">
            <div class="flex flex-wrap items-center gap-3">
                <div class="min-w-0 flex-1 sm:max-w-xs">
                    <SearchInput v-model="search" :placeholder="t('common.search_placeholder')" />
                </div>

                <div v-if="categories.length" class="flex flex-wrap items-center gap-1.5">
                    <button
                        type="button"
                        :class="[
                            'rounded-full border px-3 py-1.5 text-xs font-medium transition-colors',
                            !category ? 'border-accent bg-accent-soft text-accent' : 'border-border text-fg-muted hover:text-fg',
                        ]"
                        @click="category = null"
                    >{{ t('news.all_categories') }}</button>

                    <button
                        v-for="item in categories"
                        :key="item.id"
                        type="button"
                        :class="[
                            'rounded-full border px-3 py-1.5 text-xs font-medium transition-colors',
                            category === item.slug ? 'border-accent bg-accent-soft text-accent' : 'border-border text-fg-muted hover:text-fg',
                        ]"
                        @click="selectCategory(item.slug)"
                    >
                        {{ item.name }}
                        <span v-if="item.count != null" class="ml-1 tabular-nums opacity-60">{{ item.count }}</span>
                    </button>
                </div>
            </div>
        </PageHero>

        <div class="mx-auto max-w-7xl px-4 py-16 pb-24 sm:px-6 lg:px-8 lg:py-24 lg:pb-32">
            <div v-if="articles.data.length" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div v-for="(article, i) in articles.data" :key="article.id" v-reveal="(i % 3) * 90">
                    <NewsCard :article="article" class="h-full" />
                </div>
            </div>

            <EmptyState
                v-else
                icon="newspaper"
                :title="t('common.no_results')"
                :description="search || category ? t('common.no_results_hint') : t('news.empty')"
            />

            <div v-if="articles.data.length" class="mt-12">
                <Pagination :meta="articles.meta" />
            </div>
        </div>
    </PublicLayout>
</template>
