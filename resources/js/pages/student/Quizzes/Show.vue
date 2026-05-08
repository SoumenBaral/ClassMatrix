<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Clock, ChevronLeft, ChevronRight, Send, AlertTriangle } from 'lucide-vue-next'
import { Badge } from '@/components/ui/badge'

interface QuizOption {
    id: number
    text: string
}

interface QuizQuestion {
    id: number
    question: string
    type: 'mcq' | 'short' | 'true_false'
    marks: number
    options: QuizOption[]
}

const props = defineProps<{
    quiz: {
        id: number
        title: string
        subject: string | null
        duration_minutes: number | null
        total_marks: number | null
    }
    questions: QuizQuestion[]
    attempt: { id: number; started_at: string }
    existingAnswers: Record<string, string>
}>()

const currentIndex = ref(0)
const answers = ref<Record<number, string>>({})
const submitting = ref(false)
const showConfirm = ref(false)

// Load existing answers
onMounted(() => {
    for (const [qId, answer] of Object.entries(props.existingAnswers)) {
        answers.value[Number(qId)] = answer
    }
})

const currentQuestion = computed(() => props.questions[currentIndex.value])
const answeredCount = computed(() => Object.keys(answers.value).filter(k => answers.value[Number(k)]).length)
const progress = computed(() => Math.round((answeredCount.value / props.questions.length) * 100))

// Timer
const timeLeft = ref(0)
let timerInterval: ReturnType<typeof setInterval> | null = null

onMounted(() => {
    if (props.quiz.duration_minutes) {
        const started = new Date(props.attempt.started_at).getTime()
        const deadline = started + props.quiz.duration_minutes * 60 * 1000
        timeLeft.value = Math.max(0, Math.floor((deadline - Date.now()) / 1000))

        timerInterval = setInterval(() => {
            timeLeft.value = Math.max(0, timeLeft.value - 1)
            if (timeLeft.value === 0) {
                clearInterval(timerInterval!)
                submitQuiz()
            }
        }, 1000)
    }
})

onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval)
})

const formattedTime = computed(() => {
    const m = Math.floor(timeLeft.value / 60)
    const s = timeLeft.value % 60
    return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`
})

const timerUrgent = computed(() => timeLeft.value > 0 && timeLeft.value <= 60)

function setAnswer(questionId: number, value: string) {
    answers.value[questionId] = value
}

function submitQuiz() {
    submitting.value = true
    const payload = props.questions.map(q => ({
        question_id: q.id,
        answer: answers.value[q.id] ?? '',
    }))

    router.post(`/student/quizzes/${props.quiz.id}/submit`, { answers: payload }, {
        onFinish: () => { submitting.value = false },
    })
}
</script>

<template>
    <Head :title="`Quiz: ${quiz.title}`" />
    <div class="flex h-full flex-1 flex-col">
        <!-- Top bar -->
        <div class="sticky top-0 z-10 border-b border-border bg-card/95 backdrop-blur-sm px-6 py-3">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-lg font-bold line-clamp-1">{{ quiz.title }}</h1>
                    <div class="flex items-center gap-3 text-xs text-muted-foreground mt-0.5">
                        <Badge variant="secondary" class="text-[11px]">{{ quiz.subject }}</Badge>
                        <span>{{ quiz.total_marks }} marks</span>
                        <span>{{ answeredCount }}/{{ questions.length }} answered</span>
                    </div>
                </div>
                <!-- Timer -->
                <div v-if="quiz.duration_minutes" :class="[
                    'flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-mono font-bold',
                    timerUrgent ? 'bg-red-50 text-red-600 animate-pulse dark:bg-red-950/30 dark:text-red-400' : 'bg-muted text-foreground',
                ]">
                    <Clock class="size-4" />
                    {{ formattedTime }}
                </div>
            </div>
            <!-- Progress bar -->
            <div class="mt-2 h-1.5 rounded-full bg-muted overflow-hidden">
                <div class="h-full rounded-full bg-primary transition-all duration-300" :style="{ width: `${progress}%` }" />
            </div>
        </div>

        <div class="flex flex-1 overflow-hidden">
            <!-- Question navigator (desktop sidebar) -->
            <div class="hidden lg:flex w-56 flex-shrink-0 flex-col border-r border-border bg-muted/20 p-4">
                <div class="text-xs font-medium text-muted-foreground mb-3 uppercase tracking-wider">Questions</div>
                <div class="grid grid-cols-5 gap-1.5">
                    <button
                        v-for="(q, i) in questions"
                        :key="q.id"
                        @click="currentIndex = i"
                        :class="[
                            'flex items-center justify-center rounded-md size-9 text-xs font-medium transition-all',
                            currentIndex === i
                                ? 'bg-primary text-primary-foreground ring-2 ring-primary/30'
                                : answers[q.id]
                                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                    : 'bg-card border border-border text-muted-foreground hover:border-primary/30',
                        ]"
                    >
                        {{ i + 1 }}
                    </button>
                </div>
                <div class="mt-4 space-y-1.5 text-[11px] text-muted-foreground">
                    <div class="flex items-center gap-2"><span class="size-3 rounded bg-green-100 dark:bg-green-900/30" /> Answered</div>
                    <div class="flex items-center gap-2"><span class="size-3 rounded border border-border bg-card" /> Unanswered</div>
                    <div class="flex items-center gap-2"><span class="size-3 rounded bg-primary" /> Current</div>
                </div>
            </div>

            <!-- Question area -->
            <div class="flex-1 overflow-y-auto p-6 lg:p-8">
                <div class="mx-auto max-w-2xl">
                    <!-- Question header -->
                    <div class="flex items-center justify-between mb-6">
                        <span class="text-sm font-medium text-muted-foreground">
                            Question {{ currentIndex + 1 }} of {{ questions.length }}
                        </span>
                        <Badge variant="outline" class="text-xs">{{ currentQuestion.marks }} marks</Badge>
                    </div>

                    <!-- Question text -->
                    <h2 class="text-xl font-semibold leading-relaxed mb-8">
                        {{ currentQuestion.question }}
                    </h2>

                    <!-- MCQ / True-False options -->
                    <div v-if="currentQuestion.type === 'mcq' || currentQuestion.type === 'true_false'" class="space-y-3">
                        <button
                            v-for="option in currentQuestion.options"
                            :key="option.id"
                            @click="setAnswer(currentQuestion.id, String(option.id))"
                            :class="[
                                'w-full text-left rounded-xl border-2 px-5 py-4 transition-all duration-200',
                                answers[currentQuestion.id] === String(option.id)
                                    ? 'border-primary bg-primary/5 shadow-sm'
                                    : 'border-border hover:border-primary/30 hover:bg-muted/30',
                            ]"
                        >
                            <div class="flex items-center gap-3">
                                <div :class="[
                                    'flex items-center justify-center size-6 rounded-full border-2 transition-colors',
                                    answers[currentQuestion.id] === String(option.id)
                                        ? 'border-primary bg-primary'
                                        : 'border-muted-foreground/30',
                                ]">
                                    <div v-if="answers[currentQuestion.id] === String(option.id)" class="size-2 rounded-full bg-white" />
                                </div>
                                <span class="text-sm">{{ option.text }}</span>
                            </div>
                        </button>
                    </div>

                    <!-- Short answer -->
                    <div v-if="currentQuestion.type === 'short'">
                        <textarea
                            :value="answers[currentQuestion.id] ?? ''"
                            @input="setAnswer(currentQuestion.id, ($event.target as HTMLTextAreaElement).value)"
                            rows="5"
                            class="w-full rounded-xl border-2 border-border bg-background px-4 py-3 text-sm focus:outline-none focus:border-primary resize-none transition-colors"
                            placeholder="Type your answer here..."
                        />
                    </div>

                    <!-- Navigation -->
                    <div class="flex items-center justify-between mt-10 pt-6 border-t border-border">
                        <button
                            :disabled="currentIndex === 0"
                            @click="currentIndex--"
                            class="inline-flex items-center gap-2 rounded-lg border border-border px-4 py-2.5 text-sm font-medium transition-colors hover:bg-accent disabled:opacity-30"
                        >
                            <ChevronLeft class="size-4" /> Previous
                        </button>

                        <button
                            v-if="currentIndex < questions.length - 1"
                            @click="currentIndex++"
                            class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground transition-all hover:opacity-90"
                        >
                            Next <ChevronRight class="size-4" />
                        </button>

                        <button
                            v-else
                            @click="showConfirm = true"
                            class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-5 py-2.5 text-sm font-medium text-white transition-all hover:bg-green-700"
                        >
                            <Send class="size-4" /> Finish Quiz
                        </button>
                    </div>

                    <!-- Mobile question dots -->
                    <div class="flex justify-center gap-1.5 mt-6 lg:hidden flex-wrap">
                        <button
                            v-for="(q, i) in questions"
                            :key="q.id"
                            @click="currentIndex = i"
                            :class="[
                                'size-2.5 rounded-full transition-all',
                                currentIndex === i ? 'bg-primary scale-125' : answers[q.id] ? 'bg-green-400' : 'bg-muted-foreground/20',
                            ]"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit confirmation modal -->
        <Teleport to="body">
            <div v-if="showConfirm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
                <div class="mx-4 w-full max-w-md rounded-2xl border border-border bg-card p-6 shadow-2xl">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="flex size-10 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30">
                            <AlertTriangle class="size-5 text-amber-600 dark:text-amber-400" />
                        </div>
                        <h3 class="text-lg font-semibold">Submit Quiz?</h3>
                    </div>
                    <p class="text-sm text-muted-foreground mb-2">
                        You've answered <strong>{{ answeredCount }}</strong> of <strong>{{ questions.length }}</strong> questions.
                    </p>
                    <p v-if="answeredCount < questions.length" class="text-sm text-amber-600 dark:text-amber-400 mb-4">
                        {{ questions.length - answeredCount }} question(s) are unanswered and will receive 0 marks.
                    </p>
                    <div class="flex gap-3 justify-end mt-6">
                        <button @click="showConfirm = false" class="rounded-lg border border-border px-4 py-2 text-sm font-medium hover:bg-accent transition-colors">
                            Review Answers
                        </button>
                        <button
                            @click="submitQuiz()"
                            :disabled="submitting"
                            class="rounded-lg bg-green-600 px-5 py-2 text-sm font-medium text-white hover:bg-green-700 transition-colors disabled:opacity-50"
                        >
                            {{ submitting ? 'Submitting...' : 'Submit Quiz' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
