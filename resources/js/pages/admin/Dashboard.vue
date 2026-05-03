<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowRight, BookOpen, Building2, GraduationCap,
    Layers, LayoutGrid, TrendingUp, Users,
} from 'lucide-vue-next';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import Heading from '@/components/Heading.vue';

const props = defineProps<{
    stats: {
        totalStudents: number;
        totalTeachers: number;
        totalClasses: number;
        totalSections: number;
        totalSubjects: number;
        totalDepartments: number;
    };
    currentAcademicYear: { id: number; name: string } | null;
}>();

const statCards = [
    { label: 'Students', value: props.stats.totalStudents, icon: GraduationCap, gradient: 'from-blue-500 to-cyan-500', bg: 'bg-blue-500/10' },
    { label: 'Teachers', value: props.stats.totalTeachers, icon: Users, gradient: 'from-green-500 to-emerald-500', bg: 'bg-green-500/10' },
    { label: 'Classes', value: props.stats.totalClasses, icon: Layers, gradient: 'from-purple-500 to-violet-500', bg: 'bg-purple-500/10' },
    { label: 'Sections', value: props.stats.totalSections, icon: LayoutGrid, gradient: 'from-orange-500 to-amber-500', bg: 'bg-orange-500/10' },
    { label: 'Subjects', value: props.stats.totalSubjects, icon: BookOpen, gradient: 'from-pink-500 to-rose-500', bg: 'bg-pink-500/10' },
    { label: 'Departments', value: props.stats.totalDepartments, icon: Building2, gradient: 'from-teal-500 to-cyan-500', bg: 'bg-teal-500/10' },
];

const quickLinks = [
    { title: 'Students', desc: 'Manage student profiles', href: '/admin/students', icon: GraduationCap, color: 'text-blue-500' },
    { title: 'Teachers', desc: 'Manage staff accounts', href: '/admin/staff', icon: Users, color: 'text-green-500' },
    { title: 'Attendance', desc: 'Mark daily attendance', href: '/admin/attendance', icon: LayoutGrid, color: 'text-purple-500' },
    { title: 'Exams', desc: 'Manage examinations', href: '/admin/exams', icon: BookOpen, color: 'text-orange-500' },
    { title: 'Timetable', desc: 'Schedule classes', href: '/admin/timetable', icon: Layers, color: 'text-pink-500' },
    { title: 'Invoices', desc: 'Fee collection', href: '/admin/invoices', icon: TrendingUp, color: 'text-teal-500' },
];
</script>

<template>
    <Head title="Admin Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <!-- Header with greeting -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Dashboard</h1>
                <p class="text-sm text-muted-foreground mt-1">Welcome to ClassMatrix admin panel.</p>
            </div>
            <div v-if="currentAcademicYear" class="flex items-center gap-2 rounded-xl border border-primary/20 bg-primary/5 px-4 py-2 text-sm font-medium text-primary">
                <LayoutGrid class="size-4" />
                {{ currentAcademicYear.name }}
            </div>
        </div>

        <!-- Stat Cards with animation -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
            <Card v-for="(stat, i) in statCards" :key="stat.label"
                class="card-hover overflow-hidden border-0 shadow-sm"
                :style="{ animationDelay: `${i * 80}ms` }">
                <CardContent class="relative p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">{{ stat.label }}</p>
                            <p class="mt-2 text-3xl font-bold animate-count-up">{{ stat.value }}</p>
                        </div>
                        <div :class="['rounded-xl p-2.5', stat.bg]">
                            <component :is="stat.icon" :class="['size-5 bg-gradient-to-br bg-clip-text', stat.gradient]" style="-webkit-text-fill-color: transparent" />
                            <component :is="stat.icon" :class="['size-5 absolute opacity-0']" />
                        </div>
                    </div>
                    <!-- Subtle gradient bar at bottom -->
                    <div :class="['absolute bottom-0 left-0 h-0.5 w-full bg-gradient-to-r', stat.gradient]" />
                </CardContent>
            </Card>
        </div>

        <!-- Quick Actions Grid -->
        <div>
            <h2 class="mb-4 text-lg font-semibold">Quick Actions</h2>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <Link v-for="link in quickLinks" :key="link.title" :href="link.href"
                    class="group flex items-center gap-4 rounded-xl border border-border bg-card p-4 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md hover:border-primary/20">
                    <div class="flex size-11 items-center justify-center rounded-xl bg-muted transition-colors group-hover:bg-primary/10">
                        <component :is="link.icon" :class="['size-5', link.color]" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-medium text-sm">{{ link.title }}</div>
                        <div class="text-xs text-muted-foreground">{{ link.desc }}</div>
                    </div>
                    <ArrowRight class="size-4 text-muted-foreground opacity-0 transition-all group-hover:opacity-100 group-hover:translate-x-0.5" />
                </Link>
            </div>
        </div>

        <!-- System Info -->
        <Card class="border-0 shadow-sm">
            <CardHeader>
                <CardTitle class="text-base">System Overview</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="rounded-xl bg-muted/50 p-4">
                        <p class="text-xs text-muted-foreground">Academic Year</p>
                        <p class="mt-1 text-lg font-semibold">{{ currentAcademicYear?.name ?? 'Not set' }}</p>
                    </div>
                    <div class="rounded-xl bg-muted/50 p-4">
                        <p class="text-xs text-muted-foreground">Active Students</p>
                        <p class="mt-1 text-lg font-semibold">{{ stats.totalStudents }}</p>
                    </div>
                    <div class="rounded-xl bg-muted/50 p-4">
                        <p class="text-xs text-muted-foreground">Total Staff</p>
                        <p class="mt-1 text-lg font-semibold">{{ stats.totalTeachers }}</p>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
