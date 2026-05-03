<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import Heading from '@/components/Heading.vue';

type Attempt = {
    id: number; student_name: string; started_at: string;
    submitted_at: string; score: number; percentage: number;
};
type QuizInfo = { id: number; title: string; total_marks: number };

const props = defineProps<{ quiz: QuizInfo; attempts: Attempt[] }>();

function percentBadge(pct: number): string {
    if (pct >= 80) return 'default';
    if (pct >= 50) return 'secondary';
    return 'destructive';
}
</script>

<template>
    <Head :title="`Results - ${quiz.title}`" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center gap-3">
            <Button variant="ghost" size="sm" as-child><a href="/admin/quizzes"><ArrowLeft class="size-4" /></a></Button>
            <Heading :title="`Results: ${quiz.title}`" :description="`${attempts.length} submissions | Total: ${quiz.total_marks} marks`" />
        </div>

        <div class="rounded-lg border">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b bg-muted/50">
                        <th class="px-4 py-3 text-center font-medium w-10">#</th>
                        <th class="px-4 py-3 text-left font-medium">Student</th>
                        <th class="px-4 py-3 text-center font-medium">Started</th>
                        <th class="px-4 py-3 text-center font-medium">Submitted</th>
                        <th class="px-4 py-3 text-center font-medium">Score</th>
                        <th class="px-4 py-3 text-center font-medium">%</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(a, idx) in attempts" :key="a.id" class="border-b last:border-0 hover:bg-muted/30">
                        <td class="px-4 py-2 text-center text-muted-foreground">{{ idx + 1 }}</td>
                        <td class="px-4 py-2 font-medium">{{ a.student_name }}</td>
                        <td class="px-4 py-2 text-center text-muted-foreground">{{ a.started_at }}</td>
                        <td class="px-4 py-2 text-center text-muted-foreground">{{ a.submitted_at }}</td>
                        <td class="px-4 py-2 text-center font-mono font-bold">{{ a.score }}/{{ quiz.total_marks }}</td>
                        <td class="px-4 py-2 text-center"><Badge :variant="percentBadge(a.percentage) as any">{{ a.percentage }}%</Badge></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-if="!attempts.length" class="flex items-center justify-center rounded-xl border-2 border-dashed p-12 text-muted-foreground">No quiz attempts yet.</div>
    </div>
</template>
