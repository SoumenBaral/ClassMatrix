<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import Heading from '@/components/Heading.vue';

type Exam = { id: number; name: string; exam_type: { name: string } };
type Schedule = {
    id: number; class_level: { id: number; name: string };
    subject: { id: number; name: string; code: string };
    full_marks: number; pass_marks: number;
};
type Section = { id: number; name: string };
type Student = { id: number; name: string; roll_no: string };
type MarkData = { marks_obtained: number | null; grade: string | null; remarks: string | null };

const props = defineProps<{
    exams: Exam[];
    schedules: Schedule[];
    sections: Section[];
    students: Student[];
    marks: Record<number, MarkData>;
    selectedExamId: number;
    selectedScheduleId: number;
    selectedSectionId: number;
    schedule: Schedule | null;
}>();

const examId = ref(props.selectedExamId || '');
const scheduleId = ref(props.selectedScheduleId || '');
const sectionId = ref(props.selectedSectionId || '');

// Local marks state
const localMarks = ref<Record<number, { marks_obtained: string; remarks: string }>>(
    Object.fromEntries(
        props.students.map(s => [s.id, {
            marks_obtained: props.marks[s.id]?.marks_obtained?.toString() ?? '',
            remarks: props.marks[s.id]?.remarks ?? '',
        }])
    )
);

watch(() => props.students, (newStudents) => {
    localMarks.value = Object.fromEntries(
        newStudents.map(s => [s.id, {
            marks_obtained: props.marks[s.id]?.marks_obtained?.toString() ?? '',
            remarks: props.marks[s.id]?.remarks ?? '',
        }])
    );
}, { deep: true });

function navigate() {
    const params: Record<string, any> = {};
    if (examId.value) params.exam_id = examId.value;
    if (scheduleId.value) params.schedule_id = scheduleId.value;
    if (sectionId.value) params.section_id = sectionId.value;
    router.get('/admin/marks', params, { preserveState: true, preserveScroll: true });
}

watch(examId, () => { scheduleId.value = ''; sectionId.value = ''; navigate(); });
watch(scheduleId, () => { sectionId.value = ''; navigate(); });
watch(sectionId, () => navigate());

const saving = ref(false);

function save() {
    saving.value = true;
    router.post('/admin/marks/bulk', {
        exam_schedule_id: scheduleId.value,
        records: Object.entries(localMarks.value).map(([studentId, data]) => ({
            student_id: Number(studentId),
            marks_obtained: data.marks_obtained !== '' ? Number(data.marks_obtained) : null,
            remarks: data.remarks || null,
        })),
    }, {
        preserveScroll: true,
        onFinish: () => { saving.value = false; },
    });
}

function getGrade(studentId: number): string {
    return props.marks[studentId]?.grade ?? '-';
}
</script>

<template>
    <Head title="Marks Entry" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <Heading title="Marks Entry" description="Enter marks for students by exam and subject." />

        <!-- Filters -->
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <Label>Exam</Label>
                <select v-model="examId" class="flex h-9 w-56 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                    <option value="">Select exam...</option>
                    <option v-for="e in exams" :key="e.id" :value="e.id">{{ e.name }} ({{ e.exam_type.name }})</option>
                </select>
            </div>
            <div v-if="schedules.length">
                <Label>Subject</Label>
                <select v-model="scheduleId" class="flex h-9 w-56 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                    <option value="">Select subject...</option>
                    <option v-for="s in schedules" :key="s.id" :value="s.id">
                        {{ s.class_level.name }} - {{ s.subject.name }} ({{ s.full_marks }})
                    </option>
                </select>
            </div>
            <div v-if="sections.length">
                <Label>Section</Label>
                <select v-model="sectionId" class="flex h-9 w-44 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                    <option value="">Select...</option>
                    <option v-for="s in sections" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
            </div>
        </div>

        <!-- Marks table -->
        <template v-if="students.length && schedule">
            <div class="rounded-lg border p-3 bg-muted/30 text-sm">
                Full Marks: <strong>{{ (schedule as any).full_marks }}</strong> |
                Pass Marks: <strong>{{ (schedule as any).pass_marks }}</strong>
            </div>

            <div class="rounded-lg border">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b bg-muted/50">
                            <th class="px-4 py-3 text-left font-medium w-16">Roll</th>
                            <th class="px-4 py-3 text-left font-medium">Student</th>
                            <th class="px-4 py-3 text-center font-medium w-32">Marks</th>
                            <th class="px-4 py-3 text-center font-medium w-16">Grade</th>
                            <th class="px-4 py-3 text-left font-medium">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="student in students" :key="student.id" class="border-b last:border-0 hover:bg-muted/30">
                            <td class="px-4 py-2 text-muted-foreground">{{ student.roll_no || '-' }}</td>
                            <td class="px-4 py-2 font-medium">{{ student.name }}</td>
                            <td class="px-4 py-2">
                                <Input type="number" v-model="localMarks[student.id].marks_obtained"
                                    :max="(schedule as any).full_marks" min="0" step="0.5"
                                    class="h-8 w-24 mx-auto text-center" placeholder="-" />
                            </td>
                            <td class="px-4 py-2 text-center font-medium">{{ getGrade(student.id) }}</td>
                            <td class="px-4 py-2">
                                <Input v-model="localMarks[student.id].remarks" class="h-8 text-xs" placeholder="Optional" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end">
                <Button @click="save" :disabled="saving" size="lg">
                    {{ saving ? 'Saving...' : 'Save Marks' }}
                </Button>
            </div>
        </template>

        <div v-else class="flex items-center justify-center rounded-xl border-2 border-dashed p-12 text-muted-foreground">
            Select an exam, subject, and section to enter marks.
        </div>
    </div>
</template>
