<script setup>
import { computed, ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { cloneForForm } from '@/Support/clone';
import { scrollToFirstError } from '@/Support/form';
import { useRoute } from '@/Support/ziggy';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import FormSection from '@/Components/Form/FormSection.vue';
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
    member: { type: Object, required: true },
    options: { type: Object, required: true },
});

const route = useRoute();
const page = usePage();

const isEdit = computed(() => !!props.member.id);
const deleting = ref(false);
const form = useForm(cloneForForm(props.member));

const key = () => Math.random().toString(36).slice(2);
const newSocial = () => ({ _key: key(), platform: 'instagram', url: '' });

const platforms = ['instagram', 'facebook', 'linkedin', 'twitter', 'youtube', 'link'];

function submit() {
    form.transform((data) => ({
        ...data,
        social_links: (data.social_links ?? []).map(({ _key, ...rest }) => rest),
    }));

    isEdit.value
        ? form.put(route('admin.team.members.update', { member: props.member.id }), { preserveScroll: true, onError: scrollToFirstError })
        : form.post(route('admin.team.members.store'), { preserveScroll: true, onError: scrollToFirstError });
}

function destroy() {
    form.delete(route('admin.team.members.destroy', { member: props.member.id }));
}
</script>

<template>
    <Head :title="isEdit ? 'Edit team member' : 'New team member'" />

    <AdminLayout :title="isEdit ? 'Edit team member' : 'New team member'">
        <template #actions>
            <Button v-if="isEdit" variant="ghost" size="sm" icon="trash" @click="deleting = true">Delete</Button>
            <Button size="sm" icon="save" :loading="form.processing" @click="submit">Save</Button>
        </template>

        <form class="mx-auto max-w-3xl space-y-6" @submit.prevent="submit">
            <Alert v-if="Object.keys(form.errors).length" tone="danger" title="Some fields need attention">
                Check the highlighted language tabs and required fields below.
            </Alert>

            <FormSection id="details" number="01" title="Details">
                <div class="grid gap-5 sm:grid-cols-[10rem_1fr]">
                    <ImageUploader
                        v-model="form.photo_path"
                        :preview-url="member.photo_url"
                        label="Photo"
                        :aspect-ratio="4 / 5"
                        height="h-44"
                    />

                    <div class="space-y-4">
                        <LanguageTabs v-model="form.name" label="Name" name="name" :errors="form.errors" required v-slot="{ locale }">
                            <TextInput v-model="form.name[locale]" />
                        </LanguageTabs>

                        <LanguageTabs v-model="form.role" label="Role" name="role" :errors="form.errors" v-slot="{ locale }">
                            <TextInput v-model="form.role[locale]" placeholder="Player agent" />
                        </LanguageTabs>
                    </div>
                </div>

                <LanguageTabs v-model="form.bio" label="Short bio" name="bio" :errors="form.errors" v-slot="{ locale }">
                    <RichTextEditor v-model="form.bio[locale]" compact min-height="8rem" />
                </LanguageTabs>

                <div class="grid gap-4 sm:grid-cols-2">
                    <FormField label="Email" :error="form.errors.email" v-slot="{ id, invalid }">
                        <TextInput :id="id" v-model="form.email" type="email" icon="mail" :invalid="invalid" />
                    </FormField>
                    <FormField label="Phone" :error="form.errors.phone" v-slot="{ id, invalid }">
                        <TextInput :id="id" v-model="form.phone" icon="phone" :invalid="invalid" />
                    </FormField>
                </div>

                <Repeater
                    v-model="form.social_links"
                    label="Social links"
                    add-label="Add link"
                    empty-label="No social links."
                    :new-row="newSocial"
                    :boxed="false"
                    :max="10"
                >
                    <template #default="{ row, index }">
                        <div class="grid gap-3 sm:grid-cols-[10rem_1fr]">
                            <FormField label="Platform" v-slot="{ id }">
                                <SelectInput
                                    :id="id"
                                    v-model="row.platform"
                                    size="sm"
                                    :options="platforms.map((value) => ({ value, label: value }))"
                                />
                            </FormField>
                            <FormField label="URL" :error="form.errors[`social_links.${index}.url`]" v-slot="{ id, invalid }">
                                <TextInput :id="id" v-model="row.url" size="sm" :invalid="invalid" placeholder="https://" />
                            </FormField>
                        </div>
                    </template>
                </Repeater>
            </FormSection>

            <FormSection id="meta" number="02" title="Publishing">
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
            </FormSection>

            <div class="flex flex-wrap items-center justify-end gap-3 pb-6">
                <Button variant="ghost" :href="route('admin.team.members.index')">Cancel</Button>
                <Button type="submit" icon="save" :loading="form.processing">
                    {{ isEdit ? 'Save changes' : 'Create member' }}
                </Button>
            </div>
        </form>

        <ConfirmDialog
            :open="deleting"
            title="Delete team member?"
            message="This removes the member from the public site."
            @cancel="deleting = false"
            @confirm="destroy"
        />
    </AdminLayout>
</template>
