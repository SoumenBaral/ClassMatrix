<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { ClipboardList, Clock, CheckCircle, AlertCircle, Star, ChevronLeft, ChevronRight } from 'lucide-vue-next'
import { Badge } from '@/components/ui/badge'

interface Assignment {
    id: number
    title: string
    subject: string | null
    teacher: string | null
    due_date: string
    due_date_raw: string
    total_marks: number | null
    is_overdue: boolean
    submission_count: number
    status: 'pending' | 'submitted' | 'graded' | 'overdue'
    marks_obtained: number | null
}

interface PaginatedData {
    data: Assignment[]
    current_page: number
    last_page: number
    next_page_url: string | null
    prev_page_url: string | null
}

const props = defineProps<{
    assignments: PaginatedData
}>()

const activeFilter = ref<string>('all')

const filtered = computed(() => {
    if (activeFilter.value === 'all') return props.assignments.data
    return props.assignments.data.filter(a => a.status === activeFilter.value)
})

const statusConfig: Record<string, { label: string; variant: string; icon: any }> = {
    pending: { label: 'Pending', variant: 'outline', icon: Clock },
    submitted: { label: 'Submitted', variant: 'secondary', icon: CheckCircle },
    graded: { label: 'Graded', variant: 'default', icon: Star },
    overdue: { label: 'Overdue', variant: 'destructive', icon: AlertCircle },
}

const filters = [
    { key: 'all', label: 'All' },
    { key: 'pending', label: 'Pending' },
    { key: 'submitted', label: 'Submitted' },
    { key: 'graded', label: 'Graded' },
    { key: 'overdue', label: 'Overdue' },
]

function daysUntilDue(dateRaw: string): string {
    const diff = Math.ceil((new Date(dateRaw).getTime() - Date.now()) / (1000 * 60 * 60 * 24))
    if (diff < 0) return `${Math.abs(diff)}d overdue`
    if (diff === 0) return 'Due today'
    if (diff === 1) return 'Due tomorrow'
    return `${diff} days left`
}
</script>

<template>
    <Head title="Assignments" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div>
            <h1 class="text-2xl font-bold">Assignments</h1>
            <p class="mt-1 text-sm text-muted-foreground">Track and submit your assignments</p>
        </div>

        <!-- Filter tabs -->
        <div class="flex gap-2 overflow-x-auto pb-1">
            <button
                v-for="f in filters"
                :key="f.key"
                @click="activeFilter = f.key"
                :class="[
                    'rounded-lg px-4 py-2 text-sm font-medium transition-colors whitespace-nowrap',
                    activeFilter === f.key
                        ? 'bg-primary text-primary-foreground'
                        : 'bg-muted text-muted-foreground hover:bg-accent',
                ]"
            >
                {{ f.label }}
            </button>
        </div>

        <!-- Assignment cards -->
        <div v-if="filtered.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Link
                v-for="assignment in filtered"
                :key="assignment.id"
                :href="`/student/assignments/${assignment.id}`"
                class="group relative flex flex-col rounded-xl border border-border bg-card p-5 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg hover:border-border/80"
            >
                <!-- Subject badge -->
                <div class="flex items-center justify-between mb-3">
                    <Badge variant="secondary" class="text-xs">{{ assignment.subject ?? 'General' }}</Badge>
                    <Badge :variant="(statusConfig[assignment.status]?.variant as any) ?? 'outline'" class="text-xs">
                        {{ statusConfig[assignment.status]?.label }}
                    </Badge>
                </div>

                <!-- Title -->
                <h3 class="text-base font-semibold line-clamp-2 group-hover:text-primary transition-colors">
                    {{ assignment.title }}
                </h3>

                <!-- Meta -->
                <div class="mt-auto pt-4 flex items-center justify-between text-xs text-muted-foreground">
                    <div class="flex items-center gap-1">
                        <Clock class="size-3" />
                        <span>{{ assignment.due_date }}</span>
                    </div>
                    <div v-if="assignment.status === 'graded' && assignment.marks_obtained !== null" class="font-semibold text-primary">
                        {{ assignment.marks_obtained }}/{{ assignment.total_marks }}
                    </div>
                    <div v-else-if="assignment.total_marks" class="text-muted-foreground/60">
                        {{ assignment.total_marks }} marks
                    </div>
                </div>

                <!-- Due indicator -->
                <div v-if="assignment.status === 'pending'" :class="[
                    'mt-2 rounded-md px-2 py-1 text-center text-xs font-medium',
                    assignment.is_overdue ? 'bg-red-50 text-red-600 dark:bg-red-950/20 dark:text-red-400' : 'bg-blue-50 text-blue-600 dark:bg-blue-950/20 dark:text-blue-400',
                ]">
                    {{ daysUntilDue(assignment.due_date_raw) }}
                </div>
            </Link>
        </div>

        <!-- Empty state -->
        <div v-else class="flex flex-col items-center justify-center py-16 text-center">
            <ClipboardList class="size-12 text-muted-foreground/30 mb-4" />
            <h3 class="text-lg font-semibold text-muted-foreground">No assignments found</h3>
            <p class="mt-1 text-sm text-muted-foreground/60">
                {{ activeFilter === 'all' ? 'No assignments have been posted yet.' : `No ${activeFilter} assignments.` }}
            </p>
        </div>

        <!-- Pagination -->
        <div v-if="assignments.last_page > 1" class="flex items-center justify-center gap-2 pt-4">
            <button
                :disabled="!assignments.prev_page_url"
                @click="router.visit(assignments.prev_page_url!)"
                class="inline-flex items-center gap-1 rounded-lg border px-3 py-2 text-sm disabled:opacity-30"
            >
                <ChevronLeft class="size-4" /> Previous
            </button>
            <span class="text-sm text-muted-foreground">Page {{ assignments.current_page }} of {{ assignments.last_page }}</span>
            <button
                :disabled="!assignments.next_page_url"
                @click="router.visit(assignments.next_page_url!)"
                class="inline-flex items-center gap-1 rounded-lg border px-3 py-2 text-sm disabled:opacity-30"
            >
                Next <ChevronRight class="size-4" />
            </button>
        </div>
    </div>
</template>
