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
    trainers: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    statuses: { type: Array, default: () => [] },
});

const route = useRoute();

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? null);
const deleting = ref(null);

const columns = [
    { key: 'name', label: 'Trainer' },
    { key: 'role', label: 'Role', muted: true },
    { key: 'work_entries_count', label: 'Roles held', align: 'center' },
    { key: 'status', label: 'Status', align: 'center' },
    { key: 'updated_at', label: 'Updated', muted: true },
    { key: 'actions', label: '', align: 'right', width: 'w-24' },
];

watch([search, status], () => {
    router.get(
        route('admin.trainers.index'),
        { search: search.value || undefined, status: status.value || undefined },
        { preserveState: true, preserveScroll: true, replace: true },
    );
});

function onReorder(ids) {
    router.post(route('admin.trainers.reorder'), { ids }, { preserveScroll: true });
}

function confirmDelete() {
    router.delete(route('admin.trainers.destroy', { trainer: deleting.value.id }), {
        onFinish: () => (deleting.value = null),
    });
}
</script>

<template>
    <Head title="Trainers" />

    <AdminLayout title="Trainers" :subtitle="`${trainers.meta.total} total`">
        <template #actions>
            <Button :href="route('admin.trainers.create')" size="sm" icon="plus">New trainer</Button>
        </template>

        <div class="space-y-4">
            <div class="flex flex-wrap gap-3">
                <div class="min-w-0 flex-1 sm:max-w-xs">
                    <SearchInput v-model="search" placeholder="Search trainers…" />
                </div>
                <div class="w-40">
                    <SelectInput v-model="status" :options="statuses" placeholder="All statuses" />
                </div>
            </div>

            <DataTable
                :columns="columns"
                :rows="trainers.data"
                reorderable
                empty-title="No trainers yet"
                empty-description="Add the coaching staff behind your athletes."
                @reorder="onReorder"
            >
                <template #cell-name="{ row }">
                    <div class="flex items-center gap-3">
                        <img v-if="row.photo" :src="row.photo" :alt="row.name" class="h-9 w-9 shrink-0 rounded-lg object-cover">
                        <span v-else class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-surface-2 text-fg-subtle">
                            <Icon name="whistle" :size="15" />
                        </span>
                        <div class="min-w-0">
                            <Link
                                :href="route('admin.trainers.edit', { trainer: row.id })"
                                class="block truncate font-medium text-fg transition-colors hover:text-accent"
                            >{{ row.name }}</Link>
                            <p class="truncate text-xs text-fg-subtle">/{{ row.slug }}</p>
                        </div>
                    </div>
                </template>

                <template #cell-status="{ row }">
                    <Badge :tone="row.status === 'published' ? 'success' : 'neutral'" size="sm">{{ row.status }}</Badge>
                </template>

                <template #cell-actions="{ row }">
                    <div class="flex items-center justify-end gap-1">
                        <Link
                            :href="route('admin.trainers.edit', { trainer: row.id })"
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
                    <Button :href="route('admin.trainers.create')" size="sm" icon="plus">New trainer</Button>
                </template>
            </DataTable>

            <Pagination :meta="trainers.meta" />
        </div>

        <ConfirmDialog
            :open="!!deleting"
            title="Delete trainer?"
            :message="`“${deleting?.name}” will be removed from the public site.`"
            @cancel="deleting = null"
            @confirm="confirmDelete"
        />
    </AdminLayout>
</template>
