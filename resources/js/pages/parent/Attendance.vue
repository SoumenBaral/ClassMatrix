<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import Heading from '@/components/Heading.vue';

type Child = { id: number; name: string };
type Record = { date: string; day: string; status: string; remarks: string | null };
type Summary = { total: number; present: number; absent: number; late: number; excused: number; percentage: number };

const props = defineProps<{
    children: Child[];
    selectedStudentId: number;
    month: string;
    records: Record[];
    summary: Summary | null;
}>();

const studentId = ref(props.selectedStudentId || '');
const month = ref(props.month);

watch([studentId, month], () => {
    if (studentId.value) {
        router.get('/parent/attendance', { student_id: studentId.value, month: month.value }, { preserveState: true });
    }
});

const statusColors: Record<string, string> = {
    present: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    absent: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    late: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    excused: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    half_day: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
};
</script>

<template>
    <Head title="Attendance" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <Heading title="Attendance" description="View your child's daily attendance." />

        <div class="flex flex-wrap items-end gap-4">
            <div>
                <Label>Child</Label>
                <select v-model="studentId"
                    class="flex h-9 w-56 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                    <option value="">Select child...</option>
                    <option v-for="c in children" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
            </div>
            <div>
                <Label>Month</Label>
                <Input type="month" v-model="month" class="w-44" />
            </div>
        </div>

        <!-- Summary cards -->
        <div v-if="summary" class="grid gap-4 sm:grid-cols-3 lg:grid-cols-6">
            <Card>
                <CardContent class="pt-4 text-center">
                    <div class="text-2xl font-bold">{{ summary.total }}</div>
                    <div class="text-xs text-muted-foreground">Working Days</div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="pt-4 text-center">
                    <div class="text-2xl font-bold text-green-600">{{ summary.present }}</div>
                    <div class="text-xs text-muted-foreground">Present</div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="pt-4 text-center">
                    <div class="text-2xl font-bold text-red-600">{{ summary.absent }}</div>
                    <div class="text-xs text-muted-foreground">Absent</div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="pt-4 text-center">
                    <div class="text-2xl font-bold text-yellow-600">{{ summary.late }}</div>
                    <div class="text-xs text-muted-foreground">Late</div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="pt-4 text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ summary.excused }}</div>
                    <div class="text-xs text-muted-foreground">Excused</div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="pt-4 text-center">
                    <div class="text-2xl font-bold" :class="summary.percentage >= 75 ? 'text-green-600' : 'text-red-600'">{{ summary.percentage }}%</div>
                    <div class="text-xs text-muted-foreground">Rate</div>
                </CardContent>
            </Card>
        </div>

        <!-- Daily records -->
        <div v-if="records.length" class="rounded-lg border">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b bg-muted/50">
                        <th class="px-4 py-3 text-left font-medium">Date</th>
                        <th class="px-4 py-3 text-left font-medium">Day</th>
                        <th class="px-4 py-3 text-center font-medium">Status</th>
                        <th class="px-4 py-3 text-left font-medium">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in records" :key="r.date" class="border-b last:border-0">
                        <td class="px-4 py-2 text-muted-foreground">{{ r.date }}</td>
                        <td class="px-4 py-2">{{ r.day }}</td>
                        <td class="px-4 py-2 text-center">
                            <span :class="['rounded-full px-2.5 py-0.5 text-xs font-medium capitalize', statusColors[r.status] || '']">
                                {{ r.status.replace('_', ' ') }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-muted-foreground">{{ r.remarks ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-else class="flex items-center justify-center rounded-xl border-2 border-dashed p-12 text-muted-foreground">
            {{ studentId ? 'No attendance records for this month.' : 'Select a child to view attendance.' }}
        </div>
    </div>
</template>
