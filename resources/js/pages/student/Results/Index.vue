<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Trophy, TrendingUp, CheckCircle, XCircle, BookOpen } from 'lucide-vue-next'
import { Badge } from '@/components/ui/badge'

interface ExamInfo {
    id: number
    name: string
    type: string | null
    start_date: string | null
}

interface SubjectResult {
    subject: string | null
    marks_obtained: number | null
    full_marks: number | null
    pass_marks: number | null
    grade: string | null
    percentage: number | null
    is_passing: boolean
}

interface ExamResult {
    subjects: SubjectResult[]
    total_obtained: number
    total_full: number
    percentage: number
    passed_count: number
    total_subjects: number
}

const props = defineProps<{
    exams: ExamInfo[]
    results: Record<string, ExamResult>
    className: string | null
    sectionName: string | null
}>()

const selectedExamId = ref<number | null>(props.exams[0]?.id ?? null)

const selectedResult = computed(() => {
    if (!selectedExamId.value) return null
    return props.results[selectedExamId.value] ?? null
})

const selectedExam = computed(() =>
    props.exams.find(e => e.id === selectedExamId.value),
)

function gradeColor(grade: string | null): string {
    if (!grade) return 'text-muted-foreground'
    if (grade.startsWith('A')) return 'text-green-600 dark:text-green-400'
    if (grade.startsWith('B')) return 'text-blue-600 dark:text-blue-400'
    if (grade.startsWith('C')) return 'text-amber-600 dark:text-amber-400'
    return 'text-red-600 dark:text-red-400'
}

function percentageColor(pct: number): string {
    if (pct >= 80) return 'bg-green-500'
    if (pct >= 60) return 'bg-blue-500'
    if (pct >= 40) return 'bg-amber-500'
    return 'bg-red-500'
}
</script>

<template>
    <Head title="My Results" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div>
            <h1 class="text-2xl font-bold">My Results</h1>
            <p class="mt-1 text-sm text-muted-foreground">
                {{ className }} - Section {{ sectionName }}
            </p>
        </div>

        <!-- Exam selector -->
        <div v-if="exams.length" class="flex gap-2 overflow-x-auto pb-1">
            <button
                v-for="exam in exams"
                :key="exam.id"
                @click="selectedExamId = exam.id"
                :class="[
                    'flex-shrink-0 rounded-lg px-4 py-2 text-sm font-medium transition-colors whitespace-nowrap',
                    selectedExamId === exam.id
                        ? 'bg-primary text-primary-foreground'
                        : 'bg-muted text-muted-foreground hover:bg-accent',
                ]"
            >
                {{ exam.name }}
            </button>
        </div>

        <!-- Results display -->
        <template v-if="selectedResult">
            <!-- Summary cards -->
            <div class="grid gap-4 sm:grid-cols-4">
                <div class="rounded-xl border border-border bg-card p-4">
                    <div class="flex items-center gap-2 text-sm text-muted-foreground mb-1">
                        <TrendingUp class="size-4" /> Overall
                    </div>
                    <div class="text-3xl font-bold text-primary">{{ selectedResult.percentage }}%</div>
                    <div class="text-xs text-muted-foreground mt-1">{{ selectedResult.total_obtained }} / {{ selectedResult.total_full }}</div>
                </div>
                <div class="rounded-xl border border-border bg-card p-4">
                    <div class="flex items-center gap-2 text-sm text-muted-foreground mb-1">
                        <BookOpen class="size-4" /> Subjects
                    </div>
                    <div class="text-3xl font-bold">{{ selectedResult.total_subjects }}</div>
                    <div class="text-xs text-muted-foreground mt-1">Total subjects</div>
                </div>
                <div class="rounded-xl border border-border bg-card p-4">
                    <div class="flex items-center gap-2 text-sm text-green-600 dark:text-green-400 mb-1">
                        <CheckCircle class="size-4" /> Passed
                    </div>
                    <div class="text-3xl font-bold text-green-600 dark:text-green-400">{{ selectedResult.passed_count }}</div>
                    <div class="text-xs text-muted-foreground mt-1">of {{ selectedResult.total_subjects }}</div>
                </div>
                <div class="rounded-xl border border-border bg-card p-4">
                    <div class="flex items-center gap-2 text-sm text-red-600 dark:text-red-400 mb-1">
                        <XCircle class="size-4" /> Failed
                    </div>
                    <div class="text-3xl font-bold text-red-600 dark:text-red-400">{{ selectedResult.total_subjects - selectedResult.passed_count }}</div>
                    <div class="text-xs text-muted-foreground mt-1">needs improvement</div>
                </div>
            </div>

            <!-- Subject-wise results table -->
            <div class="rounded-xl border border-border bg-card overflow-hidden">
                <div class="border-b border-border bg-muted/30 px-5 py-3">
                    <h3 class="font-semibold flex items-center gap-2">
                        <Trophy class="size-4 text-amber-500" />
                        {{ selectedExam?.name }} — Subject Breakdown
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-border text-sm text-muted-foreground">
                                <th class="px-5 py-3 text-left font-medium">Subject</th>
                                <th class="px-5 py-3 text-center font-medium">Marks</th>
                                <th class="px-5 py-3 text-center font-medium">Percentage</th>
                                <th class="px-5 py-3 text-center font-medium">Grade</th>
                                <th class="px-5 py-3 text-center font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(subj, i) in selectedResult.subjects"
                                :key="i"
                                class="border-b border-border last:border-0 transition-colors hover:bg-muted/20"
                            >
                                <td class="px-5 py-3.5 font-medium">{{ subj.subject }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="font-semibold">{{ subj.marks_obtained ?? '-' }}</span>
                                    <span class="text-muted-foreground"> / {{ subj.full_marks }}</span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3 justify-center">
                                        <div class="w-24 h-2 rounded-full bg-muted overflow-hidden">
                                            <div
                                                :class="['h-full rounded-full transition-all', percentageColor(subj.percentage ?? 0)]"
                                                :style="{ width: `${Math.min(subj.percentage ?? 0, 100)}%` }"
                                            />
                                        </div>
                                        <span class="text-sm font-medium w-12 text-right">{{ subj.percentage !== null ? `${Math.round(subj.percentage)}%` : '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span :class="['font-bold text-lg', gradeColor(subj.grade)]">{{ subj.grade ?? '-' }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <Badge :variant="subj.is_passing ? 'default' : 'destructive'" class="text-xs">
                                        {{ subj.is_passing ? 'Pass' : 'Fail' }}
                                    </Badge>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>

        <!-- No results -->
        <div v-else class="flex flex-col items-center justify-center py-16 text-center">
            <Trophy class="size-12 text-muted-foreground/30 mb-4" />
            <h3 class="text-lg font-semibold text-muted-foreground">No results yet</h3>
            <p class="mt-1 text-sm text-muted-foreground/60">Results will appear here once your exams are graded.</p>
        </div>
    </div>
</template>
