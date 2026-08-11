<script setup>
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { useRoute } from '@/Support/ziggy';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/Data/DataTable.vue';
import Pagination from '@/Components/Data/Pagination.vue';
import SearchInput from '@/Components/Data/SearchInput.vue';
import SelectInput from '@/Components/Form/SelectInput.vue';
import Modal from '@/Components/Ui/Modal.vue';
import Badge from '@/Components/Ui/Badge.vue';
import Button from '@/Components/Ui/Button.vue';
import Icon from '@/Components/Ui/Icon.vue';
import ConfirmDialog from '@/Components/Ui/ConfirmDialog.vue';

const props = defineProps({
    messages: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    unreadCount: { type: Number, default: 0 },
});

const route = useRoute();

const search = ref(props.filters.search ?? '');
const state = ref(props.filters.state ?? null);
const viewing = ref(null);
const deleting = ref(null);

const columns = [
    { key: 'name', label: 'From' },
    { key: 'subject', label: 'Subject', muted: true },
    { key: 'locale', label: 'Language', align: 'center' },
    { key: 'created_at', label: 'Received', muted: true },
    { key: 'actions', label: '', align: 'right', width: 'w-24' },
];

watch([search, state], () => {
    router.get(
        route('admin.messages.index'),
        { search: search.value || undefined, state: state.value || undefined },
        { preserveState: true, preserveScroll: true, replace: true },
    );
});

function open(row) {
    viewing.value = row;

    if (!row.is_read) {
        router.patch(route('admin.messages.read', { message: row.id }), {}, { preserveScroll: true, preserveState: true });
    }
}

function confirmDelete() {
    router.delete(route('admin.messages.destroy', { message: deleting.value.id }), {
        preserveScroll: true,
        onFinish: () => {
            deleting.value = null;
            viewing.value = null;
        },
    });
}
</script>

<template>
    <Head title="Messages" />

    <AdminLayout title="Messages" :subtitle="`${messages.meta.total} total · ${unreadCount} unread`">
        <div class="space-y-4">
            <div class="flex flex-wrap gap-3">
                <div class="min-w-0 flex-1 sm:max-w-xs">
                    <SearchInput v-model="search" placeholder="Search messages…" />
                </div>
                <div class="w-40">
                    <SelectInput
                        v-model="state"
                        :options="[{ value: 'unread', label: 'Unread only' }]"
                        placeholder="All messages"
                    />
                </div>
            </div>

            <DataTable
                :columns="columns"
                :rows="messages.data"
                empty-title="No messages"
                empty-description="Submissions from the public contact form land here."
            >
                <template #cell-name="{ row }">
                    <div class="flex items-center gap-2.5">
                        <span
                            :class="['h-1.5 w-1.5 shrink-0 rounded-full', row.is_read ? 'bg-transparent' : 'bg-accent']"
                            :title="row.is_read ? 'Read' : 'Unread'"
                        />
                        <div class="min-w-0">
                            <button
                                type="button"
                                :class="['block truncate transition-colors hover:text-accent', row.is_read ? 'text-fg' : 'font-semibold text-fg']"
                                @click="open(row)"
                            >{{ row.name }}</button>
                            <p class="truncate text-xs text-fg-subtle">{{ row.email }}</p>
                        </div>
                    </div>
                </template>

                <template #cell-locale="{ row }">
                    <Badge tone="neutral" size="sm">{{ (row.locale || '—').toUpperCase() }}</Badge>
                </template>

                <template #cell-actions="{ row }">
                    <div class="flex items-center justify-end gap-1">
                        <button
                            type="button"
                            class="rounded-lg p-1.5 text-fg-subtle transition-colors hover:bg-surface-2 hover:text-fg"
                            title="Read"
                            @click="open(row)"
                        >
                            <Icon name="eye" :size="15" />
                        </button>
                        <button
                            type="button"
                            class="rounded-lg p-1.5 text-fg-subtle transition-colors hover:bg-danger-soft hover:text-danger"
                            @click="deleting = row"
                        >
                            <Icon name="trash" :size="15" />
                        </button>
                    </div>
                </template>
            </DataTable>

            <Pagination :meta="messages.meta" />
        </div>

        <Modal :open="!!viewing" :title="viewing?.subject || 'Message'" size="lg" @close="viewing = null">
            <div v-if="viewing" class="space-y-4">
                <dl class="grid gap-3 rounded-xl bg-surface-2 p-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-xs text-fg-subtle">From</dt>
                        <dd class="mt-0.5 text-fg">{{ viewing.name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-fg-subtle">Email</dt>
                        <dd class="mt-0.5">
                            <a :href="`mailto:${viewing.email}`" class="text-accent hover:underline">{{ viewing.email }}</a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-fg-subtle">Received</dt>
                        <dd class="mt-0.5 text-fg">{{ viewing.created_at }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-fg-subtle">Language</dt>
                        <dd class="mt-0.5 uppercase text-fg">{{ viewing.locale || '—' }}</dd>
                    </div>
                </dl>

                <p class="whitespace-pre-line text-sm leading-relaxed text-fg-muted">{{ viewing.message }}</p>
            </div>

            <template #footer>
                <Button variant="ghost" @click="deleting = viewing">Delete</Button>
                <Button :href="`mailto:${viewing?.email}`" as="a" icon="mail">Reply</Button>
            </template>
        </Modal>

        <ConfirmDialog
            :open="!!deleting"
            title="Delete message?"
            message="This permanently removes the message."
            @cancel="deleting = null"
            @confirm="confirmDelete"
        />
    </AdminLayout>
</template>
