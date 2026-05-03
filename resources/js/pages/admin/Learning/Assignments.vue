<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, Eye } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';

type Section = { id: number; name: string };
type Subject = { id: number; name: string };
type Assignment = {
    id: number; title: string; subject: string; section: string;
    teacher: string; due_date: string; total_marks: number;
    submissions_count: number; is_overdue: boolean;
};
type PaginatedData = { data: Assignment[]; links: { url: string | null; label: string; active: boolean }[] };

const props = defineProps<{
    assignments: PaginatedData; sections: Section[]; subjects: Subject[];
    filters: { section_id?: string };
}>();

const sectionId = ref(props.filters.section_id ?? '');
watch(sectionId, () => {
    router.get('/admin/assignments', { section_id: sectionId.value || undefined }, { preserveState: true });
});

const showDialog = ref(false);
const editing = ref<Assignment | null>(null);
const form = useForm({
    title: '', description: '', section_id: '' as any,
    subject_id: '' as any, due_date: '', total_marks: 100,
});

function openCreate() { editing.value = null; form.reset(); form.total_marks = 100; showDialog.value = true; }
function openEdit(a: Assignment) {
    editing.value = a; form.title = a.title; form.due_date = a.due_date; form.total_marks = a.total_marks;
    showDialog.value = true;
}
function submit() {
    if (editing.value) form.put(`/admin/assignments/${editing.value.id}`, { onSuccess: () => { showDialog.value = false; } });
    else form.post('/admin/assignments', { onSuccess: () => { showDialog.value = false; form.reset(); } });
}
function destroy(id: number) { if (confirm('Delete assignment?')) router.delete(`/admin/assignments/${id}`); }
</script>

<template>
    <Head title="Assignments" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <Heading title="Assignments" description="Create assignments and review submissions." />
            <Button @click="openCreate"><Plus class="mr-2 size-4" /> New Assignment</Button>
        </div>

        <div class="flex items-end gap-4">
            <div>
                <Label>Section</Label>
                <select v-model="sectionId" class="flex h-9 w-56 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                    <option value="">All sections</option>
                    <option v-for="s in sections" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
            </div>
        </div>

        <div class="rounded-lg border">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b bg-muted/50">
                        <th class="px-4 py-3 text-left font-medium">Title</th>
                        <th class="px-4 py-3 text-left font-medium">Subject</th>
                        <th class="px-4 py-3 text-left font-medium">Section</th>
                        <th class="px-4 py-3 text-center font-medium">Due Date</th>
                        <th class="px-4 py-3 text-center font-medium">Marks</th>
                        <th class="px-4 py-3 text-center font-medium">Submissions</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="a in assignments.data" :key="a.id"
                        :class="['border-b last:border-0 hover:bg-muted/30', a.is_overdue ? 'bg-red-50/30 dark:bg-red-950/10' : '']">
                        <td class="px-4 py-2 font-medium">{{ a.title }}</td>
                        <td class="px-4 py-2 text-muted-foreground">{{ a.subject }}</td>
                        <td class="px-4 py-2 text-muted-foreground">{{ a.section }}</td>
                        <td class="px-4 py-2 text-center" :class="a.is_overdue ? 'text-red-600 font-bold' : 'text-muted-foreground'">{{ a.due_date }}</td>
                        <td class="px-4 py-2 text-center">{{ a.total_marks }}</td>
                        <td class="px-4 py-2 text-center"><Badge variant="secondary">{{ a.submissions_count }}</Badge></td>
                        <td class="px-4 py-2 text-right">
                            <div class="flex justify-end gap-1">
                                <Button variant="outline" size="sm" as-child><a :href="`/admin/assignments/${a.id}/submissions`"><Eye class="mr-1 size-3" /> Submissions</a></Button>
                                <Button variant="ghost" size="sm" @click="openEdit(a)"><Pencil class="size-3" /></Button>
                                <Button variant="ghost" size="sm" class="text-destructive" @click="destroy(a.id)"><Trash2 class="size-3" /></Button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-if="!assignments.data.length" class="flex items-center justify-center rounded-xl border-2 border-dashed p-12 text-muted-foreground">No assignments found.</div>
    </div>

    <Dialog v-model:open="showDialog">
        <DialogContent><DialogHeader><DialogTitle>{{ editing ? 'Edit' : 'Create' }} Assignment</DialogTitle></DialogHeader>
            <form @submit.prevent="submit" class="space-y-4">
                <div><Label>Title</Label><Input v-model="form.title" /><InputError :message="form.errors.title" /></div>
                <div><Label>Description</Label>
                    <textarea v-model="form.description" rows="3" class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring" />
                </div>
                <div v-if="!editing" class="grid grid-cols-2 gap-4">
                    <div><Label>Section</Label>
                        <select v-model="form.section_id" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                            <option value="">Select...</option><option v-for="s in sections" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select><InputError :message="form.errors.section_id" />
                    </div>
                    <div><Label>Subject</Label>
                        <select v-model="form.subject_id" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                            <option value="">Select...</option><option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select><InputError :message="form.errors.subject_id" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><Label>Due Date</Label><Input type="date" v-model="form.due_date" /><InputError :message="form.errors.due_date" /></div>
                    <div><Label>Total Marks</Label><Input type="number" v-model.number="form.total_marks" min="1" /></div>
                </div>
                <DialogFooter><Button type="button" variant="outline" @click="showDialog = false">Cancel</Button><Button type="submit" :disabled="form.processing">{{ editing ? 'Update' : 'Create' }}</Button></DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
