<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    BookOpen,
    Building2,
    GraduationCap,
    Layers,
    LayoutGrid,
    Users,
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
    { label: 'Students', value: props.stats.totalStudents, icon: GraduationCap, color: 'text-blue-600 bg-blue-50 dark:bg-blue-950' },
    { label: 'Teachers', value: props.stats.totalTeachers, icon: Users, color: 'text-green-600 bg-green-50 dark:bg-green-950' },
    { label: 'Classes', value: props.stats.totalClasses, icon: Layers, color: 'text-purple-600 bg-purple-50 dark:bg-purple-950' },
    { label: 'Sections', value: props.stats.totalSections, icon: LayoutGrid, color: 'text-orange-600 bg-orange-50 dark:bg-orange-950' },
    { label: 'Subjects', value: props.stats.totalSubjects, icon: BookOpen, color: 'text-red-600 bg-red-50 dark:bg-red-950' },
    { label: 'Departments', value: props.stats.totalDepartments, icon: Building2, color: 'text-teal-600 bg-teal-50 dark:bg-teal-950' },
];
</script>

<template>
    <Head title="Admin Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <Heading title="Dashboard" description="Welcome to ClassMatrix admin panel." />
            <div v-if="currentAcademicYear" class="rounded-lg bg-primary/10 px-4 py-2 text-sm font-medium">
                Academic Year: {{ currentAcademicYear.name }}
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Card v-for="stat in statCards" :key="stat.label">
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">
                        {{ stat.label }}
                    </CardTitle>
                    <div :class="['rounded-lg p-2', stat.color]">
                        <component :is="stat.icon" class="size-4" />
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="text-3xl font-bold">{{ stat.value }}</div>
                </CardContent>
            </Card>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle>Quick Actions</CardTitle>
                </CardHeader>
                <CardContent class="grid gap-2 sm:grid-cols-2">
                    <a href="/admin/academic-years" class="flex items-center gap-3 rounded-lg border p-3 text-sm hover:bg-accent transition-colors">
                        <Layers class="size-4 text-muted-foreground" />
                        Manage Academic Years
                    </a>
                    <a href="/admin/classes" class="flex items-center gap-3 rounded-lg border p-3 text-sm hover:bg-accent transition-colors">
                        <LayoutGrid class="size-4 text-muted-foreground" />
                        Manage Classes & Sections
                    </a>
                    <a href="/admin/subjects" class="flex items-center gap-3 rounded-lg border p-3 text-sm hover:bg-accent transition-colors">
                        <BookOpen class="size-4 text-muted-foreground" />
                        Manage Subjects
                    </a>
                    <a href="/admin/departments" class="flex items-center gap-3 rounded-lg border p-3 text-sm hover:bg-accent transition-colors">
                        <Building2 class="size-4 text-muted-foreground" />
                        Manage Departments
                    </a>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>System Info</CardTitle>
                </CardHeader>
                <CardContent class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Current Academic Year</span>
                        <span class="font-medium">{{ currentAcademicYear?.name ?? 'Not set' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Total Active Students</span>
                        <span class="font-medium">{{ stats.totalStudents }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Total Staff</span>
                        <span class="font-medium">{{ stats.totalTeachers }}</span>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
