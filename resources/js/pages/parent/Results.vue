<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import Heading from '@/components/Heading.vue';

type Child = { id: number; name: string };
type Result = {
    exam: string; subject: string; marks: number | null;
    full_marks: number; pass_marks: number; grade: string | null;
    passed: boolean; percentage: number;
};

const props = defineProps<{
    children: Child[];
    selectedStudentId: number;
    results: Result[];
}>();

const studentId = ref(props.selectedStudentId || '');

watch(studentId, () => {
    if (studentId.value) {
        router.get('/parent/results', { student_id: studentId.value }, { preserveState: true });
    }
});

// Group results by exam
const groupedResults = computed(() => {
    const groups: Record<string, Result[]> = {};
    for (const r of props.results) {
        const key = r.exam ?? 'Unknown';
        if (!groups[key]) groups[key] = [];
        groups[key].push(r);
    }
    return groups;
});
</script>

<template>
    <Head title="Results" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <Heading title="Exam Results" description="View your child's exam scores and grades." />

        <div class="flex items-end gap-4">
            <div>
                <Label>Child</Label>
                <select v-model="studentId"
                    class="flex h-9 w-56 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                    <option value="">Select child...</option>
                    <option v-for="c in children" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
            </div>
        </div>

        <template v-if="Object.keys(groupedResults).length">
            <div v-for="(examResults, examName) in groupedResults" :key="examName" class="space-y-2">
                <h3 class="text-sm font-semibold text-muted-foreground">{{ examName }}</h3>
                <div class="rounded-lg border">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th class="px-4 py-3 text-left font-medium">Subject</th>
                                <th class="px-4 py-3 text-center font-medium">Marks</th>
                                <th class="px-4 py-3 text-center font-medium">Out Of</th>
                                <th class="px-4 py-3 text-center font-medium">%</th>
                                <th class="px-4 py-3 text-center font-medium">Grade</th>
                                <th class="px-4 py-3 text-center font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="r in examResults" :key="r.subject" class="border-b last:border-0">
                                <td class="px-4 py-2 font-medium">{{ r.subject }}</td>
                                <td class="px-4 py-2 text-center font-mono" :class="r.passed ? '' : 'text-red-600 font-bold'">
                                    {{ r.marks ?? '-' }}
                                </td>
                                <td class="px-4 py-2 text-center text-muted-foreground">{{ r.full_marks }}</td>
                                <td class="px-4 py-2 text-center">
                                    <Badge :variant="r.percentage >= 60 ? 'default' : r.percentage >= 33 ? 'secondary' : 'destructive'" v-if="r.marks !== null">
                                        {{ r.percentage }}%
                                    </Badge>
                                    <span v-else class="text-muted-foreground">-</span>
                                </td>
                                <td class="px-4 py-2 text-center font-bold">{{ r.grade ?? '-' }}</td>
                                <td class="px-4 py-2 text-center">
                                    <Badge v-if="r.marks !== null" :variant="r.passed ? 'default' : 'destructive'">
                                        {{ r.passed ? 'Pass' : 'Fail' }}
                                    </Badge>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>

        <div v-else class="flex items-center justify-center rounded-xl border-2 border-dashed p-12 text-muted-foreground">
            {{ studentId ? 'No exam results found yet.' : 'Select a child to view results.' }}
        </div>
    </div>
</template>
