<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';

type Submission = {
    id: number; student_name: string; roll_no: string;
    submitted_at: string; comment: string | null;
    marks: number | null; feedback: string | null;
    graded: boolean; grader: string | null;
};
type AssignmentInfo = {
    id: number; title: string; subject: string;
    section: string; due_date: string; total_marks: number;
};

const props = defineProps<{ assignment: AssignmentInfo; submissions: Submission[] }>();

const showGradeDialog = ref(false);
const gradingId = ref(0);
const gradeForm = useForm({ marks: 0, feedback: '' });

function openGrade(sub: Submission) {
    gradingId.value = sub.id;
    gradeForm.marks = sub.marks ?? 0;
    gradeForm.feedback = sub.feedback ?? '';
    showGradeDialog.value = true;
}

function submitGrade() {
    gradeForm.post(`/admin/submissions/${gradingId.value}/grade`, {
        onSuccess: () => { showGradeDialog.value = false; },
    });
}
</script>

<template>
    <Head :title="`Submissions - ${assignment.title}`" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center gap-3">
            <Button variant="ghost" size="sm" as-child><a href="/admin/assignments"><ArrowLeft class="size-4" /></a></Button>
            <Heading :title="assignment.title" :description="`${assignment.subject} | ${assignment.section} | Due: ${assignment.due_date} | Total: ${assignment.total_marks}`" />
        </div>

        <div class="rounded-lg border">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b bg-muted/50">
                        <th class="px-4 py-3 text-left font-medium">Roll</th>
                        <th class="px-4 py-3 text-left font-medium">Student</th>
                        <th class="px-4 py-3 text-center font-medium">Submitted</th>
                        <th class="px-4 py-3 text-left font-medium">Comment</th>
                        <th class="px-4 py-3 text-center font-medium">Marks</th>
                        <th class="px-4 py-3 text-center font-medium">Status</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="sub in submissions" :key="sub.id" class="border-b last:border-0 hover:bg-muted/30">
                        <td class="px-4 py-2 text-muted-foreground">{{ sub.roll_no || '-' }}</td>
                        <td class="px-4 py-2 font-medium">{{ sub.student_name }}</td>
                        <td class="px-4 py-2 text-center text-muted-foreground">{{ sub.submitted_at }}</td>
                        <td class="px-4 py-2 text-muted-foreground max-w-[200px] truncate">{{ sub.comment ?? '-' }}</td>
                        <td class="px-4 py-2 text-center font-mono">
                            <span v-if="sub.marks !== null" :class="sub.marks >= assignment.total_marks * 0.33 ? 'text-green-600' : 'text-red-600'">{{ sub.marks }}/{{ assignment.total_marks }}</span>
                            <span v-else class="text-muted-foreground">-</span>
                        </td>
                        <td class="px-4 py-2 text-center">
                            <Badge :variant="sub.graded ? 'default' : 'secondary'">{{ sub.graded ? 'Graded' : 'Pending' }}</Badge>
                        </td>
                        <td class="px-4 py-2 text-right">
                            <Button variant="outline" size="sm" @click="openGrade(sub)">{{ sub.graded ? 'Re-grade' : 'Grade' }}</Button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-if="!submissions.length" class="flex items-center justify-center rounded-xl border-2 border-dashed p-12 text-muted-foreground">No submissions yet.</div>
    </div>

    <Dialog v-model:open="showGradeDialog">
        <DialogContent><DialogHeader><DialogTitle>Grade Submission</DialogTitle></DialogHeader>
            <form @submit.prevent="submitGrade" class="space-y-4">
                <div><Label>Marks (out of {{ assignment.total_marks }})</Label><Input type="number" v-model.number="gradeForm.marks" min="0" :max="assignment.total_marks" step="0.5" /><InputError :message="gradeForm.errors.marks" /></div>
                <div><Label>Feedback</Label><textarea v-model="gradeForm.feedback" rows="3" class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring" placeholder="Optional feedback..." /></div>
                <DialogFooter><Button type="button" variant="outline" @click="showGradeDialog = false">Cancel</Button><Button type="submit" :disabled="gradeForm.processing">Save Grade</Button></DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
