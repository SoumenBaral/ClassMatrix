<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, Bot, Calendar, ClipboardCheck, GraduationCap, Sparkles, TrendingUp } from 'lucide-vue-next';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

const props = defineProps<{
    student: { name: string; class: string; section: string; roll_no: string } | null;
    stats: {
        attendanceRate: number;
        upcomingAssignments: number;
        recentMarks: { subject: string; exam: string; marks: number; total: number; grade: string }[];
        hasRoutine: boolean;
        chatCount: number;
    };
}>();

function gradeColor(grade: string): string {
    if (!grade) return 'secondary';
    if (grade.startsWith('A')) return 'default';
    if (grade.startsWith('B')) return 'secondary';
    return 'outline';
}

function attendanceColor(rate: number): string {
    if (rate >= 90) return 'text-green-500';
    if (rate >= 75) return 'text-yellow-500';
    return 'text-red-500';
}
</script>

<template>
    <Head title="Student Dashboard" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <!-- Welcome banner -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-700 p-6 text-white shadow-lg lg:p-8">
            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.4) 1px, transparent 0); background-size: 24px 24px;" />
            <div class="absolute top-0 right-0 h-48 w-48 rounded-full bg-white/10 blur-3xl" />
            <div class="relative z-10">
                <h1 class="text-2xl font-bold lg:text-3xl animate-fade-in-up">
                    Welcome back, {{ student?.name?.split(' ')[0] ?? 'Student' }}!
                </h1>
                <p class="mt-2 text-white/70 text-sm animate-fade-in-up" style="animation-delay: 100ms">
                    {{ student ? `${student.class} - Section ${student.section} | Roll No: ${student.roll_no}` : 'Your learning journey continues' }}
                </p>
                <div class="mt-4 flex flex-wrap gap-3 animate-fade-in-up" style="animation-delay: 200ms">
                    <Link href="/student/chat" class="inline-flex items-center gap-2 rounded-lg bg-white/20 px-4 py-2 text-sm font-medium backdrop-blur-sm transition-colors hover:bg-white/30">
                        <Bot class="size-4" /> Chat with AI Teacher
                    </Link>
                    <Link href="/student/routines" class="inline-flex items-center gap-2 rounded-lg bg-white/10 px-4 py-2 text-sm font-medium backdrop-blur-sm transition-colors hover:bg-white/20">
                        <Calendar class="size-4" /> My Routine
                    </Link>
                </div>
            </div>
        </div>

        <!-- Stat cards -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Card class="card-hover border-0 shadow-sm">
                <CardContent class="flex items-center gap-4 p-4">
                    <div class="rounded-xl bg-green-500/10 p-3">
                        <ClipboardCheck class="size-5 text-green-500" />
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground">Attendance</p>
                        <p :class="['text-2xl font-bold', attendanceColor(stats.attendanceRate)]">{{ stats.attendanceRate }}%</p>
                    </div>
                </CardContent>
            </Card>
            <Card class="card-hover border-0 shadow-sm">
                <CardContent class="flex items-center gap-4 p-4">
                    <div class="rounded-xl bg-blue-500/10 p-3">
                        <GraduationCap class="size-5 text-blue-500" />
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground">Assignments Due</p>
                        <p class="text-2xl font-bold">{{ stats.upcomingAssignments }}</p>
                    </div>
                </CardContent>
            </Card>
            <Card class="card-hover border-0 shadow-sm">
                <CardContent class="flex items-center gap-4 p-4">
                    <div class="rounded-xl bg-purple-500/10 p-3">
                        <Calendar class="size-5 text-purple-500" />
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground">Study Routine</p>
                        <p class="text-2xl font-bold">{{ stats.hasRoutine ? 'Active' : 'Not Set' }}</p>
                    </div>
                </CardContent>
            </Card>
            <Card class="card-hover border-0 shadow-sm">
                <CardContent class="flex items-center gap-4 p-4">
                    <div class="rounded-xl bg-orange-500/10 p-3">
                        <Bot class="size-5 text-orange-500" />
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground">AI Chats</p>
                        <p class="text-2xl font-bold">{{ stats.chatCount }}</p>
                    </div>
                </CardContent>
            </Card>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Recent marks -->
            <Card class="border-0 shadow-sm">
                <CardHeader class="flex flex-row items-center justify-between">
                    <CardTitle class="text-base">Recent Results</CardTitle>
                    <TrendingUp class="size-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div v-if="stats.recentMarks.length" class="space-y-3">
                        <div v-for="(mark, idx) in stats.recentMarks" :key="idx"
                            class="flex items-center justify-between rounded-xl border p-3 transition-colors hover:bg-muted/50 animate-fade-in-up"
                            :style="{ animationDelay: `${idx * 80}ms` }">
                            <div>
                                <div class="font-medium text-sm">{{ mark.subject }}</div>
                                <div class="text-xs text-muted-foreground">{{ mark.exam }}</div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="font-mono text-sm font-medium">{{ mark.marks }}/{{ mark.total }}</span>
                                <Badge :variant="gradeColor(mark.grade) as any">{{ mark.grade ?? '-' }}</Badge>
                            </div>
                        </div>
                    </div>
                    <div v-else class="flex flex-col items-center py-8 text-center">
                        <GraduationCap class="size-10 text-muted-foreground/20 mb-3" />
                        <p class="text-sm text-muted-foreground">No recent results</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Quick actions -->
            <Card class="border-0 shadow-sm">
                <CardHeader>
                    <CardTitle class="text-base">Quick Actions</CardTitle>
                </CardHeader>
                <CardContent class="grid gap-3">
                    <Link href="/student/routines"
                        class="group flex items-center gap-4 rounded-xl border p-4 transition-all hover:-translate-y-0.5 hover:shadow-md hover:border-purple-200 dark:hover:border-purple-800">
                        <div class="rounded-xl bg-purple-500/10 p-2.5">
                            <Calendar class="size-5 text-purple-500" />
                        </div>
                        <div class="flex-1">
                            <div class="font-medium text-sm">My Study Routine</div>
                            <div class="text-xs text-muted-foreground">View or generate your AI-powered plan</div>
                        </div>
                        <ArrowRight class="size-4 text-muted-foreground opacity-0 group-hover:opacity-100 transition-opacity" />
                    </Link>
                    <Link href="/student/chat"
                        class="group flex items-center gap-4 rounded-xl border p-4 transition-all hover:-translate-y-0.5 hover:shadow-md hover:border-orange-200 dark:hover:border-orange-800">
                        <div class="rounded-xl bg-gradient-to-br from-orange-500 to-amber-500 p-2.5 text-white">
                            <Bot class="size-5" />
                        </div>
                        <div class="flex-1">
                            <div class="font-medium text-sm">AI Personal Teacher</div>
                            <div class="text-xs text-muted-foreground">Ask questions, get explanations, solve problems</div>
                        </div>
                        <ArrowRight class="size-4 text-muted-foreground opacity-0 group-hover:opacity-100 transition-opacity" />
                    </Link>
                    <Link href="/student/routines/preferences"
                        class="group flex items-center gap-4 rounded-xl border p-4 transition-all hover:-translate-y-0.5 hover:shadow-md hover:border-green-200 dark:hover:border-green-800">
                        <div class="rounded-xl bg-green-500/10 p-2.5">
                            <Sparkles class="size-5 text-green-500" />
                        </div>
                        <div class="flex-1">
                            <div class="font-medium text-sm">Study Preferences</div>
                            <div class="text-xs text-muted-foreground">Set your schedule, goals, and priorities</div>
                        </div>
                        <ArrowRight class="size-4 text-muted-foreground opacity-0 group-hover:opacity-100 transition-opacity" />
                    </Link>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
