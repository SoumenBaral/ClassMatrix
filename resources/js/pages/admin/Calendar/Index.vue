<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, Plus, Trash2 } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';

type CalEvent = {
    id: number; title: string; description: string | null;
    start_at: string; end_at: string; start_date: string;
    type: string; color: string; location: string | null;
};
type Holiday = { id: number; name: string; date: string; recurring: boolean };

const props = defineProps<{
    events: CalEvent[];
    holidays: Holiday[];
    month: string;
}>();

// Month navigation
const currentMonth = ref(props.month);

function navigateMonth(offset: number) {
    const [y, m] = currentMonth.value.split('-').map(Number);
    const d = new Date(y, m - 1 + offset, 1);
    currentMonth.value = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
    router.get('/admin/calendar', { month: currentMonth.value }, {
        preserveState: true, preserveScroll: true,
    });
}

const monthLabel = computed(() => {
    const [y, m] = currentMonth.value.split('-').map(Number);
    return new Date(y, m - 1).toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
});

// Calendar grid
const calendarDays = computed(() => {
    const [y, m] = currentMonth.value.split('-').map(Number);
    const firstDay = new Date(y, m - 1, 1);
    const lastDay = new Date(y, m, 0);
    const startPad = (firstDay.getDay() + 6) % 7; // Monday start
    const days: { date: string; day: number; inMonth: boolean; events: CalEvent[]; holidays: Holiday[] }[] = [];

    // Padding before
    for (let i = startPad - 1; i >= 0; i--) {
        const d = new Date(y, m - 1, -i);
        days.push({ date: formatDate(d), day: d.getDate(), inMonth: false, events: [], holidays: [] });
    }

    // Month days
    for (let i = 1; i <= lastDay.getDate(); i++) {
        const dateStr = `${currentMonth.value}-${String(i).padStart(2, '0')}`;
        days.push({
            date: dateStr,
            day: i,
            inMonth: true,
            events: props.events.filter(e => e.start_date === dateStr),
            holidays: props.holidays.filter(h => h.date === dateStr),
        });
    }

    // Padding after
    const remaining = 7 - (days.length % 7);
    if (remaining < 7) {
        for (let i = 1; i <= remaining; i++) {
            const d = new Date(y, m, i);
            days.push({ date: formatDate(d), day: d.getDate(), inMonth: false, events: [], holidays: [] });
        }
    }

    return days;
});

function formatDate(d: Date): string {
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
}

// Event dialog
const showEventDialog = ref(false);
const eventForm = useForm({
    title: '', description: '', start_at: '', end_at: '',
    type: 'other', audience: 'all', color: '', location: '',
});

function openCreateEvent(date?: string) {
    eventForm.reset();
    if (date) {
        eventForm.start_at = date + 'T09:00';
        eventForm.end_at = date + 'T17:00';
    }
    showEventDialog.value = true;
}

function submitEvent() {
    eventForm.post('/admin/events', {
        onSuccess: () => { showEventDialog.value = false; eventForm.reset(); },
    });
}

function destroyEvent(event: CalEvent) {
    if (confirm(`Delete "${event.title}"?`)) {
        router.delete(`/admin/events/${event.id}`);
    }
}

// Holiday dialog
const showHolidayDialog = ref(false);
const holidayForm = useForm({ name: '', date: '', recurring: false });

function openCreateHoliday() {
    holidayForm.reset();
    showHolidayDialog.value = true;
}

function submitHoliday() {
    holidayForm.post('/admin/holidays', {
        onSuccess: () => { showHolidayDialog.value = false; holidayForm.reset(); },
    });
}

function destroyHoliday(holiday: Holiday) {
    if (confirm(`Remove "${holiday.name}"?`)) {
        router.delete(`/admin/holidays/${holiday.id}`);
    }
}

const eventTypes = ['holiday', 'exam', 'meeting', 'cultural', 'sports', 'other'];
const weekDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

const today = new Date().toISOString().split('T')[0];
</script>

<template>
    <Head title="Calendar" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <Heading title="Calendar & Events" description="School calendar, events, and holidays." />
            <div class="flex gap-2">
                <Button variant="outline" @click="openCreateHoliday">Add Holiday</Button>
                <Button @click="openCreateEvent()">
                    <Plus class="mr-2 size-4" /> Add Event
                </Button>
            </div>
        </div>

        <!-- Month navigation -->
        <div class="flex items-center justify-between">
            <Button variant="ghost" size="sm" @click="navigateMonth(-1)">
                <ChevronLeft class="size-4" />
            </Button>
            <h2 class="text-lg font-semibold">{{ monthLabel }}</h2>
            <Button variant="ghost" size="sm" @click="navigateMonth(1)">
                <ChevronRight class="size-4" />
            </Button>
        </div>

        <!-- Calendar grid -->
        <div class="rounded-lg border">
            <div class="grid grid-cols-7 border-b bg-muted/50">
                <div v-for="day in weekDays" :key="day" class="px-2 py-2 text-center text-xs font-medium text-muted-foreground">
                    {{ day }}
                </div>
            </div>
            <div class="grid grid-cols-7">
                <div v-for="(day, i) in calendarDays" :key="i"
                    :class="[
                        'min-h-[100px] border-b border-r p-1 text-sm',
                        !day.inMonth && 'bg-muted/20 text-muted-foreground',
                        day.date === today && 'bg-primary/5',
                    ]"
                    @dblclick="day.inMonth && openCreateEvent(day.date)">
                    <div class="flex items-center justify-between px-1">
                        <span :class="[
                            'text-xs font-medium',
                            day.date === today && 'rounded-full bg-primary px-1.5 py-0.5 text-primary-foreground',
                        ]">
                            {{ day.day }}
                        </span>
                    </div>
                    <div class="mt-1 space-y-0.5">
                        <div v-for="h in day.holidays" :key="'h' + h.id"
                            class="flex items-center justify-between rounded bg-red-100 px-1 py-0.5 text-xs text-red-700 dark:bg-red-900 dark:text-red-200">
                            <span class="truncate">{{ h.name }}</span>
                            <button @click.stop="destroyHoliday(h)" class="shrink-0 hover:text-red-900">
                                <Trash2 class="size-2.5" />
                            </button>
                        </div>
                        <div v-for="e in day.events" :key="'e' + e.id"
                            class="flex items-center justify-between rounded px-1 py-0.5 text-xs text-white"
                            :style="{ backgroundColor: e.color }">
                            <span class="truncate">{{ e.title }}</span>
                            <button @click.stop="destroyEvent(e)" class="shrink-0 hover:opacity-70">
                                <Trash2 class="size-2.5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming events sidebar -->
        <div class="grid gap-4 lg:grid-cols-2">
            <Card>
                <CardHeader><CardTitle class="text-base">Upcoming Events</CardTitle></CardHeader>
                <CardContent class="space-y-2">
                    <div v-for="e in events" :key="e.id" class="flex items-center gap-3 rounded-lg border p-2">
                        <div class="size-3 shrink-0 rounded-full" :style="{ backgroundColor: e.color }" />
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium truncate">{{ e.title }}</div>
                            <div class="text-xs text-muted-foreground">{{ e.start_at }} | {{ e.type }}</div>
                        </div>
                    </div>
                    <p v-if="!events.length" class="text-sm text-muted-foreground">No events this month.</p>
                </CardContent>
            </Card>
            <Card>
                <CardHeader><CardTitle class="text-base">Holidays</CardTitle></CardHeader>
                <CardContent class="space-y-2">
                    <div v-for="h in holidays" :key="h.id" class="flex items-center justify-between rounded-lg border p-2">
                        <div>
                            <div class="text-sm font-medium">{{ h.name }}</div>
                            <div class="text-xs text-muted-foreground">{{ h.date }}</div>
                        </div>
                        <Badge v-if="h.recurring" variant="outline">Recurring</Badge>
                    </div>
                    <p v-if="!holidays.length" class="text-sm text-muted-foreground">No holidays this month.</p>
                </CardContent>
            </Card>
        </div>
    </div>

    <!-- Event Dialog -->
    <Dialog v-model:open="showEventDialog">
        <DialogContent>
            <DialogHeader><DialogTitle>Create Event</DialogTitle></DialogHeader>
            <form @submit.prevent="submitEvent" class="space-y-4">
                <div>
                    <Label>Title</Label>
                    <Input v-model="eventForm.title" placeholder="Event title" />
                    <InputError :message="eventForm.errors.title" />
                </div>
                <div>
                    <Label>Description</Label>
                    <textarea v-model="eventForm.description" rows="3" placeholder="Optional description..."
                        class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <Label>Start</Label>
                        <Input type="datetime-local" v-model="eventForm.start_at" />
                        <InputError :message="eventForm.errors.start_at" />
                    </div>
                    <div>
                        <Label>End</Label>
                        <Input type="datetime-local" v-model="eventForm.end_at" />
                        <InputError :message="eventForm.errors.end_at" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <Label>Type</Label>
                        <select v-model="eventForm.type"
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                            <option v-for="t in eventTypes" :key="t" :value="t" class="capitalize">{{ t }}</option>
                        </select>
                    </div>
                    <div>
                        <Label>Location</Label>
                        <Input v-model="eventForm.location" placeholder="Optional" />
                    </div>
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="showEventDialog = false">Cancel</Button>
                    <Button type="submit" :disabled="eventForm.processing">Create</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Holiday Dialog -->
    <Dialog v-model:open="showHolidayDialog">
        <DialogContent>
            <DialogHeader><DialogTitle>Add Holiday</DialogTitle></DialogHeader>
            <form @submit.prevent="submitHoliday" class="space-y-4">
                <div>
                    <Label>Name</Label>
                    <Input v-model="holidayForm.name" placeholder="e.g. Independence Day" />
                    <InputError :message="holidayForm.errors.name" />
                </div>
                <div>
                    <Label>Date</Label>
                    <Input type="date" v-model="holidayForm.date" />
                    <InputError :message="holidayForm.errors.date" />
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="recurring" v-model="holidayForm.recurring" class="rounded" />
                    <Label for="recurring">Recurring every year</Label>
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="showHolidayDialog = false">Cancel</Button>
                    <Button type="submit" :disabled="holidayForm.processing">Add</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
