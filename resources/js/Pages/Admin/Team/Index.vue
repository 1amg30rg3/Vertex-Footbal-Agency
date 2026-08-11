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
    members: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    statuses: { type: Array, default: () => [] },
});

const route = useRoute();

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? null);
const deleting = ref(null);

const columns = [
    { key: 'name', label: 'Member' },
    { key: 'role', label: 'Role', muted: true },
    { key: 'email', label: 'Email', muted: true },
    { key: 'status', label: 'Status', align: 'center' },
    { key: 'actions', label: '', align: 'right', width: 'w-24' },
];

watch([search, status], () => {
    router.get(
        route('admin.team.members.index'),
        { search: search.value || undefined, status: status.value || undefined },
        { preserveState: true, preserveScroll: true, replace: true },
    );
});

function onReorder(ids) {
    router.post(route('admin.team.members.reorder'), { ids }, { preserveScroll: true });
}

function confirmDelete() {
    router.delete(route('admin.team.members.destroy', { member: deleting.value.id }), {
        onFinish: () => (deleting.value = null),
    });
}
</script>

<template>
    <Head title="Agency team" />

    <AdminLayout title="Agency team" :subtitle="`${members.meta.total} members`">
        <template #actions>
            <Button :href="route('admin.team.members.create')" size="sm" icon="plus">New member</Button>
        </template>

        <div class="space-y-4">
            <div class="flex flex-wrap gap-3">
                <div class="min-w-0 flex-1 sm:max-w-xs">
                    <SearchInput v-model="search" placeholder="Search members…" />
                </div>
                <div class="w-40">
                    <SelectInput v-model="status" :options="statuses" placeholder="All statuses" />
                </div>
            </div>

            <DataTable
                :columns="columns"
                :rows="members.data"
                reorderable
                empty-title="No team members yet"
                empty-description="Add the people who make the agency run."
                @reorder="onReorder"
            >
                <template #cell-name="{ row }">
                    <div class="flex items-center gap-3">
                        <img v-if="row.photo" :src="row.photo" :alt="row.name" class="h-9 w-9 shrink-0 rounded-full object-cover">
                        <span v-else class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-surface-2 text-fg-subtle">
                            <Icon name="users" :size="15" />
                        </span>
                        <Link
                            :href="route('admin.team.members.edit', { member: row.id })"
                            class="truncate font-medium text-fg transition-colors hover:text-accent"
                        >{{ row.name }}</Link>
                    </div>
                </template>

                <template #cell-status="{ row }">
                    <Badge :tone="row.status === 'published' ? 'success' : 'neutral'" size="sm">{{ row.status }}</Badge>
                </template>

                <template #cell-actions="{ row }">
                    <div class="flex items-center justify-end gap-1">
                        <Link
                            :href="route('admin.team.members.edit', { member: row.id })"
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
                    <Button :href="route('admin.team.members.create')" size="sm" icon="plus">New member</Button>
                </template>
            </DataTable>

            <Pagination :meta="members.meta" />
        </div>

        <ConfirmDialog
            :open="!!deleting"
            title="Delete team member?"
            :message="`“${deleting?.name}” will be removed from the public site.`"
            @cancel="deleting = null"
            @confirm="confirmDelete"
        />
    </AdminLayout>
</template>
