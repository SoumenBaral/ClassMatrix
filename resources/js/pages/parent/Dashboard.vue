<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ClipboardCheck, GraduationCap, Users } from 'lucide-vue-next';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import Heading from '@/components/Heading.vue';

type RecentMark = { subject: string; exam: string; marks: number; total: number; grade: string };
type Child = {
    id: number; name: string; admission_no: string; class: string;
    section: string; roll_no: string | null; relation: string;
    attendance: number; recent_marks: RecentMark[];
};

const props = defineProps<{
    parent: { name: string };
    children: Child[];
}>();

function attendanceColor(pct: number): string {
    if (pct >= 90) return 'text-green-600';
    if (pct >= 75) return 'text-yellow-600';
    return 'text-red-600';
}

function gradeVariant(grade: string): string {
    if (!grade) return 'secondary';
    if (grade.startsWith('A')) return 'default';
    if (grade.startsWith('B')) return 'secondary';
    return 'outline';
}
</script>

<template>
    <Head title="Parent Dashboard" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <Heading :title="`Welcome, ${parent.name}`" description="Parent Portal - Track your children's academic journey" />

        <!-- Children cards -->
        <template v-if="children.length">
            <div class="grid gap-6">
                <Card v-for="child in children" :key="child.id">
                    <CardHeader class="flex flex-row items-start justify-between pb-3">
                        <div class="flex items-center gap-4">
                            <div class="rounded-full bg-blue-100 p-3 dark:bg-blue-900">
                                <GraduationCap class="size-5 text-blue-600" />
                            </div>
                            <div>
                                <CardTitle class="text-lg">{{ child.name }}</CardTitle>
                                <div class="flex items-center gap-2 mt-1 text-sm text-muted-foreground">
                                    <span>{{ child.class }} - {{ child.section }}</span>
                                    <span v-if="child.roll_no">| Roll: {{ child.roll_no }}</span>
                                    <Badge variant="outline" class="capitalize">{{ child.relation }}</Badge>
                                </div>
                            </div>
                        </div>
                        <code class="text-xs text-muted-foreground">{{ child.admission_no }}</code>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <!-- Attendance -->
                            <div class="rounded-lg border p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <ClipboardCheck class="size-4 text-muted-foreground" />
                                    <span class="text-sm font-medium">Attendance</span>
                                </div>
                                <div :class="['text-3xl font-bold', attendanceColor(child.attendance)]">
                                    {{ child.attendance }}%
                                </div>
                            </div>

                            <!-- Recent Results -->
                            <div class="rounded-lg border p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <GraduationCap class="size-4 text-muted-foreground" />
                                    <span class="text-sm font-medium">Recent Results</span>
                                </div>
                                <div v-if="child.recent_marks.length" class="space-y-2">
                                    <div v-for="(mark, i) in child.recent_marks" :key="i"
                                        class="flex items-center justify-between text-sm">
                                        <span class="text-muted-foreground">{{ mark.subject }}</span>
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono">{{ mark.marks }}/{{ mark.total }}</span>
                                            <Badge :variant="gradeVariant(mark.grade) as any" class="text-xs">{{ mark.grade ?? '-' }}</Badge>
                                        </div>
                                    </div>
                                </div>
                                <p v-else class="text-sm text-muted-foreground">No results yet.</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </template>

        <!-- No children linked -->
        <Card v-else>
            <CardContent class="flex flex-col items-center justify-center py-16 text-center">
                <Users class="size-16 text-muted-foreground/20 mb-4" />
                <h3 class="text-lg font-semibold mb-2">No Children Linked</h3>
                <p class="text-sm text-muted-foreground max-w-md">
                    Your account is ready. Please ask your school administrator to link
                    your child's student profile to your account. Once linked, you'll see
                    their attendance, exam results, and school activities here.
                </p>
            </CardContent>
        </Card>
    </div>
</template>
