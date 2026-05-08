<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { ArrowLeft, CheckCircle, XCircle, Trophy, Clock, Target } from 'lucide-vue-next'
import { Badge } from '@/components/ui/badge'

interface AnswerOption {
    id: number
    text: string
    is_correct: boolean
}

interface AnswerResult {
    question: string
    type: 'mcq' | 'short' | 'true_false'
    max_marks: number
    answer: string | null
    is_correct: boolean
    marks: number
    options: AnswerOption[]
}

const props = defineProps<{
    quiz: { id: number; title: string; subject: string | null; total_marks: number | null }
    attempt: { score: number; started_at: string; submitted_at: string }
    answers: AnswerResult[]
}>()

const percentage = computed(() =>
    props.quiz.total_marks ? Math.round((props.attempt.score / props.quiz.total_marks) * 100) : 0,
)

const correctCount = computed(() => props.answers.filter(a => a.is_correct).length)

const grade = computed(() => {
    if (percentage.value >= 90) return { label: 'Excellent!', color: 'text-green-500', bg: 'bg-green-500/10' }
    if (percentage.value >= 75) return { label: 'Great Job!', color: 'text-blue-500', bg: 'bg-blue-500/10' }
    if (percentage.value >= 50) return { label: 'Good Effort', color: 'text-amber-500', bg: 'bg-amber-500/10' }
    return { label: 'Keep Trying', color: 'text-red-500', bg: 'bg-red-500/10' }
})

function getSelectedOptionText(answer: AnswerResult): string {
    if (!answer.answer) return 'No answer'
    const opt = answer.options.find(o => String(o.id) === answer.answer)
    return opt?.text ?? answer.answer
}

function getCorrectOptionText(answer: AnswerResult): string {
    const opt = answer.options.find(o => o.is_correct)
    return opt?.text ?? ''
}
</script>

<template>
    <Head :title="`Result: ${quiz.title}`" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <!-- Back -->
        <Link href="/student/quizzes" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition-colors w-fit">
            <ArrowLeft class="size-4" /> Back to Quizzes
        </Link>

        <!-- Score card -->
        <div class="rounded-2xl border border-border bg-card p-8 text-center">
            <div :class="['inline-flex size-20 items-center justify-center rounded-full mb-4', grade.bg]">
                <Trophy :class="['size-10', grade.color]" />
            </div>
            <h1 class="text-2xl font-bold mb-1">{{ grade.label }}</h1>
            <p class="text-sm text-muted-foreground mb-6">{{ quiz.title }}</p>

            <div class="flex items-center justify-center gap-8">
                <div class="text-center">
                    <div class="text-4xl font-bold text-primary">{{ attempt.score }}</div>
                    <div class="text-xs text-muted-foreground mt-1">out of {{ quiz.total_marks }}</div>
                </div>
                <div class="h-12 w-px bg-border" />
                <div class="text-center">
                    <div :class="['text-4xl font-bold', grade.color]">{{ percentage }}%</div>
                    <div class="text-xs text-muted-foreground mt-1">percentage</div>
                </div>
            </div>

            <div class="flex justify-center gap-6 mt-6 text-xs text-muted-foreground">
                <span class="flex items-center gap-1"><Clock class="size-3" /> Started: {{ attempt.started_at }}</span>
                <span class="flex items-center gap-1"><Target class="size-3" /> Submitted: {{ attempt.submitted_at }}</span>
            </div>
        </div>

        <!-- Answer review -->
        <div class="space-y-4">
            <h2 class="text-lg font-semibold">Answer Review</h2>

            <div
                v-for="(answer, i) in answers"
                :key="i"
                :class="[
                    'rounded-xl border-2 p-5 transition-colors',
                    answer.is_correct
                        ? 'border-green-200 bg-green-50/50 dark:border-green-800/30 dark:bg-green-950/10'
                        : 'border-red-200 bg-red-50/50 dark:border-red-800/30 dark:bg-red-950/10',
                ]"
            >
                <!-- Question header -->
                <div class="flex items-center justify-between mb-3">
                    <span class="flex items-center gap-2 text-sm font-medium">
                        <span class="flex items-center justify-center size-6 rounded-full bg-muted text-xs font-bold">{{ i + 1 }}</span>
                        <Badge variant="outline" class="text-[11px]">{{ answer.max_marks }} marks</Badge>
                        <Badge variant="outline" class="text-[11px] capitalize">{{ answer.type.replace('_', '/') }}</Badge>
                    </span>
                    <div class="flex items-center gap-1.5">
                        <CheckCircle v-if="answer.is_correct" class="size-5 text-green-500" />
                        <XCircle v-else class="size-5 text-red-500" />
                        <span :class="['text-sm font-semibold', answer.is_correct ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400']">
                            {{ answer.marks }}/{{ answer.max_marks }}
                        </span>
                    </div>
                </div>

                <!-- Question text -->
                <p class="font-medium mb-4">{{ answer.question }}</p>

                <!-- MCQ / True-False review -->
                <div v-if="answer.type === 'mcq' || answer.type === 'true_false'" class="space-y-2">
                    <div
                        v-for="opt in answer.options"
                        :key="opt.id"
                        :class="[
                            'rounded-lg px-4 py-2.5 text-sm border',
                            opt.is_correct
                                ? 'border-green-300 bg-green-100/50 dark:border-green-700/30 dark:bg-green-900/20'
                                : String(opt.id) === answer.answer && !answer.is_correct
                                    ? 'border-red-300 bg-red-100/50 dark:border-red-700/30 dark:bg-red-900/20'
                                    : 'border-transparent bg-muted/30',
                        ]"
                    >
                        <div class="flex items-center gap-2">
                            <CheckCircle v-if="opt.is_correct" class="size-4 text-green-500 shrink-0" />
                            <XCircle v-else-if="String(opt.id) === answer.answer" class="size-4 text-red-500 shrink-0" />
                            <span class="size-4 shrink-0" v-else />
                            <span>{{ opt.text }}</span>
                            <span v-if="String(opt.id) === answer.answer" class="ml-auto text-xs text-muted-foreground">(your answer)</span>
                            <span v-if="opt.is_correct" class="ml-auto text-xs text-green-600 dark:text-green-400">(correct)</span>
                        </div>
                    </div>
                </div>

                <!-- Short answer review -->
                <div v-if="answer.type === 'short'" class="space-y-2">
                    <div class="rounded-lg bg-muted/30 px-4 py-3 text-sm">
                        <span class="text-xs text-muted-foreground block mb-1">Your answer:</span>
                        {{ answer.answer || 'No answer provided' }}
                    </div>
                    <p class="text-xs text-muted-foreground">Short answers require manual grading by your teacher.</p>
                </div>
            </div>
        </div>
    </div>
</template>
