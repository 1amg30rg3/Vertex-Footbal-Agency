<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useRoute } from '@/Support/ziggy';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import FormField from '@/Components/Form/FormField.vue';
import TextInput from '@/Components/Form/TextInput.vue';
import CheckboxInput from '@/Components/Form/CheckboxInput.vue';
import Button from '@/Components/Ui/Button.vue';
import Alert from '@/Components/Ui/Alert.vue';

defineProps({
    status: { type: String, default: null },
    canResetPassword: { type: Boolean, default: true },
});

const route = useRoute();

const form = useForm({ email: '', password: '', remember: false });

function submit() {
    form.post(route('admin.login.store'), {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <Head title="Sign in" />

    <AuthLayout title="Sign in" subtitle="Manage players, trainers and news.">
        <Alert v-if="status" tone="success" class="mb-5">{{ status }}</Alert>

        <form class="space-y-4" @submit.prevent="submit">
            <FormField label="Email address" :error="form.errors.email" v-slot="{ id, invalid }">
                <TextInput
                    :id="id"
                    v-model="form.email"
                    type="email"
                    autocomplete="username"
                    icon="mail"
                    :invalid="invalid"
                    placeholder="you@agency.com"
                />
            </FormField>

            <FormField label="Password" :error="form.errors.password" v-slot="{ id, invalid }">
                <TextInput
                    :id="id"
                    v-model="form.password"
                    type="password"
                    autocomplete="current-password"
                    :invalid="invalid"
                    placeholder="••••••••"
                />
            </FormField>

            <div class="flex items-center justify-between gap-3">
                <CheckboxInput v-model="form.remember" label="Remember me" />

                <Link
                    v-if="canResetPassword"
                    :href="route('admin.password.request')"
                    class="text-xs font-medium text-accent transition-colors hover:text-accent-hover"
                >Forgot password?</Link>
            </div>

            <Button type="submit" block size="lg" :loading="form.processing" icon-right="arrowRight">
                Sign in
            </Button>
        </form>
    </AuthLayout>
</template>
