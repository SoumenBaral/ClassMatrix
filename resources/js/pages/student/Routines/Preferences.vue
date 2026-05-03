<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';

type Subject = { id: number; name: string };
type Preferences = {
    wake_up_time: string; sleep_time: string; focus_minutes: number;
    peak_focus: string; blocked_times: any[]; learning_goals: string[];
    subject_priorities: Record<string, number>; include_weekend: boolean;
} | null;

const props = defineProps<{ preferences: Preferences; subjects: Subject[] }>();

const form = useForm({
    wake_up_time: props.preferences?.wake_up_time?.substring(0, 5) ?? '06:00',
    sleep_time: props.preferences?.sleep_time?.substring(0, 5) ?? '22:00',
    focus_minutes: props.preferences?.focus_minutes ?? 45,
    peak_focus: props.preferences?.peak_focus ?? 'morning',
    blocked_times: props.preferences?.blocked_times ?? [],
    learning_goals: props.preferences?.learning_goals ?? [],
    subject_priorities: props.preferences?.subject_priorities ?? {},
    include_weekend: props.preferences?.include_weekend ?? true,
});

const newGoal = ref('');

function addGoal() {
    if (newGoal.value.trim()) {
        form.learning_goals.push(newGoal.value.trim());
        newGoal.value = '';
    }
}

function removeGoal(idx: number) {
    form.learning_goals.splice(idx, 1);
}

function submit() {
    form.post('/student/routines/preferences');
}
</script>

<template>
    <Head title="Study Preferences" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <Heading title="Study Preferences" description="Set your schedule and goals so AI can create the perfect routine for you." />

        <form @submit.prevent="submit" class="max-w-2xl space-y-6">
            <!-- Schedule -->
            <Card>
                <CardHeader><CardTitle class="text-base">My Schedule</CardTitle></CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div><Label>Wake Up Time</Label><Input type="time" v-model="form.wake_up_time" /><InputError :message="form.errors.wake_up_time" /></div>
                        <div><Label>Sleep Time</Label><Input type="time" v-model="form.sleep_time" /><InputError :message="form.errors.sleep_time" /></div>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="weekend" v-model="form.include_weekend" class="rounded" />
                        <Label for="weekend">Include weekend in routine</Label>
                    </div>
                </CardContent>
            </Card>

            <!-- Focus -->
            <Card>
                <CardHeader><CardTitle class="text-base">Focus Settings</CardTitle></CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label>Peak Focus Time</Label>
                            <select v-model="form.peak_focus" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                                <option value="morning">Morning (6AM-12PM)</option>
                                <option value="afternoon">Afternoon (12PM-5PM)</option>
                                <option value="evening">Evening (5PM-10PM)</option>
                            </select>
                        </div>
                        <div>
                            <Label>Focus Block Duration (minutes)</Label>
                            <Input type="number" v-model.number="form.focus_minutes" min="15" max="120" step="5" />
                            <InputError :message="form.errors.focus_minutes" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Subject Priorities -->
            <Card v-if="subjects.length">
                <CardHeader><CardTitle class="text-base">Subject Priority (1 = low, 5 = high)</CardTitle></CardHeader>
                <CardContent>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div v-for="sub in subjects" :key="sub.id" class="flex items-center justify-between rounded-lg border p-3">
                            <span class="text-sm font-medium">{{ sub.name }}</span>
                            <select v-model.number="form.subject_priorities[sub.name]"
                                class="h-8 w-16 rounded-md border border-input bg-transparent px-2 text-sm text-center">
                                <option :value="undefined">-</option>
                                <option v-for="n in 5" :key="n" :value="n">{{ n }}</option>
                            </select>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Goals -->
            <Card>
                <CardHeader><CardTitle class="text-base">Learning Goals</CardTitle></CardHeader>
                <CardContent class="space-y-3">
                    <div class="flex gap-2">
                        <Input v-model="newGoal" placeholder="e.g. Improve in Math, Finish chapter 5..." @keyup.enter.prevent="addGoal" class="flex-1" />
                        <Button type="button" variant="outline" @click="addGoal">Add</Button>
                    </div>
                    <div v-for="(goal, idx) in form.learning_goals" :key="idx" class="flex items-center gap-2 rounded-lg border p-2 text-sm">
                        <span class="flex-1">{{ goal }}</span>
                        <button type="button" @click="removeGoal(idx)" class="text-muted-foreground hover:text-destructive text-xs">Remove</button>
                    </div>
                </CardContent>
            </Card>

            <div class="flex justify-end">
                <Button type="submit" :disabled="form.processing" size="lg">Save Preferences</Button>
            </div>
        </form>
    </div>
</template>
