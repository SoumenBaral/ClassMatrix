<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';

type Period = { id: number; name: string; start_time: string; end_time: string; order: number; is_break: boolean };
type Subject = { id: number; name: string; code: string };
type Teacher = { id: number; name: string };
type Section = { id: number; name: string };
type Day = { id: number; name: string };
type TimetableEntry = {
    id: number; period_id: number;
    subject: { id: number; name: string; code: string } | null;
    teacher: { id: number; name: string } | null;
    room: string | null;
};

const props = defineProps<{
    sections: Section[];
    selectedSectionId: number;
    timetable: Record<number, Record<number, TimetableEntry>>;
    periods: Period[];
    subjects: Subject[];
    teachers: Teacher[];
    days: Day[];
}>();

const sectionId = ref(props.selectedSectionId || '');

watch(sectionId, () => {
    if (sectionId.value) {
        router.get('/admin/timetable', { section_id: sectionId.value }, {
            preserveState: true, preserveScroll: true,
        });
    }
});

// Slot edit dialog
const showDialog = ref(false);
const editingDay = ref(0);
const editingPeriod = ref(0);

const form = useForm({
    section_id: 0,
    day_of_week: 0,
    period_id: 0,
    subject_id: null as number | null,
    teacher_id: null as number | null,
    room: '',
});

function openSlot(dayId: number, periodId: number) {
    const existing = props.timetable[dayId]?.[periodId];
    editingDay.value = dayId;
    editingPeriod.value = periodId;
    form.section_id = Number(sectionId.value);
    form.day_of_week = dayId;
    form.period_id = periodId;
    form.subject_id = existing?.subject?.id ?? null;
    form.teacher_id = existing?.teacher?.id ?? null;
    form.room = existing?.room ?? '';
    showDialog.value = true;
}

function submitSlot() {
    form.post('/admin/timetable', {
        preserveScroll: true,
        onSuccess: () => { showDialog.value = false; },
    });
}

function clearSlot() {
    const existing = props.timetable[editingDay.value]?.[editingPeriod.value];
    if (existing) {
        router.delete(`/admin/timetable/${existing.id}`, { preserveScroll: true });
    }
    showDialog.value = false;
}

const classPeriods = props.periods.filter(p => !p.is_break);
const breakPeriods = props.periods.filter(p => p.is_break);

function getEntry(dayId: number, periodId: number): TimetableEntry | null {
    return props.timetable[dayId]?.[periodId] ?? null;
}

function getDayName(dayId: number): string {
    return props.days.find(d => d.id === dayId)?.name ?? '';
}
</script>

<template>
    <Head title="Timetable" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <Heading title="Timetable" description="Manage class timetable. Click any cell to assign subject & teacher." />
        </div>

        <div class="flex items-end gap-4">
            <div>
                <Label>Section</Label>
                <select v-model="sectionId"
                    class="flex h-9 w-64 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                    <option value="">Select section...</option>
                    <option v-for="s in sections" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
            </div>
        </div>

        <div v-if="sectionId && periods.length" class="overflow-auto rounded-lg border">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b bg-muted/50">
                        <th class="sticky left-0 bg-muted/50 px-4 py-3 text-left font-medium w-28">Period</th>
                        <th v-for="day in days" :key="day.id" class="px-3 py-3 text-center font-medium min-w-[140px]">
                            {{ day.name }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="period in periods" :key="period.id"
                        :class="['border-b last:border-0', period.is_break ? 'bg-orange-50/50 dark:bg-orange-950/20' : '']">
                        <td class="sticky left-0 bg-background px-4 py-2 font-medium">
                            <div>{{ period.name }}</div>
                            <div class="text-xs text-muted-foreground">
                                {{ period.start_time.substring(0, 5) }} - {{ period.end_time.substring(0, 5) }}
                            </div>
                        </td>
                        <template v-if="period.is_break">
                            <td :colspan="days.length" class="px-3 py-2 text-center text-muted-foreground italic">
                                Break
                            </td>
                        </template>
                        <template v-else>
                            <td v-for="day in days" :key="day.id" class="px-1 py-1">
                                <button @click="openSlot(day.id, period.id)"
                                    class="w-full rounded-md border border-dashed p-2 text-left transition-colors hover:bg-accent hover:border-solid min-h-[60px]">
                                    <template v-if="getEntry(day.id, period.id)?.subject">
                                        <div class="font-medium text-xs">
                                            {{ getEntry(day.id, period.id)!.subject!.name }}
                                        </div>
                                        <div class="text-xs text-muted-foreground">
                                            {{ getEntry(day.id, period.id)!.teacher?.name ?? '' }}
                                        </div>
                                        <div v-if="getEntry(day.id, period.id)!.room" class="text-xs text-muted-foreground">
                                            Rm {{ getEntry(day.id, period.id)!.room }}
                                        </div>
                                    </template>
                                    <span v-else class="text-xs text-muted-foreground">+</span>
                                </button>
                            </td>
                        </template>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-else-if="sectionId && !periods.length" class="flex items-center justify-center rounded-xl border-2 border-dashed p-12 text-muted-foreground">
            No periods defined. <a href="/admin/periods" class="ml-1 underline">Add periods first.</a>
        </div>

        <div v-else class="flex items-center justify-center rounded-xl border-2 border-dashed p-12 text-muted-foreground">
            Select a section to manage its timetable.
        </div>
    </div>

    <Dialog v-model:open="showDialog">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>
                    {{ getDayName(editingDay) }} - {{ periods.find(p => p.id === editingPeriod)?.name }}
                </DialogTitle>
            </DialogHeader>
            <form @submit.prevent="submitSlot" class="space-y-4">
                <div>
                    <Label>Subject</Label>
                    <select v-model="form.subject_id"
                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                        <option :value="null">None</option>
                        <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }} ({{ s.code }})</option>
                    </select>
                    <InputError :message="form.errors.subject_id" />
                </div>
                <div>
                    <Label>Teacher</Label>
                    <select v-model="form.teacher_id"
                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                        <option :value="null">None</option>
                        <option v-for="t in teachers" :key="t.id" :value="t.id">{{ t.name }}</option>
                    </select>
                    <InputError :message="form.errors.teacher_id" />
                </div>
                <div>
                    <Label>Room</Label>
                    <input v-model="form.room" placeholder="e.g. 101"
                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring" />
                </div>
                <DialogFooter class="gap-2">
                    <Button type="button" variant="destructive" size="sm" @click="clearSlot" v-if="getEntry(editingDay, editingPeriod)">
                        Clear
                    </Button>
                    <div class="flex-1" />
                    <Button type="button" variant="outline" @click="showDialog = false">Cancel</Button>
                    <Button type="submit" :disabled="form.processing">Save</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
