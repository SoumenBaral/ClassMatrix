<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { BookOpen, ClipboardCheck, Layers, Users } from 'lucide-vue-next';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import Heading from '@/components/Heading.vue';

const props = defineProps<{
    teacher: { name: string; designation: string };
    stats: {
        teachingSections: number;
        classSections: { id: number; name: string; students: number }[];
        subjects: { section: string; subject: string }[];
        pendingAssignments: { title: string; due_date: string; submissions: number }[];
    };
}>();
</script>

<template>
    <Head title="Teacher Dashboard" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <Heading :title="`Welcome, ${teacher.name}`" :description="teacher.designation" />

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Card>
                <CardContent class="flex items-center gap-4 pt-4">
                    <div class="rounded-lg bg-blue-50 p-3 dark:bg-blue-950"><Layers class="size-5 text-blue-600" /></div>
                    <div>
                        <div class="text-sm text-muted-foreground">Teaching Sections</div>
                        <div class="text-2xl font-bold">{{ stats.teachingSections }}</div>
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="flex items-center gap-4 pt-4">
                    <div class="rounded-lg bg-green-50 p-3 dark:bg-green-950"><Users class="size-5 text-green-600" /></div>
                    <div>
                        <div class="text-sm text-muted-foreground">Class Teacher Of</div>
                        <div class="text-2xl font-bold">{{ stats.classSections.length }} section(s)</div>
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="flex items-center gap-4 pt-4">
                    <div class="rounded-lg bg-purple-50 p-3 dark:bg-purple-950"><ClipboardCheck class="size-5 text-purple-600" /></div>
                    <div>
                        <div class="text-sm text-muted-foreground">Pending Assignments</div>
                        <div class="text-2xl font-bold">{{ stats.pendingAssignments.length }}</div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <Card>
                <CardHeader><CardTitle>My Subjects</CardTitle></CardHeader>
                <CardContent>
                    <div v-if="stats.subjects.length" class="space-y-2">
                        <div v-for="(s, i) in stats.subjects" :key="i" class="flex items-center justify-between rounded-lg border p-3 text-sm">
                            <span class="font-medium">{{ s.subject }}</span>
                            <Badge variant="outline">{{ s.section }}</Badge>
                        </div>
                    </div>
                    <p v-else class="text-sm text-muted-foreground">No subjects assigned yet.</p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader><CardTitle>Class Sections</CardTitle></CardHeader>
                <CardContent>
                    <div v-if="stats.classSections.length" class="space-y-2">
                        <div v-for="s in stats.classSections" :key="s.id" class="flex items-center justify-between rounded-lg border p-3 text-sm">
                            <span class="font-medium">{{ s.name }}</span>
                            <Badge variant="secondary">{{ s.students }} students</Badge>
                        </div>
                    </div>
                    <p v-else class="text-sm text-muted-foreground">Not assigned as class teacher.</p>
                </CardContent>
            </Card>
        </div>

        <Card v-if="stats.pendingAssignments.length">
            <CardHeader><CardTitle>Upcoming Assignments</CardTitle></CardHeader>
            <CardContent>
                <div class="space-y-2">
                    <div v-for="(a, i) in stats.pendingAssignments" :key="i" class="flex items-center justify-between rounded-lg border p-3 text-sm">
                        <span class="font-medium">{{ a.title }}</span>
                        <div class="flex items-center gap-3">
                            <Badge variant="outline">{{ a.submissions }} submitted</Badge>
                            <span class="text-muted-foreground">Due {{ a.due_date }}</span>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
