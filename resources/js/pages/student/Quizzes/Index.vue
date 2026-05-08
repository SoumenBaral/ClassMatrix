<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { Brain, Clock, HelpCircle, Star, CheckCircle2, AlertCircle, Timer, Lock } from 'lucide-vue-next'
import { Badge } from '@/components/ui/badge'

interface QuizItem {
    id: number
    title: string
    subject: string | null
    duration_minutes: number | null
    total_marks: number | null
    questions_count: number
    available_from: string | null
    available_until: string | null
    status: 'available' | 'completed' | 'in_progress' | 'upcoming' | 'expired'
    score: number | null
    attempt_id: number | null
}

defineProps<{
    quizzes: QuizItem[]
}>()

const statusConfig: Record<string, { label: string; variant: string; icon: any; color: string }> = {
    available: { label: 'Available', variant: 'default', icon: CheckCircle2, color: 'border-l-green-500' },
    completed: { label: 'Completed', variant: 'secondary', icon: Star, color: 'border-l-blue-500' },
    in_progress: { label: 'In Progress', variant: 'outline', icon: Timer, color: 'border-l-amber-500' },
    upcoming: { label: 'Upcoming', variant: 'outline', icon: Clock, color: 'border-l-gray-400' },
    expired: { label: 'Expired', variant: 'destructive', icon: Lock, color: 'border-l-red-400' },
}

function canTake(quiz: QuizItem): boolean {
    return quiz.status === 'available' || quiz.status === 'in_progress'
}

function quizLink(quiz: QuizItem): string {
    if (quiz.status === 'completed') return `/student/quizzes/${quiz.id}/result`
    if (canTake(quiz)) return `/student/quizzes/${quiz.id}`
    return '#'
}
</script>

<template>
    <Head title="Quizzes" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div>
            <h1 class="text-2xl font-bold">Quizzes</h1>
            <p class="mt-1 text-sm text-muted-foreground">Take quizzes and test your knowledge</p>
        </div>

        <!-- Quiz cards -->
        <div v-if="quizzes.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <component
                :is="canTake(quiz) || quiz.status === 'completed' ? Link : 'div'"
                v-for="quiz in quizzes"
                :key="quiz.id"
                :href="quizLink(quiz)"
                :class="[
                    'group relative flex flex-col rounded-xl border border-border bg-card p-5 transition-all duration-200 border-l-3',
                    statusConfig[quiz.status]?.color,
                    canTake(quiz) || quiz.status === 'completed'
                        ? 'hover:-translate-y-0.5 hover:shadow-lg cursor-pointer'
                        : 'opacity-70',
                ]"
            >
                <!-- Header -->
                <div class="flex items-center justify-between mb-3">
                    <Badge variant="secondary" class="text-xs">{{ quiz.subject ?? 'General' }}</Badge>
                    <Badge :variant="(statusConfig[quiz.status]?.variant as any) ?? 'outline'" class="text-xs">
                        <component :is="statusConfig[quiz.status]?.icon" class="size-3 mr-1" />
                        {{ statusConfig[quiz.status]?.label }}
                    </Badge>
                </div>

                <!-- Title -->
                <h3 class="text-base font-semibold line-clamp-2 group-hover:text-primary transition-colors">
                    {{ quiz.title }}
                </h3>

                <!-- Meta -->
                <div class="mt-auto pt-4 grid grid-cols-3 gap-2 text-xs text-muted-foreground">
                    <div class="flex items-center gap-1">
                        <HelpCircle class="size-3" />
                        {{ quiz.questions_count }} Q
                    </div>
                    <div class="flex items-center gap-1">
                        <Clock class="size-3" />
                        {{ quiz.duration_minutes ?? '∞' }} min
                    </div>
                    <div class="flex items-center gap-1">
                        <Star class="size-3" />
                        {{ quiz.total_marks }} marks
                    </div>
                </div>

                <!-- Score (if completed) -->
                <div v-if="quiz.status === 'completed' && quiz.score !== null" class="mt-3 rounded-lg bg-primary/5 px-3 py-2 text-center">
                    <span class="text-sm font-semibold text-primary">Score: {{ quiz.score }} / {{ quiz.total_marks }}</span>
                    <span class="text-xs text-muted-foreground ml-1">
                        ({{ quiz.total_marks ? Math.round((quiz.score / quiz.total_marks) * 100) : 0 }}%)
                    </span>
                </div>

                <!-- Availability -->
                <div v-if="quiz.available_from || quiz.available_until" class="mt-2 text-[11px] text-muted-foreground/60">
                    <span v-if="quiz.available_from">From: {{ quiz.available_from }}</span>
                    <span v-if="quiz.available_until" class="ml-2">Until: {{ quiz.available_until }}</span>
                </div>
            </component>
        </div>

        <!-- Empty state -->
        <div v-else class="flex flex-col items-center justify-center py-16 text-center">
            <Brain class="size-12 text-muted-foreground/30 mb-4" />
            <h3 class="text-lg font-semibold text-muted-foreground">No quizzes available</h3>
            <p class="mt-1 text-sm text-muted-foreground/60">Quizzes will appear here when your teachers create them.</p>
        </div>
    </div>
</template>
