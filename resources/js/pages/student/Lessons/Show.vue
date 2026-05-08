<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { ArrowLeft, BookOpen, Calendar, Download, FileText, Play, User } from 'lucide-vue-next'
import { Badge } from '@/components/ui/badge'

interface Material {
    id: number
    name: string
    file_path: string
    type: string | null
}

const props = defineProps<{
    lesson: {
        id: number
        title: string
        subject: string | null
        teacher: string | null
        content: string | null
        video_url: string | null
        published_at: string
    }
    materials: Material[]
}>()

function getEmbedUrl(url: string): string | null {
    // YouTube
    const ytMatch = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/)
    if (ytMatch) return `https://www.youtube.com/embed/${ytMatch[1]}`
    // Vimeo
    const vmMatch = url.match(/vimeo\.com\/(\d+)/)
    if (vmMatch) return `https://player.vimeo.com/video/${vmMatch[1]}`
    return null
}

function fileIcon(type: string | null): string {
    if (!type) return '📄'
    if (type.includes('pdf')) return '📕'
    if (type.includes('image')) return '🖼️'
    if (type.includes('video')) return '🎬'
    if (type.includes('audio')) return '🎵'
    if (type.includes('zip') || type.includes('rar')) return '📦'
    return '📄'
}
</script>

<template>
    <Head :title="lesson.title" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <!-- Back -->
        <Link href="/student/lessons" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition-colors w-fit">
            <ArrowLeft class="size-4" /> Back to Lessons
        </Link>

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Main content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Lesson header -->
                <div class="rounded-xl border border-border bg-card p-6">
                    <div class="flex flex-wrap items-center gap-2 mb-3">
                        <Badge variant="secondary">{{ lesson.subject }}</Badge>
                    </div>
                    <h1 class="text-2xl font-bold">{{ lesson.title }}</h1>
                    <div class="mt-3 flex flex-wrap gap-4 text-sm text-muted-foreground">
                        <span class="flex items-center gap-1.5"><User class="size-4" /> {{ lesson.teacher }}</span>
                        <span class="flex items-center gap-1.5"><Calendar class="size-4" /> {{ lesson.published_at }}</span>
                    </div>
                </div>

                <!-- Video -->
                <div v-if="lesson.video_url" class="rounded-xl border border-border bg-card overflow-hidden">
                    <div v-if="getEmbedUrl(lesson.video_url)" class="aspect-video">
                        <iframe
                            :src="getEmbedUrl(lesson.video_url)!"
                            class="h-full w-full"
                            allowfullscreen
                            frameborder="0"
                        />
                    </div>
                    <div v-else class="p-4">
                        <a :href="lesson.video_url" target="_blank" class="inline-flex items-center gap-2 text-sm text-primary hover:underline">
                            <Play class="size-4" /> Watch Video
                        </a>
                    </div>
                </div>

                <!-- Content -->
                <div v-if="lesson.content" class="rounded-xl border border-border bg-card p-6">
                    <div class="prose prose-sm dark:prose-invert max-w-none leading-relaxed whitespace-pre-wrap">
                        {{ lesson.content }}
                    </div>
                </div>
            </div>

            <!-- Sidebar: Materials -->
            <div class="space-y-4">
                <div class="rounded-xl border border-border bg-card p-5">
                    <h3 class="font-semibold mb-4 flex items-center gap-2">
                        <FileText class="size-5" /> Materials
                    </h3>

                    <div v-if="materials.length" class="space-y-2">
                        <a
                            v-for="material in materials"
                            :key="material.id"
                            :href="`/storage/${material.file_path}`"
                            target="_blank"
                            class="flex items-center gap-3 rounded-lg border border-border p-3 text-sm transition-colors hover:bg-muted/50"
                        >
                            <span class="text-lg">{{ fileIcon(material.type) }}</span>
                            <div class="flex-1 min-w-0">
                                <div class="font-medium truncate">{{ material.name }}</div>
                                <div v-if="material.type" class="text-xs text-muted-foreground">{{ material.type }}</div>
                            </div>
                            <Download class="size-4 text-muted-foreground shrink-0" />
                        </a>
                    </div>

                    <div v-else class="text-center py-6">
                        <BookOpen class="size-8 text-muted-foreground/30 mx-auto mb-2" />
                        <p class="text-sm text-muted-foreground/60">No materials attached</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
