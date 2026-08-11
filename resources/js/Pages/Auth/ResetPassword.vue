<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { useRoute } from '@/Support/ziggy';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import FormField from '@/Components/Form/FormField.vue';
import TextInput from '@/Components/Form/TextInput.vue';
import Button from '@/Components/Ui/Button.vue';

const props = defineProps({
    email: { type: String, default: '' },
    token: { type: String, required: true },
});

const route = useRoute();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post(route('admin.password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <Head title="Choose a new password" />

    <AuthLayout title="Choose a new password">
        <form class="space-y-4" @submit.prevent="submit">
            <FormField label="Email address" :error="form.errors.email" v-slot="{ id, invalid }">
                <TextInput :id="id" v-model="form.email" type="email" icon="mail" :invalid="invalid" autocomplete="username" />
            </FormField>

            <FormField label="New password" :error="form.errors.password" v-slot="{ id, invalid }">
                <TextInput :id="id" v-model="form.password" type="password" :invalid="invalid" autocomplete="new-password" />
            </FormField>

            <FormField label="Confirm password" :error="form.errors.password_confirmation" v-slot="{ id, invalid }">
                <TextInput :id="id" v-model="form.password_confirmation" type="password" :invalid="invalid" autocomplete="new-password" />
            </FormField>

            <Button type="submit" block :loading="form.processing">Reset password</Button>
        </form>
    </AuthLayout>
</template>
