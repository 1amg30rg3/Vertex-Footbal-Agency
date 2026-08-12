<script setup>
import { computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { useI18n } from '@/Composables/useI18n';
import { useRoute } from '@/Support/ziggy';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import PageHero from '@/Components/Site/PageHero.vue';
import FormField from '@/Components/Form/FormField.vue';
import TextInput from '@/Components/Form/TextInput.vue';
import TextArea from '@/Components/Form/TextArea.vue';
import Button from '@/Components/Ui/Button.vue';
import Alert from '@/Components/Ui/Alert.vue';
import Icon from '@/Components/Ui/Icon.vue';

const { t } = useI18n();
const route = useRoute();
const page = usePage();

const site = computed(() => page.props.site ?? {});

const form = useForm({
    name: '',
    email: '',
    subject: '',
    message: '',
    website: '',
});

const details = computed(() =>
    [
        site.value.address && {
            icon: 'location',
            label: t('contacts.address'),
            value: site.value.address,
            href: null,
        },
        site.value.phone && {
            icon: 'phone',
            label: t('contacts.phone'),
            value: site.value.phone,
            href: `tel:${site.value.phone}`,
        },
        site.value.email && {
            icon: 'mail',
            label: t('contacts.email'),
            value: site.value.email,
            href: `mailto:${site.value.email}`,
        },
    ].filter(Boolean),
);

const socialIcons = {
    instagram: 'instagram',
    facebook: 'facebook',
    linkedin: 'linkedin',
    twitter: 'twitter',
    x: 'twitter',
    youtube: 'youtube',
};

const socials = computed(() => site.value.socials ?? []);

function submit() {
    form.post(route('public.contacts.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}
</script>

<template>
    <Head :title="t('contacts.title')">
        <meta name="description" :content="t('contacts.lead')">
    </Head>

    <PublicLayout>
        <PageHero :title="t('contacts.title')" :lead="t('contacts.lead')" />

        <div class="mx-auto max-w-7xl px-4 py-16 pb-24 sm:px-6 lg:px-8 lg:py-24 lg:pb-32">
            <div class="grid gap-10 lg:grid-cols-[1fr_1.2fr] lg:gap-16">
                <div v-reveal class="space-y-8">
                    <ul class="space-y-5">
                        <li v-for="item in details" :key="item.label" class="flex items-start gap-4">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-accent-soft text-accent">
                                <Icon :name="item.icon" :size="17" />
                            </span>
                            <div class="min-w-0">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-fg-subtle">
                                    {{ item.label }}
                                </p>
                                <component
                                    :is="item.href ? 'a' : 'p'"
                                    :href="item.href || undefined"
                                    class="mt-0.5 block whitespace-pre-line text-sm text-fg transition-colors"
                                    :class="item.href ? 'hover:text-accent' : ''"
                                >{{ item.value }}</component>
                            </div>
                        </li>
                    </ul>

                    <div v-if="socials.length">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-fg-subtle">
                            {{ t('contacts.follow') }}
                        </p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <a
                                v-for="link in socials"
                                :key="link.url"
                                :href="link.url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-2 rounded-xl border border-border px-3.5 py-2 text-sm text-fg-muted transition-colors hover:border-accent hover:text-accent"
                            >
                                <Icon :name="socialIcons[link.platform] ?? 'link'" :size="15" />
                                <span class="capitalize">{{ link.platform }}</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div v-reveal="90" class="rounded-2xl border border-border bg-surface p-6 sm:p-8">
                    <h2 class="text-xl font-semibold tracking-tight text-fg">{{ t('contacts.form_title') }}</h2>
                    <p class="mt-1.5 text-sm text-fg-muted">{{ t('contacts.form_lead') }}</p>

                    <Alert v-if="form.recentlySuccessful" tone="success" class="mt-5">
                        {{ t('contacts.success') }}
                    </Alert>

                    <form class="mt-6 space-y-4" @submit.prevent="submit">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <FormField :label="t('contacts.name')" :error="form.errors.name" required v-slot="{ id, invalid }">
                                <TextInput :id="id" v-model="form.name" :invalid="invalid" autocomplete="name" />
                            </FormField>

                            <FormField :label="t('contacts.your_email')" :error="form.errors.email" required v-slot="{ id, invalid }">
                                <TextInput :id="id" v-model="form.email" type="email" :invalid="invalid" autocomplete="email" />
                            </FormField>
                        </div>

                        <FormField :label="t('contacts.subject')" :error="form.errors.subject" v-slot="{ id, invalid }">
                            <TextInput :id="id" v-model="form.subject" :invalid="invalid" />
                        </FormField>

                        <FormField :label="t('contacts.message')" :error="form.errors.message" required v-slot="{ id, invalid }">
                            <TextArea :id="id" v-model="form.message" :rows="6" :invalid="invalid" :maxlength="5000" />
                        </FormField>

                        <Button type="submit" size="lg" block :loading="form.processing" icon-right="arrowRight">
                            {{ form.processing ? t('contacts.sending') : t('contacts.send') }}
                        </Button>

                        <div class="sr-only" aria-hidden="true">
                            <label for="contact-website">Leave this field empty</label>
                            <input
                                id="contact-website"
                                v-model="form.website"
                                type="text"
                                tabindex="-1"
                                autocomplete="off"
                            >
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
