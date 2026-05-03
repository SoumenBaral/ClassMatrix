<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { KeyRound, Plus, Search, ShieldCheck, UserCheck, UserX } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';

type Department = { id: number; name: string };
type UserRow = {
    id: number; name: string; email: string; phone: string | null;
    user_type: string; status: string; created_at: string; roles: string[];
};
type PaginatedData = { data: UserRow[]; links: { url: string | null; label: string; active: boolean }[] };

const props = defineProps<{
    users: PaginatedData;
    filters: { type?: string; search?: string; status?: string };
    isSuperAdmin: boolean;
    departments: Department[];
}>();

// Filters
const search = ref(props.filters.search ?? '');
const typeFilter = ref(props.filters.type ?? '');
const statusFilter = ref(props.filters.status ?? '');

function filter() {
    router.get('/admin/users', {
        search: search.value || undefined,
        type: typeFilter.value || undefined,
        status: statusFilter.value || undefined,
    }, { preserveState: true });
}

// Create user dialog
const showCreateDialog = ref(false);
const createForm = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    role: 'teacher',
    designation: '',
    department_id: null as number | null,
    qualification: '',
});

function openCreate(role: string) {
    createForm.reset();
    createForm.role = role;
    if (role === 'teacher') createForm.designation = 'Teacher';
    showCreateDialog.value = true;
}

function submitCreate() {
    createForm.post('/admin/users', {
        onSuccess: () => { showCreateDialog.value = false; createForm.reset(); },
    });
}

// Toggle status
function toggleStatus(user: UserRow) {
    const action = user.status === 'active' ? 'suspend' : 'activate';
    if (confirm(`${action.charAt(0).toUpperCase() + action.slice(1)} "${user.name}"?`)) {
        router.post(`/admin/users/${user.id}/toggle-status`);
    }
}

// Reset password
const showPasswordDialog = ref(false);
const passwordUserId = ref(0);
const passwordUserName = ref('');
const passwordForm = useForm({ password: '' });

function openResetPassword(user: UserRow) {
    passwordUserId.value = user.id;
    passwordUserName.value = user.name;
    passwordForm.reset();
    showPasswordDialog.value = true;
}

function submitResetPassword() {
    passwordForm.post(`/admin/users/${passwordUserId.value}/reset-password`, {
        onSuccess: () => { showPasswordDialog.value = false; },
    });
}

const typeColors: Record<string, string> = {
    admin: 'default',
    teacher: 'secondary',
    student: 'outline',
    parent: 'outline',
    staff: 'secondary',
};
const statusColors: Record<string, string> = {
    active: 'default',
    inactive: 'secondary',
    suspended: 'destructive',
};
</script>

<template>
    <Head title="User Management" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <Heading title="User Management" description="Create and manage admin & teacher accounts." />
            <div class="flex gap-2">
                <Button v-if="isSuperAdmin" variant="outline" @click="openCreate('admin')">
                    <ShieldCheck class="mr-2 size-4" /> Create Admin
                </Button>
                <Button @click="openCreate('teacher')">
                    <Plus class="mr-2 size-4" /> Create Teacher
                </Button>
            </div>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap items-end gap-4">
            <div class="relative">
                <Search class="absolute left-2.5 top-2.5 size-4 text-muted-foreground" />
                <Input v-model="search" @keyup.enter="filter" placeholder="Search name or email..." class="pl-9 w-56" />
            </div>
            <div>
                <select v-model="typeFilter" @change="filter"
                    class="flex h-9 w-36 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                    <option value="">All Types</option>
                    <option value="admin">Admin</option>
                    <option value="teacher">Teacher</option>
                    <option value="student">Student</option>
                    <option value="parent">Parent</option>
                </select>
            </div>
            <div>
                <select v-model="statusFilter" @change="filter"
                    class="flex h-9 w-32 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>
        </div>

        <!-- Users table -->
        <div class="rounded-lg border">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b bg-muted/50">
                        <th class="px-4 py-3 text-left font-medium">Name</th>
                        <th class="px-4 py-3 text-left font-medium">Email</th>
                        <th class="px-4 py-3 text-center font-medium">Type</th>
                        <th class="px-4 py-3 text-center font-medium">Status</th>
                        <th class="px-4 py-3 text-center font-medium">Joined</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in users.data" :key="user.id" class="border-b last:border-0 hover:bg-muted/30">
                        <td class="px-4 py-2">
                            <div class="font-medium">{{ user.name }}</div>
                            <div v-if="user.phone" class="text-xs text-muted-foreground">{{ user.phone }}</div>
                        </td>
                        <td class="px-4 py-2 text-muted-foreground">{{ user.email }}</td>
                        <td class="px-4 py-2 text-center">
                            <Badge :variant="(typeColors[user.user_type] as any)" class="capitalize">{{ user.user_type }}</Badge>
                        </td>
                        <td class="px-4 py-2 text-center">
                            <Badge :variant="(statusColors[user.status] as any)" class="capitalize">{{ user.status }}</Badge>
                        </td>
                        <td class="px-4 py-2 text-center text-muted-foreground">{{ user.created_at }}</td>
                        <td class="px-4 py-2 text-right">
                            <div class="flex justify-end gap-1">
                                <Button variant="ghost" size="sm" :title="user.status === 'active' ? 'Suspend' : 'Activate'" @click="toggleStatus(user)">
                                    <UserX v-if="user.status === 'active'" class="size-3.5 text-destructive" />
                                    <UserCheck v-else class="size-3.5 text-green-600" />
                                </Button>
                                <Button variant="ghost" size="sm" title="Reset Password" @click="openResetPassword(user)">
                                    <KeyRound class="size-3.5" />
                                </Button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="users.links.length > 3" class="flex justify-center gap-1">
            <template v-for="link in users.links" :key="link.label">
                <button v-if="link.url" @click="router.get(link.url)"
                    :class="['rounded-md px-3 py-1 text-sm', link.active ? 'bg-primary text-primary-foreground' : 'hover:bg-muted']"
                    v-html="link.label" />
            </template>
        </div>
    </div>

    <!-- Create User Dialog -->
    <Dialog v-model:open="showCreateDialog">
        <DialogContent class="max-w-md">
            <DialogHeader>
                <DialogTitle>Create {{ createForm.role === 'admin' ? 'Admin' : 'Teacher' }} Account</DialogTitle>
            </DialogHeader>
            <form @submit.prevent="submitCreate" class="space-y-4">
                <div>
                    <Label>Full Name</Label>
                    <Input v-model="createForm.name" placeholder="Enter full name" />
                    <InputError :message="createForm.errors.name" />
                </div>
                <div>
                    <Label>Email</Label>
                    <Input v-model="createForm.email" type="email" placeholder="email@example.com" />
                    <InputError :message="createForm.errors.email" />
                </div>
                <div>
                    <Label>Phone <span class="text-muted-foreground text-xs">(optional)</span></Label>
                    <Input v-model="createForm.phone" placeholder="+91 98765 43210" />
                </div>
                <div>
                    <Label>Password</Label>
                    <Input v-model="createForm.password" type="password" placeholder="Minimum 8 characters" />
                    <InputError :message="createForm.errors.password" />
                </div>

                <!-- Teacher-specific fields -->
                <template v-if="createForm.role === 'teacher'">
                    <div class="border-t pt-4">
                        <p class="text-xs font-medium text-muted-foreground mb-3">TEACHER DETAILS</p>
                        <div class="space-y-3">
                            <div>
                                <Label>Designation</Label>
                                <Input v-model="createForm.designation" placeholder="e.g. Senior Teacher, HOD" />
                                <InputError :message="createForm.errors.designation" />
                            </div>
                            <div>
                                <Label>Department</Label>
                                <select v-model="createForm.department_id"
                                    class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                                    <option :value="null">None</option>
                                    <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                                </select>
                            </div>
                            <div>
                                <Label>Qualification <span class="text-muted-foreground text-xs">(optional)</span></Label>
                                <Input v-model="createForm.qualification" placeholder="e.g. M.Sc, B.Ed" />
                            </div>
                        </div>
                    </div>
                </template>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="showCreateDialog = false">Cancel</Button>
                    <Button type="submit" :disabled="createForm.processing">
                        Create {{ createForm.role === 'admin' ? 'Admin' : 'Teacher' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Reset Password Dialog -->
    <Dialog v-model:open="showPasswordDialog">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Reset Password for {{ passwordUserName }}</DialogTitle>
            </DialogHeader>
            <form @submit.prevent="submitResetPassword" class="space-y-4">
                <div>
                    <Label>New Password</Label>
                    <Input v-model="passwordForm.password" type="password" placeholder="Minimum 8 characters" />
                    <InputError :message="passwordForm.errors.password" />
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="showPasswordDialog = false">Cancel</Button>
                    <Button type="submit" :disabled="passwordForm.processing">Reset Password</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
