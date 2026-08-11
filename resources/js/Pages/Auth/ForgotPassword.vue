<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useRoute } from '@/Support/ziggy';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import FormField from '@/Components/Form/FormField.vue';
import TextInput from '@/Components/Form/TextInput.vue';
import Button from '@/Components/Ui/Button.vue';
import Alert from '@/Components/Ui/Alert.vue';

defineProps({ status: { type: String, default: null } });

const route = useRoute();
const form = useForm({ email: '' });
</script>

<template>
    <Head title="Reset password" />

    <AuthLayout title="Forgot your password?" subtitle="We'll email you a reset link.">
        <Alert v-if="status" tone="success" class="mb-5">{{ status }}</Alert>

        <form class="space-y-4" @submit.prevent="form.post(route('admin.password.email'))">
            <FormField label="Email address" :error="form.errors.email" v-slot="{ id, invalid }">
                <TextInput :id="id" v-model="form.email" type="email" icon="mail" :invalid="invalid" autocomplete="username" />
            </FormField>

            <Button type="submit" block :loading="form.processing">Send reset link</Button>

            <p class="text-center text-xs text-fg-muted">
                <Link :href="route('admin.login')" class="font-medium text-accent hover:text-accent-hover">
                    Back to sign in
                </Link>
            </p>
        </form>
    </AuthLayout>
</template>
