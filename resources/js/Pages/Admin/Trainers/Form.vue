<script setup>
import { computed, ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { cloneForForm } from '@/Support/clone';
import { scrollToFirstError } from '@/Support/form';
import { useRoute } from '@/Support/ziggy';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import FormSection from '@/Components/Form/FormSection.vue';
import StickySectionNav from '@/Components/Form/StickySectionNav.vue';
import LanguageTabs from '@/Components/Form/LanguageTabs.vue';
import FormField from '@/Components/Form/FormField.vue';
import TextInput from '@/Components/Form/TextInput.vue';
import SelectInput from '@/Components/Form/SelectInput.vue';
import RichTextEditor from '@/Components/Form/RichTextEditor.vue';
import ImageUploader from '@/Components/Form/ImageUploader.vue';
import Repeater from '@/Components/Form/Repeater.vue';
import Button from '@/Components/Ui/Button.vue';
import Alert from '@/Components/Ui/Alert.vue';
import ConfirmDialog from '@/Components/Ui/ConfirmDialog.vue';

const props = defineProps({
    trainer: { type: Object, required: true },
    options: { type: Object, required: true },
});

const route = useRoute();
const page = usePage();

const isEdit = computed(() => !!props.trainer.id);
const locales = computed(() => page.props.locales ?? []);
const defaultLocale = computed(() => page.props.defaultLocale ?? 'ka');

const deleting = ref(false);
const form = useForm(cloneForForm(props.trainer));

const sections = [
    { id: 'profile', number: '01', label: 'Profile' },
    { id: 'history', number: '02', label: 'Work history' },
    { id: 'meta', number: '03', label: 'Publishing & SEO' },
];

const blankMap = () => Object.fromEntries(locales.value.map((item) => [item.code, '']));
const key = () => Math.random().toString(36).slice(2);

const newWork = () => ({
    _key: key(),
    id: null,
    organization: '',
    logo_path: null,
    logo_url: null,
    title: blankMap(),
    started_on: null,
    ended_on: null,
    notes: blankMap(),
});

function submit() {
    form.transform((data) => ({
        ...data,
        work: (data.work ?? []).map(({ _key, ...rest }) => rest),
    }));

    isEdit.value
        ? form.put(route('admin.trainers.update', { trainer: props.trainer.id }), { preserveScroll: true, onError: scrollToFirstError })
        : form.post(route('admin.trainers.store'), { preserveScroll: true, onError: scrollToFirstError });
}

function destroy() {
    form.delete(route('admin.trainers.destroy', { trainer: props.trainer.id }));
}
</script>

<template>
    <Head :title="isEdit ? 'Edit trainer' : 'New trainer'" />

    <AdminLayout
        :title="isEdit ? 'Edit trainer' : 'New trainer'"
        :subtitle="isEdit ? `/${trainer.slug}` : 'Create a new trainer profile'"
    >
        <template #actions>
            <Button v-if="isEdit" variant="ghost" size="sm" icon="trash" @click="deleting = true">Delete</Button>
            <Button size="sm" icon="save" :loading="form.processing" @click="submit">Save</Button>
        </template>

        <form class="grid gap-6 lg:grid-cols-[13rem_1fr]" @submit.prevent="submit">
            <aside class="hidden lg:block">
                <StickySectionNav :sections="sections" />
            </aside>

            <div class="min-w-0 space-y-6">
                <Alert v-if="Object.keys(form.errors).length" tone="danger" title="Some fields need attention">
                    Check the highlighted language tabs and required fields below.
                </Alert>

                <FormSection id="profile" number="01" title="Profile">
                    <div class="grid gap-5 sm:grid-cols-[10rem_1fr]">
                        <ImageUploader
                            v-model="form.photo_path"
                            :preview-url="trainer.photo_url"
                            label="Photo"
                            :aspect-ratio="1"
                            height="h-40"
                        />

                        <div class="space-y-4">
                            <LanguageTabs
                                v-model="form.first_name"
                                label="First name"
                                name="first_name"
                                :errors="form.errors"
                                required
                                v-slot="{ locale }"
                            >
                                <TextInput v-model="form.first_name[locale]" />
                            </LanguageTabs>

                            <LanguageTabs
                                v-model="form.last_name"
                                label="Last name"
                                name="last_name"
                                :errors="form.errors"
                                required
                                v-slot="{ locale }"
                            >
                                <TextInput v-model="form.last_name[locale]" />
                            </LanguageTabs>
                        </div>
                    </div>

                    <ImageUploader
                        v-model="form.cover_path"
                        :preview-url="trainer.cover_url"
                        label="Cover photo"
                        :aspect-ratio="16 / 9"
                        height="h-48"
                    />

                    <LanguageTabs v-model="form.role" label="Role" name="role" :errors="form.errors" v-slot="{ locale }">
                        <TextInput v-model="form.role[locale]" placeholder="Head of Development" />
                    </LanguageTabs>

                    <LanguageTabs
                        v-model="form.bio"
                        label="Short description"
                        name="bio"
                        :errors="form.errors"
                        v-slot="{ locale }"
                    >
                        <RichTextEditor v-model="form.bio[locale]" placeholder="A short biography…" />
                    </LanguageTabs>

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <FormField label="Nationality" :error="form.errors.nationality" v-slot="{ id, invalid }">
                            <TextInput :id="id" v-model="form.nationality" :invalid="invalid" />
                        </FormField>
                        <FormField label="Date of birth" :error="form.errors.date_of_birth" v-slot="{ id, invalid }">
                            <TextInput :id="id" v-model="form.date_of_birth" type="date" :invalid="invalid" />
                        </FormField>
                        <FormField label="Email" :error="form.errors.email" v-slot="{ id, invalid }">
                            <TextInput :id="id" v-model="form.email" type="email" icon="mail" :invalid="invalid" />
                        </FormField>
                        <FormField label="Phone" :error="form.errors.phone" v-slot="{ id, invalid }">
                            <TextInput :id="id" v-model="form.phone" icon="phone" :invalid="invalid" />
                        </FormField>
                        <FormField label="Instagram" :error="form.errors.instagram" v-slot="{ id, invalid }">
                            <TextInput :id="id" v-model="form.instagram" icon="instagram" :invalid="invalid" />
                        </FormField>
                        <FormField label="LinkedIn" :error="form.errors.linkedin" v-slot="{ id, invalid }">
                            <TextInput :id="id" v-model="form.linkedin" icon="linkedin" :invalid="invalid" />
                        </FormField>
                    </div>
                </FormSection>

                <FormSection
                    id="history"
                    number="02"
                    title="Work history"
                    description="Where and when they worked."
                >
                    <Repeater
                        v-model="form.work"
                        add-label="Add role"
                        empty-label="No work history yet."
                        :new-row="newWork"
                        collapsible
                        :max="40"
                    >
                        <template #summary="{ row }">
                            <span class="truncate text-sm text-fg-muted">{{ row.organization || 'New entry' }}</span>
                        </template>

                        <template #default="{ row, index }">
                            <div class="space-y-4">
                                <div class="grid gap-4 sm:grid-cols-[7rem_1fr]">
                                    <ImageUploader
                                        v-model="row.logo_path"
                                        :preview-url="row.logo_url"
                                        label="Logo"
                                        height="h-20"
                                        :croppable="false"
                                    />

                                    <FormField
                                        label="Organisation"
                                        :error="form.errors[`work.${index}.organization`]"
                                        required
                                        v-slot="{ id, invalid }"
                                    >
                                        <TextInput :id="id" v-model="row.organization" size="sm" :invalid="invalid" />
                                    </FormField>
                                </div>

                                <LanguageTabs
                                    v-model="row.title"
                                    label="Role held"
                                    :name="`work.${index}.title`"
                                    :errors="form.errors"
                                    :copyable="false"
                                    v-slot="{ locale }"
                                >
                                    <TextInput v-model="row.title[locale]" size="sm" placeholder="Assistant coach" />
                                </LanguageTabs>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <FormField label="From" v-slot="{ id }">
                                        <TextInput :id="id" v-model="row.started_on" type="date" size="sm" />
                                    </FormField>
                                    <FormField
                                        label="To"
                                        hint="Leave empty if still there."
                                        :error="form.errors[`work.${index}.ended_on`]"
                                        v-slot="{ id, invalid }"
                                    >
                                        <TextInput :id="id" v-model="row.ended_on" type="date" size="sm" :invalid="invalid" />
                                    </FormField>
                                </div>

                                <LanguageTabs
                                    v-model="row.notes"
                                    label="Notes"
                                    :name="`work.${index}.notes`"
                                    :errors="form.errors"
                                    :copyable="false"
                                    v-slot="{ locale }"
                                >
                                    <TextInput v-model="row.notes[locale]" size="sm" />
                                </LanguageTabs>
                            </div>
                        </template>
                    </Repeater>
                </FormSection>

                <FormSection id="meta" number="03" title="Publishing &amp; SEO">
                    <div class="grid gap-4 sm:grid-cols-3">
                        <FormField label="Status" :error="form.errors.status" v-slot="{ id }">
                            <SelectInput
                                :id="id"
                                v-model="form.status"
                                :options="options.statuses.map((value) => ({ value, label: value }))"
                            />
                        </FormField>
                        <FormField label="Slug" hint="Auto-generated if empty." :error="form.errors.slug" v-slot="{ id, invalid }">
                            <TextInput :id="id" v-model="form.slug" :invalid="invalid" />
                        </FormField>
                        <FormField label="Sort order" :error="form.errors.sort_order" v-slot="{ id }">
                            <TextInput :id="id" v-model="form.sort_order" type="number" min="0" />
                        </FormField>
                    </div>

                    <LanguageTabs v-model="form.seo_title" label="SEO title" name="seo_title" :errors="form.errors" v-slot="{ locale }">
                        <TextInput v-model="form.seo_title[locale]" />
                    </LanguageTabs>

                    <LanguageTabs
                        v-model="form.seo_description"
                        label="SEO description"
                        name="seo_description"
                        :errors="form.errors"
                        v-slot="{ locale }"
                    >
                        <TextInput v-model="form.seo_description[locale]" />
                    </LanguageTabs>
                </FormSection>

                <div class="flex flex-wrap items-center justify-end gap-3 pb-6">
                    <Button variant="ghost" :href="route('admin.trainers.index')">Cancel</Button>
                    <Button type="submit" icon="save" :loading="form.processing">
                        {{ isEdit ? 'Save changes' : 'Create trainer' }}
                    </Button>
                </div>
            </div>
        </form>

        <ConfirmDialog
            :open="deleting"
            title="Delete trainer?"
            message="This removes the trainer and their work history from the public site."
            @cancel="deleting = false"
            @confirm="destroy"
        />
    </AdminLayout>
</template>
