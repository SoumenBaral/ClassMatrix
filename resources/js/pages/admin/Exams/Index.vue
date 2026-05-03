<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, CalendarDays, Eye } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';

type ExamType = { id: number; name: string };
type Term = { id: number; name: string };
type Exam = {
    id: number; name: string; status: string;
    start_date: string | null; end_date: string | null;
    exam_type: ExamType; term: Term | null; schedules_count: number;
};

const props = defineProps<{
    exams: Exam[];
    examTypes: ExamType[];
    terms: Term[];
}>();

const showDialog = ref(false);
const editing = ref<Exam | null>(null);

const form = useForm({
    exam_type_id: '' as any,
    name: '',
    term_id: null as number | null,
    start_date: '',
    end_date: '',
    status: 'upcoming',
});

function openCreate() {
    editing.value = null;
    form.reset();
    showDialog.value = true;
}

function openEdit(exam: Exam) {
    editing.value = exam;
    form.exam_type_id = exam.exam_type.id;
    form.name = exam.name;
    form.term_id = exam.term?.id ?? null;
    form.start_date = exam.start_date ?? '';
    form.end_date = exam.end_date ?? '';
    form.status = exam.status;
    showDialog.value = true;
}

function submit() {
    if (editing.value) {
        form.put(`/admin/exams/${editing.value.id}`, { onSuccess: () => { showDialog.value = false; } });
    } else {
        form.post('/admin/exams', { onSuccess: () => { showDialog.value = false; form.reset(); } });
    }
}

function destroy(exam: Exam) {
    if (confirm(`Delete "${exam.name}"? This deletes all schedules and marks.`)) {
        router.delete(`/admin/exams/${exam.id}`);
    }
}

const statusVariant: Record<string, string> = {
    upcoming: 'secondary',
    ongoing: 'default',
    completed: 'outline',
    cancelled: 'destructive',
};
</script>

<template>
    <Head title="Exams" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <Heading title="Exams" description="Create and manage examinations." />
            <Button @click="openCreate"><Plus class="mr-2 size-4" /> Create Exam</Button>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Card v-for="exam in exams" :key="exam.id">
                <CardHeader class="flex flex-row items-start justify-between pb-2">
                    <div>
                        <CardTitle class="text-base">{{ exam.name }}</CardTitle>
                        <p class="text-xs text-muted-foreground mt-1">{{ exam.exam_type.name }}</p>
                    </div>
                    <Badge :variant="(statusVariant[exam.status] as any) ?? 'secondary'" class="capitalize">
                        {{ exam.status }}
                    </Badge>
                </CardHeader>
                <CardContent class="space-y-3">
                    <div class="text-sm text-muted-foreground space-y-1">
                        <div v-if="exam.start_date">
                            <CalendarDays class="inline size-3 mr-1" />
                            {{ exam.start_date }} - {{ exam.end_date }}
                        </div>
                        <div v-if="exam.term">Term: {{ exam.term.name }}</div>
                        <div>{{ exam.schedules_count }} schedule(s)</div>
                    </div>
                    <div class="flex gap-2 pt-1">
                        <Button variant="outline" size="sm" as-child>
                            <a :href="`/admin/exams/${exam.id}/schedules`">
                                <Eye class="mr-1 size-3" /> Schedules
                            </a>
                        </Button>
                        <Button variant="outline" size="sm" @click="openEdit(exam)">
                            <Pencil class="size-3" />
                        </Button>
                        <Button variant="outline" size="sm" class="text-destructive" @click="destroy(exam)">
                            <Trash2 class="size-3" />
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>

        <div v-if="!exams.length" class="flex items-center justify-center rounded-xl border-2 border-dashed p-12 text-muted-foreground">
            No exams created yet.
        </div>
    </div>

    <Dialog v-model:open="showDialog">
        <DialogContent>
            <DialogHeader><DialogTitle>{{ editing ? 'Edit' : 'Create' }} Exam</DialogTitle></DialogHeader>
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <Label>Name</Label>
                    <Input v-model="form.name" placeholder="e.g. Mid-Term Exam 2026" />
                    <InputError :message="form.errors.name" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <Label>Exam Type</Label>
                        <select v-model="form.exam_type_id" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                            <option value="">Select...</option>
                            <option v-for="t in examTypes" :key="t.id" :value="t.id">{{ t.name }}</option>
                        </select>
                        <InputError :message="form.errors.exam_type_id" />
                    </div>
                    <div>
                        <Label>Term</Label>
                        <select v-model="form.term_id" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                            <option :value="null">None</option>
                            <option v-for="t in terms" :key="t.id" :value="t.id">{{ t.name }}</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><Label>Start Date</Label><Input type="date" v-model="form.start_date" /><InputError :message="form.errors.start_date" /></div>
                    <div><Label>End Date</Label><Input type="date" v-model="form.end_date" /><InputError :message="form.errors.end_date" /></div>
                </div>
                <div>
                    <Label>Status</Label>
                    <select v-model="form.status" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                        <option value="upcoming">Upcoming</option>
                        <option value="ongoing">Ongoing</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="showDialog = false">Cancel</Button>
                    <Button type="submit" :disabled="form.processing">{{ editing ? 'Update' : 'Create' }}</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
