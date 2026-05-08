<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { ref } from 'vue'
import { BookOpen, FileText, Play, User } from 'lucide-vue-next'
import { Badge } from '@/components/ui/badge'

interface LessonItem {
    id: number
    title: string
    subject: string | null
    subject_code: string | null
    teacher: string | null
    has_video: boolean
    materials_count: number
    published_at: string
}

const props = defineProps<{
    lessons: Record<string, LessonItem[]>
    subjects: string[]
}>()

const activeSubject = ref<string | null>(props.subjects[0] ?? null)

const subjectColors: Record<string, string> = {}
const palette = [
    'from-blue-500 to-cyan-500',
    'from-purple-500 to-violet-500',
    'from-emerald-500 to-green-500',
    'from-orange-500 to-amber-500',
    'from-pink-500 to-rose-500',
    'from-indigo-500 to-blue-500',
    'from-teal-500 to-cyan-500',
]
props.subjects.forEach((s, i) => { subjectColors[s] = palette[i % palette.length] })
</script>

<template>
    <Head title="Lessons" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div>
            <h1 class="text-2xl font-bold">Lessons</h1>
            <p class="mt-1 text-sm text-muted-foreground">Study materials and lessons from your teachers</p>
        </div>

        <!-- Subject tabs -->
        <div v-if="subjects.length" class="flex gap-2 overflow-x-auto pb-1">
            <button
                v-for="subject in subjects"
                :key="subject"
                @click="activeSubject = subject"
                :class="[
                    'flex-shrink-0 rounded-lg px-4 py-2 text-sm font-medium transition-colors whitespace-nowrap',
                    activeSubject === subject
                        ? 'bg-primary text-primary-foreground'
                        : 'bg-muted text-muted-foreground hover:bg-accent',
                ]"
            >
                {{ subject }}
            </button>
        </div>

        <!-- Lesson cards for active subject -->
        <div v-if="activeSubject && lessons[activeSubject]?.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Link
                v-for="lesson in lessons[activeSubject]"
                :key="lesson.id"
                :href="`/student/lessons/${lesson.id}`"
                class="group flex flex-col rounded-xl border border-border bg-card overflow-hidden transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg"
            >
                <!-- Color banner -->
                <div :class="['h-2 bg-linear-to-r', subjectColors[activeSubject] ?? 'from-blue-500 to-cyan-500']" />

                <div class="flex flex-col flex-1 p-5">
                    <h3 class="text-base font-semibold line-clamp-2 group-hover:text-primary transition-colors">
                        {{ lesson.title }}
                    </h3>

                    <div class="mt-2 flex flex-wrap gap-2">
                        <Badge v-if="lesson.has_video" variant="secondary" class="text-[11px] gap-1">
                            <Play class="size-3" /> Video
                        </Badge>
                        <Badge v-if="lesson.materials_count > 0" variant="outline" class="text-[11px] gap-1">
                            <FileText class="size-3" /> {{ lesson.materials_count }} file{{ lesson.materials_count > 1 ? 's' : '' }}
                        </Badge>
                    </div>

                    <div class="mt-auto pt-4 flex items-center justify-between text-xs text-muted-foreground">
                        <span class="flex items-center gap-1"><User class="size-3" /> {{ lesson.teacher }}</span>
                        <span>{{ lesson.published_at }}</span>
                    </div>
                </div>
            </Link>
        </div>

        <!-- Empty state -->
        <div v-if="!subjects.length" class="flex flex-col items-center justify-center py-16 text-center">
            <BookOpen class="size-12 text-muted-foreground/30 mb-4" />
            <h3 class="text-lg font-semibold text-muted-foreground">No lessons available</h3>
            <p class="mt-1 text-sm text-muted-foreground/60">Lessons will appear here once your teachers publish them.</p>
        </div>
    </div>
</template>
