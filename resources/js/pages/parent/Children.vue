<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { GraduationCap, Calendar, User } from 'lucide-vue-next';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import Heading from '@/components/Heading.vue';

type Child = {
    id: number; name: string; email: string; admission_no: string;
    roll_no: string | null; dob: string | null; gender: string | null;
    blood_group: string | null; class: string; section: string;
    admission_date: string | null; relation: string;
    attendance_rate: number; total_days: number; present_days: number;
};

const props = defineProps<{ children: Child[] }>();
</script>

<template>
    <Head title="My Children" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <Heading title="My Children" description="Detailed profiles of your linked children." />

        <div v-if="children.length" class="grid gap-6">
            <Card v-for="child in children" :key="child.id">
                <CardHeader class="flex flex-row items-start gap-4 pb-4">
                    <div class="rounded-full bg-blue-100 p-3 dark:bg-blue-900">
                        <User class="size-6 text-blue-600" />
                    </div>
                    <div class="flex-1">
                        <CardTitle class="text-xl">{{ child.name }}</CardTitle>
                        <div class="flex items-center gap-2 mt-1">
                            <Badge variant="outline" class="capitalize">{{ child.relation }}</Badge>
                            <code class="text-xs text-muted-foreground">{{ child.admission_no }}</code>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold" :class="child.attendance_rate >= 75 ? 'text-green-600' : 'text-red-600'">
                            {{ child.attendance_rate }}%
                        </div>
                        <div class="text-xs text-muted-foreground">Attendance</div>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-lg border p-3">
                            <div class="text-xs text-muted-foreground mb-1">Class & Section</div>
                            <div class="font-medium">{{ child.class }} - {{ child.section }}</div>
                        </div>
                        <div class="rounded-lg border p-3">
                            <div class="text-xs text-muted-foreground mb-1">Roll Number</div>
                            <div class="font-medium">{{ child.roll_no ?? 'Not assigned' }}</div>
                        </div>
                        <div class="rounded-lg border p-3">
                            <div class="text-xs text-muted-foreground mb-1">Date of Birth</div>
                            <div class="font-medium">{{ child.dob ?? '-' }}</div>
                        </div>
                        <div class="rounded-lg border p-3">
                            <div class="text-xs text-muted-foreground mb-1">Gender / Blood Group</div>
                            <div class="font-medium capitalize">{{ child.gender ?? '-' }} {{ child.blood_group ? '| ' + child.blood_group : '' }}</div>
                        </div>
                        <div class="rounded-lg border p-3">
                            <div class="text-xs text-muted-foreground mb-1">Admission Date</div>
                            <div class="font-medium">{{ child.admission_date ?? '-' }}</div>
                        </div>
                        <div class="rounded-lg border p-3">
                            <div class="text-xs text-muted-foreground mb-1">Days Present</div>
                            <div class="font-medium">{{ child.present_days }} / {{ child.total_days }}</div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <Card v-else>
            <CardContent class="flex flex-col items-center justify-center py-16 text-center">
                <GraduationCap class="size-12 text-muted-foreground/20 mb-4" />
                <h3 class="text-lg font-semibold mb-2">No Children Linked</h3>
                <p class="text-sm text-muted-foreground">Ask your school admin to link your child's profile.</p>
            </CardContent>
        </Card>
    </div>
</template>
