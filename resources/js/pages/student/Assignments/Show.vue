<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ArrowLeft, Calendar, CheckCircle2, Clock, FileText, MessageSquare, Paperclip, Send, Star, Upload, User } from 'lucide-vue-next'
import { Badge } from '@/components/ui/badge'
import InputError from '@/components/InputError.vue'

interface AssignmentData {
    id: number
    title: string
    description: string | null
    subject: string | null
    teacher: string | null
    due_date: string
    is_overdue: boolean
    total_marks: number | null
    attachment: string | null
}

interface SubmissionData {
    id: number
    submitted_at: string
    file: string | null
    comment: string | null
    marks: number | null
    feedback: string | null
    graded_at: string | null
    is_graded: boolean
}

const props = defineProps<{
    assignment: AssignmentData
    submission: SubmissionData | null
}>()

const form = useForm({
    comment: '',
    file: null as File | null,
})

function submit() {
    form.post(`/student/assignments/${props.assignment.id}/submit`, {
        forceFormData: true,
    })
}
</script>

<template>
    <Head :title="assignment.title" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <!-- Back link -->
        <Link href="/student/assignments" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition-colors w-fit">
            <ArrowLeft class="size-4" /> Back to Assignments
        </Link>

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Main content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Assignment details -->
                <div class="rounded-xl border border-border bg-card p-6">
                    <div class="flex flex-wrap items-center gap-2 mb-4">
                        <Badge variant="secondary">{{ assignment.subject ?? 'General' }}</Badge>
                        <Badge v-if="assignment.is_overdue && !submission" variant="destructive">Overdue</Badge>
                        <Badge v-if="submission?.is_graded" variant="default">Graded</Badge>
                        <Badge v-else-if="submission" variant="secondary">Submitted</Badge>
                    </div>

                    <h1 class="text-2xl font-bold">{{ assignment.title }}</h1>

                    <div class="mt-4 flex flex-wrap gap-4 text-sm text-muted-foreground">
                        <span class="flex items-center gap-1.5"><User class="size-4" /> {{ assignment.teacher }}</span>
                        <span class="flex items-center gap-1.5"><Calendar class="size-4" /> Due: {{ assignment.due_date }}</span>
                        <span v-if="assignment.total_marks" class="flex items-center gap-1.5"><Star class="size-4" /> {{ assignment.total_marks }} marks</span>
                    </div>

                    <div v-if="assignment.description" class="mt-6 prose prose-sm dark:prose-invert max-w-none text-muted-foreground leading-relaxed whitespace-pre-wrap">
                        {{ assignment.description }}
                    </div>

                    <div v-if="assignment.attachment" class="mt-4">
                        <a :href="`/storage/${assignment.attachment}`" target="_blank" class="inline-flex items-center gap-2 rounded-lg border border-border bg-muted/50 px-4 py-2 text-sm hover:bg-muted transition-colors">
                            <Paperclip class="size-4" /> View Attachment
                        </a>
                    </div>
                </div>

                <!-- Submission form (if not yet submitted) -->
                <div v-if="!submission" class="rounded-xl border border-border bg-card p-6">
                    <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
                        <Send class="size-5" /> Submit Your Work
                    </h2>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1.5">Comment</label>
                            <textarea
                                v-model="form.comment"
                                rows="4"
                                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring resize-none"
                                placeholder="Add notes about your submission..."
                            />
                            <InputError :message="form.errors.comment" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1.5">Attachment</label>
                            <div class="flex items-center gap-3">
                                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-dashed border-border px-4 py-3 text-sm text-muted-foreground transition-colors hover:border-primary hover:text-primary">
                                    <Upload class="size-4" />
                                    {{ form.file ? form.file.name : 'Choose file' }}
                                    <input type="file" class="hidden" @change="form.file = ($event.target as HTMLInputElement).files?.[0] ?? null" />
                                </label>
                            </div>
                            <InputError :message="form.errors.file" />
                        </div>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-2 rounded-lg bg-primary px-6 py-2.5 text-sm font-medium text-primary-foreground transition-all hover:opacity-90 disabled:opacity-50"
                        >
                            <Send class="size-4" /> {{ form.processing ? 'Submitting...' : 'Submit Assignment' }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Sidebar: Submission status -->
            <div class="space-y-4">
                <!-- Submission info -->
                <div v-if="submission" class="rounded-xl border border-border bg-card p-5">
                    <h3 class="font-semibold mb-4 flex items-center gap-2">
                        <CheckCircle2 class="size-5 text-green-500" /> Submission
                    </h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground">Submitted</span>
                            <span class="font-medium">{{ submission.submitted_at }}</span>
                        </div>
                        <div v-if="submission.file">
                            <a :href="`/storage/${submission.file}`" target="_blank" class="inline-flex items-center gap-1.5 text-primary hover:underline">
                                <FileText class="size-3.5" /> View uploaded file
                            </a>
                        </div>
                        <div v-if="submission.comment" class="border-t border-border pt-3">
                            <span class="text-xs text-muted-foreground block mb-1">Your comment:</span>
                            <p class="text-muted-foreground">{{ submission.comment }}</p>
                        </div>
                    </div>
                </div>

                <!-- Grade card -->
                <div v-if="submission?.is_graded" class="rounded-xl border border-border bg-card p-5">
                    <h3 class="font-semibold mb-4 flex items-center gap-2">
                        <Star class="size-5 text-amber-500" /> Grade
                    </h3>
                    <div class="text-center py-2">
                        <div class="text-4xl font-bold text-primary">{{ submission.marks }}</div>
                        <div class="text-sm text-muted-foreground mt-1">out of {{ assignment.total_marks }}</div>
                        <div v-if="assignment.total_marks" class="mt-3 h-2 rounded-full bg-muted overflow-hidden">
                            <div
                                class="h-full rounded-full bg-primary transition-all"
                                :style="{ width: `${Math.min((submission.marks! / assignment.total_marks) * 100, 100)}%` }"
                            />
                        </div>
                    </div>
                    <div v-if="submission.feedback" class="mt-4 border-t border-border pt-3">
                        <span class="text-xs text-muted-foreground block mb-1">Teacher feedback:</span>
                        <p class="text-sm text-muted-foreground whitespace-pre-wrap">{{ submission.feedback }}</p>
                    </div>
                    <div class="text-xs text-muted-foreground mt-3">
                        Graded on {{ submission.graded_at }}
                    </div>
                </div>

                <!-- Status card (no submission yet) -->
                <div v-if="!submission" class="rounded-xl border border-border bg-card p-5">
                    <h3 class="font-semibold mb-3 flex items-center gap-2">
                        <Clock class="size-5 text-muted-foreground" /> Status
                    </h3>
                    <div :class="[
                        'rounded-lg px-4 py-3 text-center text-sm font-medium',
                        assignment.is_overdue
                            ? 'bg-red-50 text-red-600 dark:bg-red-950/20 dark:text-red-400'
                            : 'bg-blue-50 text-blue-600 dark:bg-blue-950/20 dark:text-blue-400',
                    ]">
                        {{ assignment.is_overdue ? 'Past due date' : 'Awaiting submission' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
