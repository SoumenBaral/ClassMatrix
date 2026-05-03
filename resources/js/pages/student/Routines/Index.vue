<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import Heading from '@/components/Heading.vue';

type Block = { start: string; end: string; type: string; subject: string | null; title: string; priority?: number };
type Day = { day: string; date: string; blocks: Block[] };
type Routine = {
    id: number; week_start_date: string; type: string; status: string;
    weekly_goals: string[]; study_tips: string[]; ai_summary: string;
    blocks: Day[];
};

const props = defineProps<{
    routine: Routine | null;
    generatingRoutine: boolean;
    preferences: any;
}>();

const generating = ref(false);
const selectedDay = ref(0);

function generate(type: string) {
    generating.value = true;
    router.post('/student/routines/generate', { type }, {
        preserveScroll: true,
        onFinish: () => { generating.value = false; },
    });
}

const isGenerating = computed(() => props.generatingRoutine || generating.value);

const typeColors: Record<string, string> = {
    study: 'bg-blue-100 border-blue-300 text-blue-800 dark:bg-blue-950 dark:text-blue-200',
    revision: 'bg-purple-100 border-purple-300 text-purple-800 dark:bg-purple-950 dark:text-purple-200',
    practice: 'bg-indigo-100 border-indigo-300 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-200',
    homework: 'bg-cyan-100 border-cyan-300 text-cyan-800 dark:bg-cyan-950 dark:text-cyan-200',
    reading: 'bg-teal-100 border-teal-300 text-teal-800 dark:bg-teal-950 dark:text-teal-200',
    break: 'bg-gray-100 border-gray-300 text-gray-600 dark:bg-gray-800 dark:text-gray-300',
    exercise: 'bg-green-100 border-green-300 text-green-800 dark:bg-green-950 dark:text-green-200',
    meal: 'bg-orange-100 border-orange-300 text-orange-800 dark:bg-orange-950 dark:text-orange-200',
    school: 'bg-yellow-100 border-yellow-300 text-yellow-800 dark:bg-yellow-950 dark:text-yellow-200',
    free: 'bg-pink-100 border-pink-300 text-pink-800 dark:bg-pink-950 dark:text-pink-200',
    extracurricular: 'bg-rose-100 border-rose-300 text-rose-800 dark:bg-rose-950 dark:text-rose-200',
    sleep: 'bg-slate-100 border-slate-300 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
};
</script>

<template>
    <Head title="My Routine" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <Heading title="My Study Routine" description="AI-generated personalized study plan." />
            <div class="flex gap-2">
                <Button variant="outline" @click="generate('monthly')" :disabled="isGenerating">Monthly</Button>
                <Button @click="generate('weekly')" :disabled="isGenerating">
                    {{ isGenerating ? 'Generating...' : routine ? 'Regenerate Weekly' : 'Generate Routine' }}
                </Button>
            </div>
        </div>

        <!-- Generating state -->
        <div v-if="isGenerating" class="rounded-xl bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-950/30 dark:to-purple-950/30 p-8 text-center">
            <div class="animate-pulse text-lg font-medium text-indigo-700 dark:text-indigo-300">
                AI is crafting your personalized routine...
            </div>
            <p class="mt-2 text-sm text-muted-foreground">This may take 10-30 seconds.</p>
        </div>

        <!-- No preferences -->
        <div v-else-if="!preferences" class="rounded-xl border-2 border-dashed p-12 text-center">
            <p class="text-muted-foreground mb-4">Set your study preferences first so AI knows your schedule.</p>
            <Button as-child><a href="/student/routines/preferences">Set Preferences</a></Button>
        </div>

        <!-- Routine ready -->
        <template v-else-if="routine?.status === 'ready'">
            <!-- Summary -->
            <div class="rounded-xl bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/30 dark:to-indigo-950/30 p-5">
                <p class="text-sm">{{ routine.ai_summary }}</p>
            </div>

            <!-- Goals & Tips -->
            <div class="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardHeader><CardTitle class="text-base">This Week's Goals</CardTitle></CardHeader>
                    <CardContent>
                        <ul class="space-y-2 text-sm">
                            <li v-for="(goal, i) in routine.weekly_goals" :key="i" class="flex gap-2">
                                <span class="text-green-500 font-bold">{{ i + 1 }}.</span> {{ goal }}
                            </li>
                        </ul>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader><CardTitle class="text-base">Study Tips</CardTitle></CardHeader>
                    <CardContent>
                        <ul class="space-y-2 text-sm">
                            <li v-for="(tip, i) in routine.study_tips" :key="i" class="flex gap-2">
                                <span class="text-blue-500">*</span> {{ tip }}
                            </li>
                        </ul>
                    </CardContent>
                </Card>
            </div>

            <!-- Day tabs -->
            <div class="flex gap-2 overflow-x-auto pb-2">
                <button v-for="(day, idx) in routine.blocks" :key="idx"
                    @click="selectedDay = idx"
                    :class="[
                        'rounded-lg px-4 py-2 text-sm font-medium transition-colors whitespace-nowrap',
                        selectedDay === idx
                            ? 'bg-primary text-primary-foreground'
                            : 'bg-muted hover:bg-muted/80'
                    ]">
                    {{ day.day }} <span class="text-xs opacity-70">{{ day.date }}</span>
                </button>
            </div>

            <!-- Time blocks -->
            <div v-if="routine.blocks[selectedDay]" class="space-y-2">
                <div v-for="(block, idx) in routine.blocks[selectedDay].blocks" :key="idx"
                    :class="['flex items-center gap-4 rounded-lg border p-3 transition-colors', typeColors[block.type] || 'bg-muted/30']">
                    <div class="w-24 text-center shrink-0">
                        <div class="text-xs font-mono font-bold">{{ block.start }}</div>
                        <div class="text-xs text-muted-foreground">{{ block.end }}</div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-medium text-sm">{{ block.title }}</div>
                        <div v-if="block.subject" class="text-xs opacity-70">{{ block.subject }}</div>
                    </div>
                    <Badge variant="outline" class="capitalize text-xs shrink-0">{{ block.type }}</Badge>
                </div>
            </div>
        </template>

        <!-- No routine -->
        <div v-else class="rounded-xl border-2 border-dashed p-12 text-center">
            <p class="text-muted-foreground mb-4">No routine generated yet. Click "Generate Routine" to get started.</p>
        </div>
    </div>
</template>
