<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Plus, Trash2, ArrowLeft } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';

type ClassLevel = { id: number; name: string };
type Subject = { id: number; name: string; code: string };
type Schedule = {
    id: number; exam_date: string | null; start_time: string | null;
    end_time: string | null; full_marks: number; pass_marks: number;
    room: string | null;
    class_level: ClassLevel; subject: Subject;
};
type Exam = { id: number; name: string; exam_type: { name: string } };

const props = defineProps<{
    exam: Exam;
    schedules: Schedule[];
    classLevels: ClassLevel[];
    subjects: Subject[];
}>();

const showDialog = ref(false);
const form = useForm({
    class_level_id: '' as any,
    subject_id: '' as any,
    exam_date: '',
    start_time: '',
    end_time: '',
    full_marks: 100,
    pass_marks: 33,
    room: '',
});

function openCreate() {
    form.reset();
    form.full_marks = 100;
    form.pass_marks = 33;
    showDialog.value = true;
}

function submit() {
    form.post(`/admin/exams/${props.exam.id}/schedules`, {
        onSuccess: () => { showDialog.value = false; form.reset(); },
    });
}

function destroy(schedule: Schedule) {
    if (confirm('Remove this schedule?')) {
        router.delete(`/admin/exam-schedules/${schedule.id}`);
    }
}
</script>

<template>
    <Head :title="`${exam.name} - Schedules`" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center gap-3">
            <Button variant="ghost" size="sm" as-child>
                <a href="/admin/exams"><ArrowLeft class="size-4" /></a>
            </Button>
            <Heading :title="exam.name" :description="`${exam.exam_type.name} — Exam Schedule`" />
            <div class="flex-1" />
            <Button @click="openCreate"><Plus class="mr-2 size-4" /> Add Schedule</Button>
        </div>

        <div class="rounded-lg border">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b bg-muted/50">
                        <th class="px-4 py-3 text-left font-medium">Class</th>
                        <th class="px-4 py-3 text-left font-medium">Subject</th>
                        <th class="px-4 py-3 text-left font-medium">Date</th>
                        <th class="px-4 py-3 text-left font-medium">Time</th>
                        <th class="px-4 py-3 text-center font-medium">Full</th>
                        <th class="px-4 py-3 text-center font-medium">Pass</th>
                        <th class="px-4 py-3 text-left font-medium">Room</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="s in schedules" :key="s.id" class="border-b last:border-0 hover:bg-muted/30">
                        <td class="px-4 py-2 font-medium">{{ s.class_level.name }}</td>
                        <td class="px-4 py-2">{{ s.subject.name }} <code class="text-xs text-muted-foreground">({{ s.subject.code }})</code></td>
                        <td class="px-4 py-2 text-muted-foreground">{{ s.exam_date ?? '-' }}</td>
                        <td class="px-4 py-2 text-muted-foreground">
                            {{ s.start_time ? s.start_time.substring(0, 5) + ' - ' + s.end_time?.substring(0, 5) : '-' }}
                        </td>
                        <td class="px-4 py-2 text-center">{{ s.full_marks }}</td>
                        <td class="px-4 py-2 text-center">{{ s.pass_marks }}</td>
                        <td class="px-4 py-2 text-muted-foreground">{{ s.room ?? '-' }}</td>
                        <td class="px-4 py-2 text-right">
                            <Button variant="ghost" size="sm" class="text-destructive" @click="destroy(s)">
                                <Trash2 class="size-3" />
                            </Button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="!schedules.length" class="flex items-center justify-center rounded-xl border-2 border-dashed p-12 text-muted-foreground">
            No schedules yet. Add subjects and dates for this exam.
        </div>
    </div>

    <Dialog v-model:open="showDialog">
        <DialogContent>
            <DialogHeader><DialogTitle>Add Exam Schedule</DialogTitle></DialogHeader>
            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <Label>Class</Label>
                        <select v-model="form.class_level_id" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                            <option value="">Select...</option>
                            <option v-for="c in classLevels" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                        <InputError :message="form.errors.class_level_id" />
                    </div>
                    <div>
                        <Label>Subject</Label>
                        <select v-model="form.subject_id" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                            <option value="">Select...</option>
                            <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                        <InputError :message="form.errors.subject_id" />
                    </div>
                </div>
                <div>
                    <Label>Exam Date</Label>
                    <Input type="date" v-model="form.exam_date" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><Label>Start Time</Label><Input type="time" v-model="form.start_time" /></div>
                    <div><Label>End Time</Label><Input type="time" v-model="form.end_time" /></div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div><Label>Full Marks</Label><Input type="number" v-model.number="form.full_marks" min="1" /><InputError :message="form.errors.full_marks" /></div>
                    <div><Label>Pass Marks</Label><Input type="number" v-model.number="form.pass_marks" min="0" /><InputError :message="form.errors.pass_marks" /></div>
                    <div><Label>Room</Label><Input v-model="form.room" placeholder="101" /></div>
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="showDialog = false">Cancel</Button>
                    <Button type="submit" :disabled="form.processing">Add</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
