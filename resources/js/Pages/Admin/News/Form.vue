<script setup>
import { computed, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
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
import ToggleSwitch from '@/Components/Form/ToggleSwitch.vue';
import RichTextEditor from '@/Components/Form/RichTextEditor.vue';
import ImageUploader from '@/Components/Form/ImageUploader.vue';
import Button from '@/Components/Ui/Button.vue';
import Alert from '@/Components/Ui/Alert.vue';
import ConfirmDialog from '@/Components/Ui/ConfirmDialog.vue';

const props = defineProps({
    article: { type: Object, required: true },
    options: { type: Object, required: true },
});

const route = useRoute();

const isEdit = computed(() => !!props.article.id);
const deleting = ref(false);
const form = useForm(cloneForForm(props.article));

const needsDate = computed(() => form.status === 'scheduled');

function submit() {
    isEdit.value
        ? form.put(route('admin.news.update', { news: props.article.id }), { preserveScroll: true, onError: scrollToFirstError })
        : form.post(route('admin.news.store'), { preserveScroll: true, onError: scrollToFirstError });
}

function destroy() {
    form.delete(route('admin.news.destroy', { news: props.article.id }));
}
</script>

<template>
    <Head :title="isEdit ? 'Edit article' : 'New article'" />

    <AdminLayout
        :title="isEdit ? 'Edit article' : 'New article'"
        :subtitle="isEdit ? `/${article.slug}` : 'Write a new article'"
    >
        <template #actions>
            <Button v-if="isEdit" variant="ghost" size="sm" icon="trash" @click="deleting = true">Delete</Button>
            <Button size="sm" icon="save" :loading="form.processing" @click="submit">Save</Button>
        </template>

        <form class="grid gap-6 lg:grid-cols-[1fr_20rem]" @submit.prevent="submit">
            <div class="min-w-0 space-y-6">
                <Alert v-if="Object.keys(form.errors).length" tone="danger" title="Some fields need attention">
                    Check the highlighted language tabs and required fields below.
                </Alert>

                <FormSection id="content" number="01" title="Content">
                    <LanguageTabs v-model="form.title" label="Title" name="title" :errors="form.errors" required v-slot="{ locale }">
                        <TextInput v-model="form.title[locale]" />
                    </LanguageTabs>

                    <LanguageTabs
                        v-model="form.excerpt"
                        label="Excerpt"
                        hint="Shown in listings and used as the meta description fallback."
                        name="excerpt"
                        :errors="form.errors"
                        v-slot="{ locale }"
                    >
                        <TextArea v-model="form.excerpt[locale]" :rows="3" :maxlength="600" />
                    </LanguageTabs>

                    <LanguageTabs v-model="form.body" label="Body" name="body" :errors="form.errors" v-slot="{ locale }">
                        <RichTextEditor v-model="form.body[locale]" min-height="20rem" placeholder="Write the article…" />
                    </LanguageTabs>
                </FormSection>

                <FormSection id="seo" number="03" title="SEO">
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
                        <TextArea v-model="form.seo_description[locale]" :rows="2" :maxlength="500" />
                    </LanguageTabs>
                </FormSection>
            </div>

            <aside class="space-y-6">
                <FormSection id="publish" number="02" title="Publishing">
                    <ImageUploader
                        v-model="form.cover_path"
                        :preview-url="article.cover_url"
                        label="Cover image"
                        :aspect-ratio="16 / 10"
                        height="h-36"
                        :error="form.errors.cover_path"
                    />

                    <FormField label="Status" :error="form.errors.status" v-slot="{ id }">
                        <SelectInput
                            :id="id"
                            v-model="form.status"
                            :options="options.statuses.map((value) => ({ value, label: value }))"
                        />
                    </FormField>

                    <FormField
                        :label="needsDate ? 'Publish at' : 'Publish date'"
                        :hint="needsDate ? 'Goes live automatically at this time.' : 'Defaults to now when published.'"
                        :error="form.errors.published_at"
                        :required="needsDate"
                        v-slot="{ id, invalid }"
                    >
                        <TextInput :id="id" v-model="form.published_at" type="datetime-local" :invalid="invalid" />
                    </FormField>

                    <FormField label="Category" :error="form.errors.news_category_id" v-slot="{ id }">
                        <SelectInput :id="id" v-model="form.news_category_id" :options="options.categories" placeholder="Uncategorised" />
                    </FormField>

                    <hr class="border-border">

                    <ToggleSwitch
                        v-model="form.is_featured"
                        label="Show on homepage"
                        hint="Featured articles appear in the homepage strip."
                    />

                    <FormField
                        v-if="form.is_featured"
                        label="Homepage order"
                        hint="Lower numbers appear first."
                        :error="form.errors.featured_order"
                        v-slot="{ id }"
                    >
                        <TextInput :id="id" v-model="form.featured_order" type="number" min="0" />
                    </FormField>

                    <hr class="border-border">

                    <FormField label="Slug" hint="Auto-generated if empty." :error="form.errors.slug" v-slot="{ id, invalid }">
                        <TextInput :id="id" v-model="form.slug" :invalid="invalid" />
                    </FormField>

                    <dl v-if="isEdit" class="space-y-1 text-xs text-fg-subtle">
                        <div class="flex justify-between">
                            <dt>Views</dt>
                            <dd class="tabular-nums">{{ article.views }}</dd>
                        </div>
                        <div v-if="article.author" class="flex justify-between">
                            <dt>Author</dt>
                            <dd>{{ article.author }}</dd>
                        </div>
                    </dl>
                </FormSection>

                <div class="flex flex-col gap-2 pb-6">
                    <Button type="submit" block icon="save" :loading="form.processing">
                        {{ isEdit ? 'Save changes' : 'Create article' }}
                    </Button>
                    <Button variant="ghost" block :href="route('admin.news.index')">Cancel</Button>
                </div>
            </aside>
        </form>

        <ConfirmDialog
            :open="deleting"
            title="Delete article?"
            message="This removes the article from the public site."
            @cancel="deleting = false"
            @confirm="destroy"
        />
    </AdminLayout>
</template>
