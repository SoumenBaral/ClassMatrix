<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import Heading from '@/components/Heading.vue';

type Student = { id: number; name: string; roll_no: string; admission_no: string };
type Section = { id: number; name: string };
type AttendanceRecord = { id: number; status: string; remarks: string | null };

const props = defineProps<{
    sections: Section[];
    selectedSectionId: number;
    date: string;
    students: Student[];
    attendances: Record<number, AttendanceRecord>;
}>();

const sectionId = ref(props.selectedSectionId || '');
const date = ref(props.date);

// Track attendance state locally
const records = ref<Record<number, { status: string; remarks: string }>>(
    Object.fromEntries(
        props.students.map((s) => [
            s.id,
            {
                status: props.attendances[s.id]?.status ?? 'present',
                remarks: props.attendances[s.id]?.remarks ?? '',
            },
        ]),
    ),
);

// Re-initialize records when students change
watch(() => props.students, (newStudents) => {
    records.value = Object.fromEntries(
        newStudents.map((s) => [
            s.id,
            {
                status: props.attendances[s.id]?.status ?? 'present',
                remarks: props.attendances[s.id]?.remarks ?? '',
            },
        ]),
    );
}, { deep: true });

const statuses = ['present', 'absent', 'late', 'excused', 'half_day'] as const;

const statusColors: Record<string, string> = {
    present: 'bg-green-100 text-green-800 border-green-300 dark:bg-green-900 dark:text-green-200',
    absent: 'bg-red-100 text-red-800 border-red-300 dark:bg-red-900 dark:text-red-200',
    late: 'bg-yellow-100 text-yellow-800 border-yellow-300 dark:bg-yellow-900 dark:text-yellow-200',
    excused: 'bg-blue-100 text-blue-800 border-blue-300 dark:bg-blue-900 dark:text-blue-200',
    half_day: 'bg-orange-100 text-orange-800 border-orange-300 dark:bg-orange-900 dark:text-orange-200',
};

function loadSection() {
    router.get('/admin/attendance', { section_id: sectionId.value, date: date.value }, {
        preserveState: true,
        preserveScroll: true,
    });
}

watch([sectionId, date], () => {
    if (sectionId.value) loadSection();
});

function markAll(status: string) {
    for (const key of Object.keys(records.value)) {
        records.value[Number(key)].status = status;
    }
}

const saving = ref(false);

function save() {
    saving.value = true;
    router.post('/admin/attendance/bulk', {
        section_id: sectionId.value,
        date: date.value,
        records: Object.entries(records.value).map(([studentId, data]) => ({
            student_id: Number(studentId),
            status: data.status,
            remarks: data.remarks || null,
        })),
    }, {
        preserveScroll: true,
        onFinish: () => { saving.value = false; },
    });
}

const summary = computed(() => {
    const counts: Record<string, number> = {};
    for (const s of statuses) counts[s] = 0;
    for (const r of Object.values(records.value)) {
        counts[r.status] = (counts[r.status] || 0) + 1;
    }
    return counts;
});
</script>

<template>
    <Head title="Mark Attendance" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <Heading title="Mark Attendance" description="Daily student attendance." />
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <Label>Section</Label>
                <select v-model="sectionId"
                    class="flex h-9 w-56 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                    <option value="">Select section...</option>
                    <option v-for="s in sections" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
            </div>
            <div>
                <Label>Date</Label>
                <Input type="date" v-model="date" class="w-44" />
            </div>
        </div>

        <template v-if="students.length">
            <!-- Quick actions & summary -->
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex gap-2">
                    <span class="text-sm text-muted-foreground mr-2">Mark All:</span>
                    <button v-for="s in statuses" :key="s" @click="markAll(s)"
                        :class="['rounded-md border px-2 py-1 text-xs font-medium capitalize transition-colors', statusColors[s]]">
                        {{ s.replace('_', ' ') }}
                    </button>
                </div>
                <div class="flex gap-3 text-sm">
                    <span v-for="s in statuses" :key="s" class="capitalize">
                        <span :class="['inline-block size-2 rounded-full mr-1', statusColors[s].split(' ')[0]]"></span>
                        {{ s.replace('_', ' ') }}: {{ summary[s] }}
                    </span>
                </div>
            </div>

            <!-- Student list -->
            <div class="rounded-lg border">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b bg-muted/50">
                            <th class="px-4 py-3 text-left font-medium w-16">Roll</th>
                            <th class="px-4 py-3 text-left font-medium">Student</th>
                            <th class="px-4 py-3 text-center font-medium">Status</th>
                            <th class="px-4 py-3 text-left font-medium">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="student in students" :key="student.id" class="border-b last:border-0 hover:bg-muted/30">
                            <td class="px-4 py-2 text-muted-foreground">{{ student.roll_no || '-' }}</td>
                            <td class="px-4 py-2 font-medium">{{ student.name }}</td>
                            <td class="px-4 py-2">
                                <div class="flex justify-center gap-1">
                                    <button v-for="s in statuses" :key="s"
                                        @click="records[student.id].status = s"
                                        :class="[
                                            'rounded-md border px-2 py-1 text-xs font-medium capitalize transition-all',
                                            records[student.id]?.status === s
                                                ? statusColors[s] + ' ring-2 ring-offset-1 ring-current'
                                                : 'bg-muted/30 text-muted-foreground hover:bg-muted',
                                        ]">
                                        {{ s === 'half_day' ? 'HD' : s.charAt(0).toUpperCase() }}
                                    </button>
                                </div>
                            </td>
                            <td class="px-4 py-2">
                                <Input v-model="records[student.id].remarks" placeholder="Optional..." class="h-8 text-xs" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end">
                <Button @click="save" :disabled="saving" size="lg">
                    {{ saving ? 'Saving...' : 'Save Attendance' }}
                </Button>
            </div>
        </template>

        <div v-else-if="sectionId" class="flex items-center justify-center rounded-xl border-2 border-dashed p-12 text-muted-foreground">
            No active students in this section.
        </div>

        <div v-else class="flex items-center justify-center rounded-xl border-2 border-dashed p-12 text-muted-foreground">
            Select a section and date to mark attendance.
        </div>
    </div>
</template>
