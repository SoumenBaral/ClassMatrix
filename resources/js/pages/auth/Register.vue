<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { GraduationCap, Heart, ChevronLeft } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';

defineOptions({
    layout: {
        title: 'Create your account',
        description: 'Choose your role and get started with ClassMatrix',
    },
});

type Role = {
    value: string;
    label: string;
    description: string;
    icon: any;
    color: string;
    bgColor: string;
};

const roles: Role[] = [
    {
        value: 'student',
        label: 'Student',
        description: 'View results, AI study routines, chat with AI teacher',
        icon: GraduationCap,
        color: 'text-blue-600 dark:text-blue-400',
        bgColor: 'bg-blue-50 border-blue-200 hover:border-blue-400 dark:bg-blue-950/30 dark:border-blue-800 dark:hover:border-blue-600',
    },
    {
        value: 'parent',
        label: 'Parent / Guardian',
        description: 'Track your child\'s attendance, results & school activities',
        icon: Heart,
        color: 'text-amber-600 dark:text-amber-400',
        bgColor: 'bg-amber-50 border-amber-200 hover:border-amber-400 dark:bg-amber-950/30 dark:border-amber-800 dark:hover:border-amber-600',
    },
];

const step = ref<'role' | 'details'>('role');
const selectedRole = ref<Role | null>(null);

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    role: '',
    // Parent-specific
    relation: 'father',
    child_admission_no: '',
});

function selectRole(role: Role) {
    selectedRole.value = role;
    form.role = role.value;
    step.value = 'details';
}

function goBack() {
    step.value = 'role';
}

function submit() {
    form.post('/register', {
        onSuccess: () => {
            form.reset('password', 'password_confirmation');
        },
    });
}

const isParent = computed(() => selectedRole.value?.value === 'parent');
const selectedColor = computed(() => selectedRole.value?.color ?? '');

const relations = [
    { value: 'father', label: 'Father' },
    { value: 'mother', label: 'Mother' },
    { value: 'guardian', label: 'Guardian' },
    { value: 'other', label: 'Other Relative' },
];
</script>

<template>
    <Head title="Register" />

    <!-- Step 1: Role Selection -->
    <div v-if="step === 'role'" class="flex flex-col gap-6">
        <div class="grid gap-3">
            <button
                v-for="role in roles"
                :key="role.value"
                @click="selectRole(role)"
                :class="[
                    'flex items-start gap-4 rounded-xl border-2 p-4 text-left transition-all duration-200',
                    role.bgColor,
                ]"
            >
                <div :class="['mt-0.5 rounded-lg p-2', role.color]">
                    <component :is="role.icon" class="size-5" />
                </div>
                <div class="flex-1">
                    <div class="font-semibold text-sm">{{ role.label }}</div>
                    <div class="text-xs text-muted-foreground mt-0.5">{{ role.description }}</div>
                </div>
                <div class="mt-1 text-muted-foreground">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </button>
        </div>

        <div class="text-center text-sm text-muted-foreground">
            Already have an account?
            <TextLink :href="login()" class="underline underline-offset-4">Log in</TextLink>
        </div>
    </div>

    <!-- Step 2: Details Form -->
    <form v-else @submit.prevent="submit" class="flex flex-col gap-5">
        <!-- Back + Role badge -->
        <div class="flex items-center gap-3">
            <button type="button" @click="goBack" class="rounded-lg p-1.5 hover:bg-muted transition-colors">
                <ChevronLeft class="size-4" />
            </button>
            <div :class="['flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-medium', selectedRole?.bgColor]">
                <component :is="selectedRole?.icon" :class="['size-3', selectedColor]" />
                <span>Registering as {{ selectedRole?.label }}</span>
            </div>
        </div>

        <div class="grid gap-4">
            <div class="grid gap-2">
                <Label for="name">Full Name</Label>
                <Input id="name" v-model="form.name" type="text" required autofocus autocomplete="name" placeholder="Enter your full name" />
                <InputError :message="form.errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="email">Email Address</Label>
                <Input id="email" v-model="form.email" type="email" required autocomplete="email" placeholder="you@example.com" />
                <InputError :message="form.errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="phone">Phone Number</Label>
                <Input id="phone" v-model="form.phone" type="tel" autocomplete="tel" placeholder="+91 98765 43210" />
                <InputError :message="form.errors.phone" />
            </div>

            <!-- Parent-specific: link to child -->
            <template v-if="isParent">
                <div class="rounded-lg border border-amber-200 bg-amber-50/50 p-4 dark:border-amber-800 dark:bg-amber-950/20">
                    <p class="text-xs font-medium text-amber-800 dark:text-amber-300 mb-3">LINK TO YOUR CHILD</p>
                    <div class="grid gap-3">
                        <div class="grid gap-2">
                            <Label for="child_admission_no">Child's Admission Number <span class="text-red-500">*</span></Label>
                            <Input id="child_admission_no" v-model="form.child_admission_no"
                                required
                                placeholder="e.g. ADM-000001"
                                class="bg-white dark:bg-background" />
                            <p class="text-xs text-muted-foreground">Ask your child or school for this number. Registration requires a valid admission number.</p>
                            <InputError :message="form.errors.child_admission_no" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="relation">Your Relation</Label>
                            <select id="relation" v-model="form.relation"
                                class="flex h-9 w-full rounded-md border border-input bg-white dark:bg-background px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                                <option v-for="r in relations" :key="r.value" :value="r.value">{{ r.label }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </template>

            <div class="grid gap-2">
                <Label for="password">Password</Label>
                <PasswordInput id="password" v-model="form.password" required autocomplete="new-password" placeholder="Create a strong password" />
                <InputError :message="form.errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation">Confirm Password</Label>
                <PasswordInput id="password_confirmation" v-model="form.password_confirmation" required autocomplete="new-password" placeholder="Confirm your password" />
                <InputError :message="form.errors.password_confirmation" />
            </div>

            <InputError :message="form.errors.role" />

            <Button type="submit" class="mt-1 w-full" :disabled="form.processing">
                <Spinner v-if="form.processing" />
                Create {{ selectedRole?.label }} Account
            </Button>
        </div>

        <div class="text-center text-sm text-muted-foreground">
            Already have an account?
            <TextLink :href="login()" class="underline underline-offset-4">Log in</TextLink>
        </div>
    </form>
</template>
