<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Pencil, Plus, Search, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';

type Department = { id: number; name: string };
type StaffMember = {
    id: number; name: string; email: string; phone: string | null;
    employee_no: string; designation: string; department: string | null;
    department_id: number | null; qualification: string | null;
    joining_date: string | null; experience_years: number;
    gender: string | null; status: string; user_status: string; user_id: number;
};
type PaginatedData = { data: StaffMember[]; links: { url: string | null; label: string; active: boolean }[] };

const props = defineProps<{
    staff: PaginatedData;
    departments: Department[];
    filters: { search?: string; department_id?: string };
}>();

const search = ref(props.filters.search ?? '');
const deptFilter = ref(props.filters.department_id ?? '');

function filter() {
    router.get('/admin/staff', {
        search: search.value || undefined,
        department_id: deptFilter.value || undefined,
    }, { preserveState: true });
}

// Create
const showCreate = ref(false);
const createForm = useForm({
    name: '', email: '', phone: '', password: '',
    designation: 'Teacher', department_id: null as number | null,
    qualification: '', gender: '', experience_years: 0,
});

function submitCreate() {
    createForm.post('/admin/staff', { onSuccess: () => { showCreate.value = false; createForm.reset(); } });
}

// Edit
const showEdit = ref(false);
const editingId = ref(0);
const editForm = useForm({
    name: '', email: '', phone: '', designation: '',
    department_id: null as number | null, qualification: '',
    gender: '', experience_years: 0, status: 'active',
});

function openEdit(s: StaffMember) {
    editingId.value = s.id;
    editForm.name = s.name; editForm.email = s.email; editForm.phone = s.phone ?? '';
    editForm.designation = s.designation; editForm.department_id = s.department_id;
    editForm.qualification = s.qualification ?? ''; editForm.gender = s.gender ?? '';
    editForm.experience_years = s.experience_years; editForm.status = s.status;
    showEdit.value = true;
}

function submitEdit() {
    editForm.put(`/admin/staff/${editingId.value}`, { onSuccess: () => { showEdit.value = false; } });
}

function destroy(s: StaffMember) {
    if (confirm(`Delete "${s.name}"? This will also delete their user account.`)) {
        router.delete(`/admin/staff/${s.id}`);
    }
}
</script>

<template>
    <Head title="Teachers & Staff" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <Heading title="Teachers & Staff" description="Manage all teacher and staff accounts." />
            <Button @click="showCreate = true"><Plus class="mr-2 size-4" /> Add Teacher</Button>
        </div>

        <div class="flex flex-wrap items-end gap-4">
            <div class="relative">
                <Search class="absolute left-2.5 top-2.5 size-4 text-muted-foreground" />
                <Input v-model="search" @keyup.enter="filter" placeholder="Name, email, employee no..." class="pl-9 w-56" />
            </div>
            <select v-model="deptFilter" @change="filter" class="flex h-9 w-48 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                <option value="">All Departments</option>
                <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
            </select>
        </div>

        <div class="rounded-lg border">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b bg-muted/50">
                        <th class="px-4 py-3 text-left font-medium">Name</th>
                        <th class="px-4 py-3 text-left font-medium">Employee #</th>
                        <th class="px-4 py-3 text-left font-medium">Designation</th>
                        <th class="px-4 py-3 text-left font-medium">Department</th>
                        <th class="px-4 py-3 text-left font-medium">Qualification</th>
                        <th class="px-4 py-3 text-center font-medium">Status</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="s in staff.data" :key="s.id" class="border-b last:border-0 hover:bg-muted/30">
                        <td class="px-4 py-2">
                            <div class="font-medium">{{ s.name }}</div>
                            <div class="text-xs text-muted-foreground">{{ s.email }}</div>
                        </td>
                        <td class="px-4 py-2"><code class="text-xs">{{ s.employee_no }}</code></td>
                        <td class="px-4 py-2">{{ s.designation }}</td>
                        <td class="px-4 py-2 text-muted-foreground">{{ s.department ?? '-' }}</td>
                        <td class="px-4 py-2 text-muted-foreground">{{ s.qualification ?? '-' }}</td>
                        <td class="px-4 py-2 text-center"><Badge :variant="s.status === 'active' ? 'default' : 'destructive'" class="capitalize">{{ s.status }}</Badge></td>
                        <td class="px-4 py-2 text-right">
                            <div class="flex justify-end gap-1">
                                <Button variant="ghost" size="sm" @click="openEdit(s)"><Pencil class="size-3" /></Button>
                                <Button variant="ghost" size="sm" class="text-destructive" @click="destroy(s)"><Trash2 class="size-3" /></Button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="staff.links.length > 3" class="flex justify-center gap-1">
            <template v-for="link in staff.links" :key="link.label">
                <button v-if="link.url" @click="router.get(link.url)" :class="['rounded-md px-3 py-1 text-sm', link.active ? 'bg-primary text-primary-foreground' : 'hover:bg-muted']" v-html="link.label" />
            </template>
        </div>
    </div>

    <!-- Create -->
    <Dialog v-model:open="showCreate">
        <DialogContent class="max-w-md"><DialogHeader><DialogTitle>Add Teacher / Staff</DialogTitle></DialogHeader>
            <form @submit.prevent="submitCreate" class="space-y-4">
                <div><Label>Full Name</Label><Input v-model="createForm.name" required /><InputError :message="createForm.errors.name" /></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><Label>Email</Label><Input v-model="createForm.email" type="email" required /><InputError :message="createForm.errors.email" /></div>
                    <div><Label>Phone</Label><Input v-model="createForm.phone" /></div>
                </div>
                <div><Label>Password</Label><Input v-model="createForm.password" type="password" required /><InputError :message="createForm.errors.password" /></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><Label>Designation</Label><Input v-model="createForm.designation" required /><InputError :message="createForm.errors.designation" /></div>
                    <div><Label>Department</Label>
                        <select v-model="createForm.department_id" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                            <option :value="null">None</option>
                            <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div><Label>Qualification</Label><Input v-model="createForm.qualification" /></div>
                    <div><Label>Gender</Label>
                        <select v-model="createForm.gender" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                            <option value="">-</option><option value="male">Male</option><option value="female">Female</option>
                        </select>
                    </div>
                    <div><Label>Experience (yrs)</Label><Input v-model.number="createForm.experience_years" type="number" min="0" /></div>
                </div>
                <DialogFooter><Button type="button" variant="outline" @click="showCreate = false">Cancel</Button><Button type="submit" :disabled="createForm.processing">Create</Button></DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Edit -->
    <Dialog v-model:open="showEdit">
        <DialogContent class="max-w-md"><DialogHeader><DialogTitle>Edit Staff</DialogTitle></DialogHeader>
            <form @submit.prevent="submitEdit" class="space-y-4">
                <div><Label>Full Name</Label><Input v-model="editForm.name" required /></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><Label>Email</Label><Input v-model="editForm.email" type="email" required /></div>
                    <div><Label>Phone</Label><Input v-model="editForm.phone" /></div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><Label>Designation</Label><Input v-model="editForm.designation" required /></div>
                    <div><Label>Department</Label>
                        <select v-model="editForm.department_id" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                            <option :value="null">None</option>
                            <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div><Label>Qualification</Label><Input v-model="editForm.qualification" /></div>
                    <div><Label>Experience</Label><Input v-model.number="editForm.experience_years" type="number" min="0" /></div>
                    <div><Label>Status</Label>
                        <select v-model="editForm.status" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                            <option value="active">Active</option><option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <DialogFooter><Button type="button" variant="outline" @click="showEdit = false">Cancel</Button><Button type="submit" :disabled="editForm.processing">Update</Button></DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
