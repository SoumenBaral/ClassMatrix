<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import Heading from '@/components/Heading.vue';

type Section = { id: number; name: string };
type ReportRow = {
    id: number; name: string; roll_no: string;
    total_days: number; present: number; absent: number;
    late: number; excused: number; percentage: number;
};

const props = defineProps<{
    sections: Section[];
    selectedSectionId: number;
    month: string;
    report: ReportRow[];
}>();

const sectionId = ref(props.selectedSectionId || '');
const month = ref(props.month);

watch([sectionId, month], () => {
    if (sectionId.value) {
        router.get('/admin/attendance/report', {
            section_id: sectionId.value,
            month: month.value,
        }, { preserveState: true, preserveScroll: true });
    }
});

function percentageColor(pct: number): string {
    if (pct >= 90) return 'default';
    if (pct >= 75) return 'secondary';
    return 'destructive';
}
</script>

<template>
    <Head title="Attendance Report" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <Heading title="Attendance Report" description="Monthly attendance summary by section." />

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
                <Label>Month</Label>
                <Input type="month" v-model="month" class="w-44" />
            </div>
        </div>

        <div v-if="report.length" class="rounded-lg border">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b bg-muted/50">
                        <th class="px-4 py-3 text-left font-medium">Roll</th>
                        <th class="px-4 py-3 text-left font-medium">Student</th>
                        <th class="px-4 py-3 text-center font-medium">Working Days</th>
                        <th class="px-4 py-3 text-center font-medium">Present</th>
                        <th class="px-4 py-3 text-center font-medium">Absent</th>
                        <th class="px-4 py-3 text-center font-medium">Late</th>
                        <th class="px-4 py-3 text-center font-medium">Excused</th>
                        <th class="px-4 py-3 text-center font-medium">%</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in report" :key="row.id" class="border-b last:border-0 hover:bg-muted/30">
                        <td class="px-4 py-2 text-muted-foreground">{{ row.roll_no || '-' }}</td>
                        <td class="px-4 py-2 font-medium">{{ row.name }}</td>
                        <td class="px-4 py-2 text-center">{{ row.total_days }}</td>
                        <td class="px-4 py-2 text-center text-green-600">{{ row.present }}</td>
                        <td class="px-4 py-2 text-center text-red-600">{{ row.absent }}</td>
                        <td class="px-4 py-2 text-center text-yellow-600">{{ row.late }}</td>
                        <td class="px-4 py-2 text-center text-blue-600">{{ row.excused }}</td>
                        <td class="px-4 py-2 text-center">
                            <Badge :variant="percentageColor(row.percentage) as any">
                                {{ row.percentage }}%
                            </Badge>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-else class="flex items-center justify-center rounded-xl border-2 border-dashed p-12 text-muted-foreground">
            {{ sectionId ? 'No attendance data for this period.' : 'Select a section and month to view the report.' }}
        </div>
    </div>
</template>
