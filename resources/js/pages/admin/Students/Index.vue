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

type Section = { id: number; name: string };
type Student = {
    id: number; name: string; email: string; phone: string | null;
    admission_no: string; roll_no: string | null; class_section: string;
    section_id: number | null; gender: string | null; dob: string | null;
    status: string; user_status: string; user_id: number;
};
type PaginatedData = { data: Student[]; links: { url: string | null; label: string; active: boolean }[] };

const props = defineProps<{
    students: PaginatedData;
    sections: Section[];
    filters: { search?: string; section_id?: string; status?: string };
}>();

const search = ref(props.filters.search ?? '');
const sectionFilter = ref(props.filters.section_id ?? '');
const statusFilter = ref(props.filters.status ?? '');

function filter() {
    router.get('/admin/students', {
        search: search.value || undefined,
        section_id: sectionFilter.value || undefined,
        status: statusFilter.value || undefined,
    }, { preserveState: true });
}

// Create
const showCreate = ref(false);
const createForm = useForm({
    name: '', email: '', phone: '', password: '',
    roll_no: '', dob: '', gender: '', section_id: null as number | null,
    blood_group: '', address: '',
});

function submitCreate() {
    createForm.post('/admin/students', { onSuccess: () => { showCreate.value = false; createForm.reset(); } });
}

// Edit
const showEdit = ref(false);
const editingId = ref(0);
const editForm = useForm({
    name: '', email: '', phone: '', roll_no: '', dob: '', gender: '',
    section_id: null as number | null, blood_group: '', status: 'active',
});

function openEdit(s: Student) {
    editingId.value = s.id;
    editForm.name = s.name; editForm.email = s.email; editForm.phone = s.phone ?? '';
    editForm.roll_no = s.roll_no ?? ''; editForm.dob = s.dob ?? '';
    editForm.gender = s.gender ?? ''; editForm.section_id = s.section_id;
    editForm.status = s.status;
    showEdit.value = true;
}

function submitEdit() {
    editForm.put(`/admin/students/${editingId.value}`, { onSuccess: () => { showEdit.value = false; } });
}

function destroy(s: Student) {
    if (confirm(`Delete student "${s.name}"? This will also delete their user account.`)) {
        router.delete(`/admin/students/${s.id}`);
    }
}

const statusColors: any = { active: 'default', alumni: 'secondary', withdrawn: 'destructive' };
</script>

<template>
    <Head title="Students" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <Heading title="Students" description="Manage all student accounts and profiles." />
            <Button @click="showCreate = true"><Plus class="mr-2 size-4" /> Add Student</Button>
        </div>

        <div class="flex flex-wrap items-end gap-4">
            <div class="relative">
                <Search class="absolute left-2.5 top-2.5 size-4 text-muted-foreground" />
                <Input v-model="search" @keyup.enter="filter" placeholder="Name, email, admission no..." class="pl-9 w-56" />
            </div>
            <select v-model="sectionFilter" @change="filter" class="flex h-9 w-48 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                <option value="">All Sections</option>
                <option v-for="s in sections" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
            <select v-model="statusFilter" @change="filter" class="flex h-9 w-32 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="alumni">Alumni</option>
                <option value="withdrawn">Withdrawn</option>
            </select>
        </div>

        <div class="rounded-lg border">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b bg-muted/50">
                        <th class="px-4 py-3 text-left font-medium">Student</th>
                        <th class="px-4 py-3 text-left font-medium">Admission #</th>
                        <th class="px-4 py-3 text-left font-medium">Class</th>
                        <th class="px-4 py-3 text-left font-medium">Roll</th>
                        <th class="px-4 py-3 text-center font-medium">Status</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="s in students.data" :key="s.id" class="border-b last:border-0 hover:bg-muted/30">
                        <td class="px-4 py-2">
                            <div class="font-medium">{{ s.name }}</div>
                            <div class="text-xs text-muted-foreground">{{ s.email }}</div>
                        </td>
                        <td class="px-4 py-2"><code class="text-xs">{{ s.admission_no }}</code></td>
                        <td class="px-4 py-2 text-muted-foreground">{{ s.class_section }}</td>
                        <td class="px-4 py-2">{{ s.roll_no ?? '-' }}</td>
                        <td class="px-4 py-2 text-center"><Badge :variant="statusColors[s.status]" class="capitalize">{{ s.status }}</Badge></td>
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

        <div v-if="students.links.length > 3" class="flex justify-center gap-1">
            <template v-for="link in students.links" :key="link.label">
                <button v-if="link.url" @click="router.get(link.url)" :class="['rounded-md px-3 py-1 text-sm', link.active ? 'bg-primary text-primary-foreground' : 'hover:bg-muted']" v-html="link.label" />
            </template>
        </div>
    </div>

    <!-- Create Dialog -->
    <Dialog v-model:open="showCreate">
        <DialogContent class="max-w-md"><DialogHeader><DialogTitle>Add Student</DialogTitle></DialogHeader>
            <form @submit.prevent="submitCreate" class="space-y-4">
                <div><Label>Full Name</Label><Input v-model="createForm.name" required /><InputError :message="createForm.errors.name" /></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><Label>Email</Label><Input v-model="createForm.email" type="email" required /><InputError :message="createForm.errors.email" /></div>
                    <div><Label>Phone</Label><Input v-model="createForm.phone" /></div>
                </div>
                <div><Label>Password</Label><Input v-model="createForm.password" type="password" required /><InputError :message="createForm.errors.password" /></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><Label>Section</Label>
                        <select v-model="createForm.section_id" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                            <option :value="null">Not assigned</option>
                            <option v-for="s in sections" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                    </div>
                    <div><Label>Roll No</Label><Input v-model="createForm.roll_no" /></div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div><Label>DOB</Label><Input v-model="createForm.dob" type="date" /></div>
                    <div><Label>Gender</Label>
                        <select v-model="createForm.gender" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                            <option value="">-</option><option value="male">Male</option><option value="female">Female</option><option value="other">Other</option>
                        </select>
                    </div>
                    <div><Label>Blood</Label><Input v-model="createForm.blood_group" placeholder="A+" /></div>
                </div>
                <DialogFooter><Button type="button" variant="outline" @click="showCreate = false">Cancel</Button><Button type="submit" :disabled="createForm.processing">Create</Button></DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Edit Dialog -->
    <Dialog v-model:open="showEdit">
        <DialogContent class="max-w-md"><DialogHeader><DialogTitle>Edit Student</DialogTitle></DialogHeader>
            <form @submit.prevent="submitEdit" class="space-y-4">
                <div><Label>Full Name</Label><Input v-model="editForm.name" required /><InputError :message="editForm.errors.name" /></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><Label>Email</Label><Input v-model="editForm.email" type="email" required /><InputError :message="editForm.errors.email" /></div>
                    <div><Label>Phone</Label><Input v-model="editForm.phone" /></div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><Label>Section</Label>
                        <select v-model="editForm.section_id" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                            <option :value="null">Not assigned</option>
                            <option v-for="s in sections" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                    </div>
                    <div><Label>Roll No</Label><Input v-model="editForm.roll_no" /></div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div><Label>DOB</Label><Input v-model="editForm.dob" type="date" /></div>
                    <div><Label>Gender</Label>
                        <select v-model="editForm.gender" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                            <option value="">-</option><option value="male">Male</option><option value="female">Female</option><option value="other">Other</option>
                        </select>
                    </div>
                    <div><Label>Status</Label>
                        <select v-model="editForm.status" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                            <option value="active">Active</option><option value="alumni">Alumni</option><option value="withdrawn">Withdrawn</option>
                        </select>
                    </div>
                </div>
                <DialogFooter><Button type="button" variant="outline" @click="showEdit = false">Cancel</Button><Button type="submit" :disabled="editForm.processing">Update</Button></DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
