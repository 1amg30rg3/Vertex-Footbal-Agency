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
import TextArea from '@/Components/Form/TextArea.vue';
import SelectInput from '@/Components/Form/SelectInput.vue';
import ImageUploader from '@/Components/Form/ImageUploader.vue';
import Repeater from '@/Components/Form/Repeater.vue';
import DataTable from '@/Components/Data/DataTable.vue';
import Modal from '@/Components/Ui/Modal.vue';
import Badge from '@/Components/Ui/Badge.vue';
import Button from '@/Components/Ui/Button.vue';
import Icon from '@/Components/Ui/Icon.vue';
import Alert from '@/Components/Ui/Alert.vue';
import ConfirmDialog from '@/Components/Ui/ConfirmDialog.vue';

const props = defineProps({
    settings: { type: Object, required: true },
    users: { type: Array, default: () => [] },
    roles: { type: Array, default: () => [] },
    canManageUsers: { type: Boolean, default: false },
});

const route = useRoute();
const page = usePage();

const key = () => Math.random().toString(36).slice(2);
const platforms = ['instagram', 'facebook', 'linkedin', 'twitter', 'youtube', 'link'];
const newSocial = () => ({ _key: key(), platform: 'instagram', url: '' });

const form = useForm(cloneForForm(props.settings));

const userModal = ref(false);
const editingUser = ref(null);
const deletingUser = ref(null);

const userForm = useForm({ name: '', email: '', password: '', password_confirmation: '', role: 'editor' });

const userColumns = [
    { key: 'name', label: 'Name' },
    { key: 'email', label: 'Email', muted: true },
    { key: 'role', label: 'Role', align: 'center' },
    { key: 'last_login_at', label: 'Last login', muted: true },
    { key: 'actions', label: '', align: 'right', width: 'w-24' },
];

function submit() {
    form.transform((data) => ({
        ...data,
        socials: (data.socials ?? []).map(({ _key, ...rest }) => rest),
    }));

    form.put(route('admin.settings.update'), { preserveScroll: true, onError: scrollToFirstError });
}

function openCreateUser() {
    editingUser.value = null;
    userForm.defaults({ name: '', email: '', password: '', password_confirmation: '', role: 'editor' });
    userForm.reset();
    userForm.clearErrors();
    userModal.value = true;
}

function openEditUser(user) {
    editingUser.value = user;
    userForm.defaults({
        name: user.name,
        email: user.email,
        password: '',
        password_confirmation: '',
        role: user.role,
    });
    userForm.reset();
    userForm.clearErrors();
    userModal.value = true;
}

function submitUser() {
    const options = { preserveScroll: true, onError: scrollToFirstError, onSuccess: () => (userModal.value = false) };

    editingUser.value
        ? userForm.put(route('admin.settings.users.update', { user: editingUser.value.id }), options)
        : userForm.post(route('admin.settings.users.store'), options);
}

function confirmDeleteUser() {
    userForm.delete(route('admin.settings.users.destroy', { user: deletingUser.value.id }), {
        preserveScroll: true,
        onFinish: () => (deletingUser.value = null),
    });
}
</script>

<template>
    <Head title="Settings" />

    <AdminLayout title="Settings" subtitle="Branding, contact details and accounts.">
        <template #actions>
            <Button size="sm" icon="save" :loading="form.processing" @click="submit">Save</Button>
        </template>

        <div class="mx-auto max-w-4xl space-y-6">
            <form class="space-y-6" @submit.prevent="submit">
                <Alert v-if="Object.keys(form.errors).length" tone="danger" title="Some fields need attention">
                    Check the highlighted fields below.
                </Alert>

                <FormSection id="branding" number="01" title="Branding">
                    <LanguageTabs v-model="form.site_name" label="Site name" name="site_name" :errors="form.errors" v-slot="{ locale }">
                        <TextInput v-model="form.site_name[locale]" />
                    </LanguageTabs>

                    <LanguageTabs
                        v-model="form.site_tagline"
                        label="Tagline"
                        name="site_tagline"
                        :errors="form.errors"
                        v-slot="{ locale }"
                    >
                        <TextInput v-model="form.site_tagline[locale]" placeholder="Confidence. Discipline. Passion. Success." />
                    </LanguageTabs>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <ImageUploader
                            v-model="form.logo_path"
                            :preview-url="settings.logo_url"
                            label="Logo"
                            hint="Shown on the dark header."
                            height="h-24"
                            :croppable="false"
                        />
                        <ImageUploader
                            v-model="form.logo_light_path"
                            :preview-url="settings.logo_light_url"
                            label="Logo (light theme)"
                            hint="Optional — falls back to the main logo."
                            height="h-24"
                            :croppable="false"
                        />
                        <ImageUploader
                            v-model="form.share_image_path"
                            :preview-url="settings.share_image_url"
                            label="Social share image"
                            hint="Shown when a link is shared on Facebook, LinkedIn, WhatsApp or X. Use 1200×630. Falls back to the logo, which crops badly."
                            height="h-32"
                            :croppable="false"
                        />
                    </div>

                    <LanguageTabs v-model="form.copyright" label="Copyright line" name="copyright" :errors="form.errors" v-slot="{ locale }">
                        <TextInput v-model="form.copyright[locale]" />
                    </LanguageTabs>
                </FormSection>

                <FormSection id="contact" number="02" title="Contact details" description="Used in the site footer.">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <FormField label="Email" :error="form.errors.contact_email" v-slot="{ id, invalid }">
                            <TextInput :id="id" v-model="form.contact_email" type="email" icon="mail" :invalid="invalid" />
                        </FormField>
                        <FormField label="Phone" :error="form.errors.contact_phone" v-slot="{ id, invalid }">
                            <TextInput :id="id" v-model="form.contact_phone" icon="phone" :invalid="invalid" />
                        </FormField>
                    </div>

                    <LanguageTabs
                        v-model="form.contact_address"
                        label="Address"
                        name="contact_address"
                        :errors="form.errors"
                        v-slot="{ locale }"
                    >
                        <TextArea v-model="form.contact_address[locale]" :rows="2" />
                    </LanguageTabs>

                    <Repeater
                        v-model="form.socials"
                        label="Social links"
                        add-label="Add link"
                        empty-label="No social links yet."
                        :new-row="newSocial"
                        :boxed="false"
                        :max="12"
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
                                <FormField label="URL" :error="form.errors[`socials.${index}.url`]" v-slot="{ id, invalid }">
                                    <TextInput :id="id" v-model="row.url" size="sm" :invalid="invalid" placeholder="https://" />
                                </FormField>
                            </div>
                        </template>
                    </Repeater>
                </FormSection>

                <FormSection id="content" number="03" title="Content defaults">
                    <FormField
                        label="Featured news on the homepage"
                        hint="Default count; the home page editor can override it."
                        :error="form.errors.featured_news_limit"
                        v-slot="{ id, invalid }"
                    >
                        <TextInput :id="id" v-model="form.featured_news_limit" type="number" min="1" max="12" :invalid="invalid" />
                    </FormField>
                </FormSection>

                <div class="flex justify-end">
                    <Button type="submit" icon="save" :loading="form.processing">Save settings</Button>
                </div>
            </form>

            <FormSection id="appearance" number="04" title="Account">
                <p class="text-xs text-fg-subtle">
                    Signed in as <span class="text-fg">{{ page.props.auth?.user?.email }}</span>.
                </p>
            </FormSection>

            <FormSection
                v-if="canManageUsers"
                id="accounts"
                number="05"
                title="Accounts"
                description="Admins manage everything; editors manage content only."
            >
                <template #actions>
                    <Button size="sm" variant="outline" icon="plus" @click="openCreateUser">New account</Button>
                </template>

                <DataTable :columns="userColumns" :rows="users" empty-title="No accounts">
                    <template #cell-name="{ row }">
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-fg">{{ row.name }}</span>
                            <Badge v-if="row.is_self" tone="accent" size="sm">You</Badge>
                        </div>
                    </template>

                    <template #cell-role="{ row }">
                        <Badge :tone="row.role === 'admin' ? 'info' : 'neutral'" size="sm">{{ row.role }}</Badge>
                    </template>

                    <template #cell-actions="{ row }">
                        <div class="flex items-center justify-end gap-1">
                            <button
                                type="button"
                                class="rounded-lg p-1.5 text-fg-subtle transition-colors hover:bg-surface-2 hover:text-fg"
                                @click="openEditUser(row)"
                            >
                                <Icon name="edit" :size="15" />
                            </button>
                            <button
                                v-if="!row.is_self"
                                type="button"
                                class="rounded-lg p-1.5 text-fg-subtle transition-colors hover:bg-danger-soft hover:text-danger"
                                @click="deletingUser = row"
                            >
                                <Icon name="trash" :size="15" />
                            </button>
                        </div>
                    </template>
                </DataTable>
            </FormSection>
        </div>

        <Modal :open="userModal" :title="editingUser ? 'Edit account' : 'New account'" @close="userModal = false">
            <div class="space-y-4">
                <FormField label="Name" :error="userForm.errors.name" required v-slot="{ id, invalid }">
                    <TextInput :id="id" v-model="userForm.name" :invalid="invalid" />
                </FormField>

                <FormField label="Email" :error="userForm.errors.email" required v-slot="{ id, invalid }">
                    <TextInput :id="id" v-model="userForm.email" type="email" icon="mail" :invalid="invalid" />
                </FormField>

                <FormField label="Role" :error="userForm.errors.role" required v-slot="{ id }">
                    <SelectInput :id="id" v-model="userForm.role" :options="roles.map((value) => ({ value, label: value }))" />
                </FormField>

                <FormField
                    label="Password"
                    :hint="editingUser ? 'Leave empty to keep the current password.' : null"
                    :error="userForm.errors.password"
                    :required="!editingUser"
                    v-slot="{ id, invalid }"
                >
                    <TextInput :id="id" v-model="userForm.password" type="password" :invalid="invalid" autocomplete="new-password" />
                </FormField>

                <FormField label="Confirm password" v-slot="{ id }">
                    <TextInput :id="id" v-model="userForm.password_confirmation" type="password" autocomplete="new-password" />
                </FormField>
            </div>

            <template #footer>
                <Button variant="ghost" @click="userModal = false">Cancel</Button>
                <Button :loading="userForm.processing" @click="submitUser">
                    {{ editingUser ? 'Save' : 'Create' }}
                </Button>
            </template>
        </Modal>

        <ConfirmDialog
            :open="!!deletingUser"
            title="Delete account?"
            :message="`${deletingUser?.name} will lose access to the admin panel immediately.`"
            @cancel="deletingUser = null"
            @confirm="confirmDeleteUser"
        />
    </AdminLayout>
</template>
