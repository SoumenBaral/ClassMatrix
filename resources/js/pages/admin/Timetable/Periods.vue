<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, Coffee } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';

type Period = {
    id: number; name: string; start_time: string;
    end_time: string; order: number; is_break: boolean;
};

const props = defineProps<{ periods: Period[] }>();

const showDialog = ref(false);
const editing = ref<Period | null>(null);

const form = useForm({
    name: '',
    start_time: '',
    end_time: '',
    order: 1,
    is_break: false,
});

function openCreate() {
    editing.value = null;
    form.reset();
    form.order = props.periods.length + 1;
    showDialog.value = true;
}

function openEdit(period: Period) {
    editing.value = period;
    form.name = period.name;
    form.start_time = period.start_time.substring(0, 5);
    form.end_time = period.end_time.substring(0, 5);
    form.order = period.order;
    form.is_break = period.is_break;
    showDialog.value = true;
}

function submit() {
    if (editing.value) {
        form.put(`/admin/periods/${editing.value.id}`, {
            onSuccess: () => { showDialog.value = false; },
        });
    } else {
        form.post('/admin/periods', {
            onSuccess: () => { showDialog.value = false; form.reset(); },
        });
    }
}

function destroy(period: Period) {
    if (confirm(`Delete "${period.name}"?`)) {
        router.delete(`/admin/periods/${period.id}`);
    }
}
</script>

<template>
    <Head title="Periods" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <Heading title="Periods" description="Define school periods/time slots for timetable." />
            <Button @click="openCreate">
                <Plus class="mr-2 size-4" /> Add Period
            </Button>
        </div>

        <div class="rounded-lg border">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b bg-muted/50">
                        <th class="px-4 py-3 text-left font-medium w-16">Order</th>
                        <th class="px-4 py-3 text-left font-medium">Name</th>
                        <th class="px-4 py-3 text-left font-medium">Start</th>
                        <th class="px-4 py-3 text-left font-medium">End</th>
                        <th class="px-4 py-3 text-left font-medium">Type</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="period in periods" :key="period.id"
                        :class="['border-b last:border-0', period.is_break ? 'bg-muted/20' : 'hover:bg-muted/30']">
                        <td class="px-4 py-2 text-muted-foreground">{{ period.order }}</td>
                        <td class="px-4 py-2 font-medium">
                            <div class="flex items-center gap-2">
                                <Coffee v-if="period.is_break" class="size-3 text-orange-500" />
                                {{ period.name }}
                            </div>
                        </td>
                        <td class="px-4 py-2">{{ period.start_time.substring(0, 5) }}</td>
                        <td class="px-4 py-2">{{ period.end_time.substring(0, 5) }}</td>
                        <td class="px-4 py-2">
                            <Badge :variant="period.is_break ? 'secondary' : 'default'">
                                {{ period.is_break ? 'Break' : 'Class' }}
                            </Badge>
                        </td>
                        <td class="px-4 py-2 text-right">
                            <div class="flex justify-end gap-1">
                                <Button variant="ghost" size="sm" @click="openEdit(period)">
                                    <Pencil class="size-3" />
                                </Button>
                                <Button variant="ghost" size="sm" class="text-destructive" @click="destroy(period)">
                                    <Trash2 class="size-3" />
                                </Button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="!periods.length" class="flex items-center justify-center rounded-xl border-2 border-dashed p-12 text-muted-foreground">
            No periods defined yet. Add periods to build your timetable.
        </div>
    </div>

    <Dialog v-model:open="showDialog">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{ editing ? 'Edit' : 'Create' }} Period</DialogTitle>
            </DialogHeader>
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <Label>Name</Label>
                    <Input v-model="form.name" placeholder="e.g. Period 1, Lunch Break" />
                    <InputError :message="form.errors.name" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <Label>Start Time</Label>
                        <Input type="time" v-model="form.start_time" />
                        <InputError :message="form.errors.start_time" />
                    </div>
                    <div>
                        <Label>End Time</Label>
                        <Input type="time" v-model="form.end_time" />
                        <InputError :message="form.errors.end_time" />
                    </div>
                </div>
                <div>
                    <Label>Order</Label>
                    <Input type="number" v-model.number="form.order" min="1" />
                    <InputError :message="form.errors.order" />
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="is_break" v-model="form.is_break" class="rounded" />
                    <Label for="is_break">This is a break (not a class period)</Label>
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="showDialog = false">Cancel</Button>
                    <Button type="submit" :disabled="form.processing">{{ editing ? 'Update' : 'Create' }}</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
