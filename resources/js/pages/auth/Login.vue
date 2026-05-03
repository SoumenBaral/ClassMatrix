<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import { request } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Welcome back',
        description: 'Sign in to your ClassMatrix account',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

function submit() {
    form.post('/login', {
        onSuccess: () => form.reset('password'),
    });
}
</script>

<template>
    <Head title="Log in" />

    <div v-if="status" class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-center text-sm font-medium text-green-700 dark:bg-green-950/30 dark:text-green-400">
        {{ status }}
    </div>

    <form @submit.prevent="submit" class="flex flex-col gap-6">
        <div class="grid gap-5">
            <div class="grid gap-2">
                <Label for="email">Email Address</Label>
                <Input id="email" v-model="form.email" type="email" required autofocus autocomplete="email" placeholder="you@example.com" />
                <InputError :message="form.errors.email" />
            </div>

            <div class="grid gap-2">
                <div class="flex items-center justify-between">
                    <Label for="password">Password</Label>
                    <TextLink v-if="canResetPassword" :href="request()" class="text-xs">
                        Forgot password?
                    </TextLink>
                </div>
                <PasswordInput id="password" v-model="form.password" required autocomplete="current-password" placeholder="Enter your password" />
                <InputError :message="form.errors.password" />
            </div>

            <div class="flex items-center space-x-3">
                <Checkbox id="remember" v-model:checked="form.remember" />
                <Label for="remember" class="text-sm">Remember me</Label>
            </div>

            <Button type="submit" class="mt-1 w-full" :disabled="form.processing">
                <Spinner v-if="form.processing" />
                Sign In
            </Button>
        </div>

        <div class="text-center text-sm text-muted-foreground" v-if="canRegister">
            Don't have an account?
            <TextLink :href="register()">Create account</TextLink>
        </div>
    </form>
</template>
