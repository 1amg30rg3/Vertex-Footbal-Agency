<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useRoute } from '@/Support/ziggy';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/Data/DataTable.vue';
import Pagination from '@/Components/Data/Pagination.vue';
import SearchInput from '@/Components/Data/SearchInput.vue';
import SelectInput from '@/Components/Form/SelectInput.vue';
import Badge from '@/Components/Ui/Badge.vue';
import Button from '@/Components/Ui/Button.vue';
import Icon from '@/Components/Ui/Icon.vue';
import ConfirmDialog from '@/Components/Ui/ConfirmDialog.vue';

const props = defineProps({
    players: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    positions: { type: Array, default: () => [] },
    statuses: { type: Array, default: () => [] },
});

const route = useRoute();

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? null);
const position = ref(props.filters.position ?? null);
const deleting = ref(null);

const columns = [
    { key: 'name', label: 'Player', sortable: false },
    { key: 'position', label: 'Position', sortable: true },
    { key: 'current_club', label: 'Club', sortable: true, muted: true },
    { key: 'seasons_count', label: 'Seasons', align: 'center' },
    { key: 'status', label: 'Status', sortable: true, align: 'center' },
    { key: 'updated_at', label: 'Updated', sortable: true, muted: true },
    { key: 'actions', label: '', align: 'right', width: 'w-24' },
];

function reload(extra = {}) {
    router.get(
        route('admin.players.index'),
        {
            search: search.value || undefined,
            status: status.value || undefined,
            position: position.value || undefined,
            ...props.filters,
            ...extra,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

watch([search, status, position], () => reload({ sort: undefined, direction: undefined }));

function onSort({ sort, direction }) {
    reload({ sort, direction });
}

function onReorder(ids) {
    router.post(route('admin.players.reorder'), { ids }, { preserveScroll: true });
}

function confirmDelete() {
    router.delete(route('admin.players.destroy', { player: deleting.value.id }), {
        onFinish: () => (deleting.value = null),
    });
}
</script>

<template>
    <Head title="Players" />

    <AdminLayout title="Players" :subtitle="`${players.meta.total} total`">
        <template #actions>
            <Button :href="route('admin.players.create')" size="sm" icon="plus">New player</Button>
        </template>

        <div class="space-y-4">
            <div class="flex flex-wrap gap-3">
                <div class="min-w-0 flex-1 sm:max-w-xs">
                    <SearchInput v-model="search" placeholder="Search players…" />
                </div>
                <div class="w-40">
                    <SelectInput v-model="status" :options="statuses" placeholder="All statuses" />
                </div>
                <div class="w-44">
                    <SelectInput
                        v-model="position"
                        :options="positions.map((value) => ({ value, label: value }))"
                        placeholder="All positions"
                    />
                </div>
            </div>

            <DataTable
                :columns="columns"
                :rows="players.data"
                :sort="filters.sort"
                :direction="filters.direction"
                reorderable
                empty-title="No players yet"
                empty-description="Create your first player profile to get started."
                @sort="onSort"
                @reorder="onReorder"
            >
                <template #cell-name="{ row }">
                    <div class="flex items-center gap-3">
                        <img
                            v-if="row.photo"
                            :src="row.photo"
                            :alt="row.name"
                            class="h-9 w-9 shrink-0 rounded-lg object-cover"
                        >
                        <span
                            v-else
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-surface-2 text-fg-subtle"
                        >
                            <Icon name="users" :size="15" />
                        </span>

                        <div class="min-w-0">
                            <Link
                                :href="route('admin.players.edit', { player: row.id })"
                                class="block truncate font-medium text-fg transition-colors hover:text-accent"
                            >{{ row.name }}</Link>
                            <p class="truncate text-xs text-fg-subtle">/{{ row.slug }}</p>
                        </div>

                        <Icon v-if="row.is_featured" name="star" :size="13" filled class="shrink-0 text-accent" />
                    </div>
                </template>

                <template #cell-position="{ row }">
                    <span class="capitalize text-fg-muted">{{ row.position ?? '—' }}</span>
                </template>

                <template #cell-status="{ row }">
                    <Badge :tone="row.status === 'published' ? 'success' : 'neutral'" size="sm">
                        {{ row.status }}
                    </Badge>
                </template>

                <template #cell-actions="{ row }">
                    <div class="flex items-center justify-end gap-1">
                        <Link
                            :href="route('admin.players.edit', { player: row.id })"
                            class="rounded-lg p-1.5 text-fg-subtle transition-colors hover:bg-surface-2 hover:text-fg"
                            title="Edit"
                        >
                            <Icon name="edit" :size="15" />
                        </Link>
                        <button
                            type="button"
                            class="rounded-lg p-1.5 text-fg-subtle transition-colors hover:bg-danger-soft hover:text-danger"
                            title="Delete"
                            @click="deleting = row"
                        >
                            <Icon name="trash" :size="15" />
                        </button>
                    </div>
                </template>

                <template #empty-action>
                    <Button :href="route('admin.players.create')" size="sm" icon="plus">New player</Button>
                </template>
            </DataTable>

            <Pagination :meta="players.meta" />
        </div>

        <ConfirmDialog
            :open="!!deleting"
            title="Delete player?"
            :message="`“${deleting?.name}” will be removed from the public site. This can be undone by a developer, but not from this panel.`"
            @cancel="deleting = null"
            @confirm="confirmDelete"
        />
    </AdminLayout>
</template>
