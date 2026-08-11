<script setup>
import { computed, ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { useRoute } from '@/Support/ziggy';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/Data/DataTable.vue';
import LanguageTabs from '@/Components/Form/LanguageTabs.vue';
import FormField from '@/Components/Form/FormField.vue';
import TextInput from '@/Components/Form/TextInput.vue';
import Modal from '@/Components/Ui/Modal.vue';
import Button from '@/Components/Ui/Button.vue';
import Icon from '@/Components/Ui/Icon.vue';
import ConfirmDialog from '@/Components/Ui/ConfirmDialog.vue';

defineProps({ categories: { type: Array, default: () => [] } });

const route = useRoute();
const page = usePage();

const editing = ref(null);
const deleting = ref(null);
const modalOpen = ref(false);

const locales = computed(() => page.props.locales ?? []);
const blankMap = () => Object.fromEntries(locales.value.map((item) => [item.code, '']));

const form = useForm({ name: {}, slug: '', color: '#cd942c', sort_order: 0 });

const columns = [
    { key: 'display_name', label: 'Category' },
    { key: 'slug', label: 'Slug', muted: true },
    { key: 'news_count', label: 'Articles', align: 'center' },
    { key: 'sort_order', label: 'Order', align: 'center' },
    { key: 'actions', label: '', align: 'right', width: 'w-24' },
];

function openCreate() {
    editing.value = null;
    form.defaults({ name: blankMap(), slug: '', color: '#cd942c', sort_order: 0 });
    form.reset();
    form.clearErrors();
    modalOpen.value = true;
}

function openEdit(row) {
    editing.value = row;
    form.defaults({
        name: { ...blankMap(), ...row.name },
        slug: row.slug,
        color: row.color ?? '#cd942c',
        sort_order: row.sort_order,
    });
    form.reset();
    form.clearErrors();
    modalOpen.value = true;
}

function submit() {
    const options = {
        preserveScroll: true,
        onSuccess: () => (modalOpen.value = false),
    };

    editing.value
        ? form.put(route('admin.news.categories.update', { category: editing.value.id }), options)
        : form.post(route('admin.news.categories.store'), options);
}

function confirmDelete() {
    form.delete(route('admin.news.categories.destroy', { category: deleting.value.id }), {
        preserveScroll: true,
        onFinish: () => (deleting.value = null),
    });
}
</script>

<template>
    <Head title="News categories" />

    <AdminLayout title="News categories" subtitle="Optional grouping for articles.">
        <template #actions>
            <Button :href="route('admin.news.index')" variant="secondary" size="sm" icon="arrowLeft">Back to news</Button>
            <Button size="sm" icon="plus" @click="openCreate">New category</Button>
        </template>

        <DataTable
            :columns="columns"
            :rows="categories"
            empty-title="No categories yet"
            empty-description="Categories are optional — articles work fine without them."
        >
            <template #cell-display_name="{ row }">
                <div class="flex items-center gap-2.5">
                    <span class="h-3 w-3 shrink-0 rounded-full" :style="{ background: row.color || 'var(--accent)' }" />
                    <button type="button" class="font-medium text-fg transition-colors hover:text-accent" @click="openEdit(row)">
                        {{ row.display_name }}
                    </button>
                </div>
            </template>

            <template #cell-actions="{ row }">
                <div class="flex items-center justify-end gap-1">
                    <button
                        type="button"
                        class="rounded-lg p-1.5 text-fg-subtle transition-colors hover:bg-surface-2 hover:text-fg"
                        @click="openEdit(row)"
                    >
                        <Icon name="edit" :size="15" />
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

            <template #empty-action>
                <Button size="sm" icon="plus" @click="openCreate">New category</Button>
            </template>
        </DataTable>

        <Modal :open="modalOpen" :title="editing ? 'Edit category' : 'New category'" @close="modalOpen = false">
            <div class="space-y-4">
                <LanguageTabs v-model="form.name" label="Name" name="name" :errors="form.errors" required v-slot="{ locale }">
                    <TextInput v-model="form.name[locale]" />
                </LanguageTabs>

                <div class="grid gap-4 sm:grid-cols-3">
                    <FormField label="Slug" hint="Auto if empty." :error="form.errors.slug" v-slot="{ id, invalid }" class="sm:col-span-2">
                        <TextInput :id="id" v-model="form.slug" :invalid="invalid" />
                    </FormField>

                    <FormField label="Colour" :error="form.errors.color" v-slot="{ id }">
                        <input
                            :id="id"
                            v-model="form.color"
                            type="color"
                            class="h-10 w-full cursor-pointer rounded-lg border border-border bg-bg-elevated p-1"
                        >
                    </FormField>
                </div>

                <FormField label="Sort order" :error="form.errors.sort_order" v-slot="{ id }">
                    <TextInput :id="id" v-model="form.sort_order" type="number" min="0" />
                </FormField>
            </div>

            <template #footer>
                <Button variant="ghost" @click="modalOpen = false">Cancel</Button>
                <Button :loading="form.processing" @click="submit">{{ editing ? 'Save' : 'Create' }}</Button>
            </template>
        </Modal>

        <ConfirmDialog
            :open="!!deleting"
            title="Delete category?"
            message="Articles in this category stay published; they simply become uncategorised."
            @cancel="deleting = null"
            @confirm="confirmDelete"
        />
    </AdminLayout>
</template>
