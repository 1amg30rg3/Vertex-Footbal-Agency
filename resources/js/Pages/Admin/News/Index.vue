<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useRoute } from '@/Support/ziggy';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/Data/DataTable.vue';
import Pagination from '@/Components/Data/Pagination.vue';
import SearchInput from '@/Components/Data/SearchInput.vue';
import SelectInput from '@/Components/Form/SelectInput.vue';
import CheckboxInput from '@/Components/Form/CheckboxInput.vue';
import Badge from '@/Components/Ui/Badge.vue';
import Button from '@/Components/Ui/Button.vue';
import Icon from '@/Components/Ui/Icon.vue';
import ConfirmDialog from '@/Components/Ui/ConfirmDialog.vue';

const props = defineProps({
    articles: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    statuses: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
});

const route = useRoute();

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? null);
const category = ref(props.filters.category ?? null);
const featuredOnly = ref(!!props.filters.featured);
const deleting = ref(null);

const statusTones = { published: 'success', draft: 'neutral', scheduled: 'info' };

const columns = [
    { key: 'title', label: 'Article' },
    { key: 'category', label: 'Category', muted: true },
    { key: 'published_at', label: 'Publish date', muted: true },
    { key: 'is_featured', label: 'Homepage', align: 'center' },
    { key: 'status', label: 'Status', align: 'center' },
    { key: 'views', label: 'Views', align: 'right' },
    { key: 'actions', label: '', align: 'right', width: 'w-24' },
];

watch([search, status, category, featuredOnly], () => {
    router.get(
        route('admin.news.index'),
        {
            search: search.value || undefined,
            status: status.value || undefined,
            category: category.value || undefined,
            featured: featuredOnly.value ? 1 : undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
});

function toggleFeatured(row) {
    router.patch(route('admin.news.featured', { news: row.id }), {}, { preserveScroll: true });
}

function confirmDelete() {
    router.delete(route('admin.news.destroy', { news: deleting.value.id }), {
        onFinish: () => (deleting.value = null),
    });
}
</script>

<template>
    <Head title="News" />

    <AdminLayout title="News" :subtitle="`${articles.meta.total} articles`">
        <template #actions>
            <Button :href="route('admin.news.categories.index')" variant="secondary" size="sm" icon="filter">
                Categories
            </Button>
            <Button :href="route('admin.news.create')" size="sm" icon="plus">New article</Button>
        </template>

        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <div class="min-w-0 flex-1 sm:max-w-xs">
                    <SearchInput v-model="search" placeholder="Search articles…" />
                </div>
                <div class="w-40">
                    <SelectInput v-model="status" :options="statuses" placeholder="All statuses" />
                </div>
                <div class="w-48">
                    <SelectInput
                        v-model="category"
                        :options="categories.map((item) => ({ value: item.slug, label: item.label }))"
                        placeholder="All categories"
                    />
                </div>
                <CheckboxInput v-model="featuredOnly" label="Homepage only" />
            </div>

            <DataTable
                :columns="columns"
                :rows="articles.data"
                empty-title="No articles yet"
                empty-description="Publish your first announcement or match report."
            >
                <template #cell-title="{ row }">
                    <div class="flex items-center gap-3">
                        <img v-if="row.cover" :src="row.cover" :alt="row.title" class="h-9 w-14 shrink-0 rounded-md object-cover">
                        <span v-else class="flex h-9 w-14 shrink-0 items-center justify-center rounded-md bg-surface-2 text-fg-subtle">
                            <Icon name="newspaper" :size="15" />
                        </span>
                        <div class="min-w-0">
                            <Link
                                :href="route('admin.news.edit', { news: row.id })"
                                class="block truncate font-medium text-fg transition-colors hover:text-accent"
                            >{{ row.title }}</Link>
                            <p class="truncate text-xs text-fg-subtle">/{{ row.slug }}</p>
                        </div>
                    </div>
                </template>

                <template #cell-is_featured="{ row }">
                    <button
                        type="button"
                        :class="[
                            'rounded-lg p-1.5 transition-colors',
                            row.is_featured ? 'text-accent hover:bg-accent-soft' : 'text-fg-subtle hover:bg-surface-2 hover:text-fg',
                        ]"
                        :title="row.is_featured ? 'Shown on homepage' : 'Show on homepage'"
                        @click="toggleFeatured(row)"
                    >
                        <Icon name="star" :size="16" :filled="row.is_featured" />
                    </button>
                </template>

                <template #cell-status="{ row }">
                    <div class="flex items-center justify-center gap-1.5">
                        <Badge :tone="statusTones[row.status] ?? 'neutral'" size="sm">{{ row.status }}</Badge>
                        <Icon
                            v-if="row.status !== 'draft' && !row.is_live"
                            name="clock"
                            :size="13"
                            class="text-fg-subtle"
                            title="Not live yet"
                        />
                    </div>
                </template>

                <template #cell-views="{ row }">
                    <span class="tabular-nums text-fg-muted">{{ row.views }}</span>
                </template>

                <template #cell-actions="{ row }">
                    <div class="flex items-center justify-end gap-1">
                        <Link
                            :href="route('admin.news.edit', { news: row.id })"
                            class="rounded-lg p-1.5 text-fg-subtle transition-colors hover:bg-surface-2 hover:text-fg"
                        >
                            <Icon name="edit" :size="15" />
                        </Link>
                        <button
                            type="button"
                            class="rounded-lg p-1.5 text-fg-subtle transition-colors hover:bg-danger-soft hover:text-danger"
                            @click="deleting = row"
                        >
                            <Icon name="trash" :size="15" />
                        </button>
                    </div>
                </template>

                <template #empty-action>
                    <Button :href="route('admin.news.create')" size="sm" icon="plus">New article</Button>
                </template>
            </DataTable>

            <Pagination :meta="articles.meta" />
        </div>

        <ConfirmDialog
            :open="!!deleting"
            title="Delete article?"
            :message="`“${deleting?.title}” will be removed from the public site.`"
            @cancel="deleting = null"
            @confirm="confirmDelete"
        />
    </AdminLayout>
</template>
