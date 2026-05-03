<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import Heading from '@/components/Heading.vue';

type Exam = { id: number; name: string; exam_type: { name: string } };
type Section = { id: number; name: string; class_level_id: number };
type SubjectInfo = { id: number; name: string; code: string; full_marks: number; pass_marks: number; schedule_id: number };
type SubjectResult = { marks: number | null; grade: string | null; pass: boolean | null };
type StudentResult = {
    id: number; name: string; roll_no: string;
    subjects: Record<number, SubjectResult>;
    total_obtained: number; total_full: number;
    percentage: number; grade: string | null; gpa: number | null;
};

const props = defineProps<{
    exams: Exam[];
    sections: Section[];
    selectedExamId: number;
    selectedSectionId: number;
    results: StudentResult[];
    subjects: SubjectInfo[];
}>();

const examId = ref(props.selectedExamId || '');
const sectionId = ref(props.selectedSectionId || '');

function navigate() {
    const params: Record<string, any> = {};
    if (examId.value) params.exam_id = examId.value;
    if (sectionId.value) params.section_id = sectionId.value;
    router.get('/admin/results', params, { preserveState: true, preserveScroll: true });
}

watch(examId, () => { sectionId.value = ''; navigate(); });
watch(sectionId, () => navigate());

function percentBadge(pct: number): string {
    if (pct >= 80) return 'default';
    if (pct >= 60) return 'secondary';
    if (pct >= 33) return 'outline';
    return 'destructive';
}

function markClass(result: SubjectResult): string {
    if (result.marks === null) return 'text-muted-foreground';
    return result.pass ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400 font-bold';
}
</script>

<template>
    <Head title="Exam Results" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <Heading title="Exam Results" description="View student results and rankings." />

        <div class="flex flex-wrap items-end gap-4">
            <div>
                <Label>Exam</Label>
                <select v-model="examId" class="flex h-9 w-64 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                    <option value="">Select exam...</option>
                    <option v-for="e in exams" :key="e.id" :value="e.id">{{ e.name }}</option>
                </select>
            </div>
            <div>
                <Label>Section</Label>
                <select v-model="sectionId" class="flex h-9 w-56 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                    <option value="">Select...</option>
                    <option v-for="s in sections" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
            </div>
        </div>

        <template v-if="results.length">
            <div class="overflow-auto rounded-lg border">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b bg-muted/50">
                            <th class="sticky left-0 bg-muted/50 px-3 py-3 text-center font-medium w-10">#</th>
                            <th class="sticky left-10 bg-muted/50 px-3 py-3 text-left font-medium min-w-[120px]">Student</th>
                            <th class="px-3 py-3 text-left font-medium w-14">Roll</th>
                            <th v-for="sub in subjects" :key="sub.id" class="px-3 py-3 text-center font-medium min-w-[80px]">
                                <div>{{ sub.code }}</div>
                                <div class="text-xs text-muted-foreground font-normal">({{ sub.full_marks }})</div>
                            </th>
                            <th class="px-3 py-3 text-center font-medium">Total</th>
                            <th class="px-3 py-3 text-center font-medium">%</th>
                            <th class="px-3 py-3 text-center font-medium">Grade</th>
                            <th class="px-3 py-3 text-center font-medium">GPA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(student, idx) in results" :key="student.id" class="border-b last:border-0 hover:bg-muted/30">
                            <td class="sticky left-0 bg-background px-3 py-2 text-center text-muted-foreground">{{ idx + 1 }}</td>
                            <td class="sticky left-10 bg-background px-3 py-2 font-medium">{{ student.name }}</td>
                            <td class="px-3 py-2 text-muted-foreground">{{ student.roll_no || '-' }}</td>
                            <td v-for="sub in subjects" :key="sub.id" class="px-3 py-2 text-center"
                                :class="markClass(student.subjects[sub.id] || { marks: null, grade: null, pass: null })">
                                <template v-if="student.subjects[sub.id]?.marks !== null && student.subjects[sub.id]?.marks !== undefined">
                                    {{ student.subjects[sub.id].marks }}
                                </template>
                                <span v-else class="text-muted-foreground">-</span>
                            </td>
                            <td class="px-3 py-2 text-center font-medium">{{ student.total_obtained }}/{{ student.total_full }}</td>
                            <td class="px-3 py-2 text-center">
                                <Badge :variant="percentBadge(student.percentage) as any">{{ student.percentage }}%</Badge>
                            </td>
                            <td class="px-3 py-2 text-center font-bold">{{ student.grade ?? '-' }}</td>
                            <td class="px-3 py-2 text-center">{{ student.gpa ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </template>

        <div v-else class="flex items-center justify-center rounded-xl border-2 border-dashed p-12 text-muted-foreground">
            {{ examId && sectionId ? 'No results found. Marks may not be entered yet.' : 'Select an exam and section to view results.' }}
        </div>
    </div>
</template>
