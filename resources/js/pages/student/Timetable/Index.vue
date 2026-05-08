<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Clock, MapPin, User } from 'lucide-vue-next'

interface Period {
    id: number
    name: string
    start_time: string
    end_time: string
}

interface TimetableEntry {
    subject: string | null
    subject_code: string | null
    teacher: string | null
    room: string | null
}

interface BreakPeriod {
    id: number
    name: string
    start_time: string
    end_time: string
    order: number
}

const props = defineProps<{
    periods: Period[]
    timetable: Record<string, Record<string, TimetableEntry>>
    breaks: BreakPeriod[]
    currentDay: number
    className: string | null
    sectionName: string | null
}>()

const days = [
    { num: 1, name: 'Monday', short: 'Mon' },
    { num: 2, name: 'Tuesday', short: 'Tue' },
    { num: 3, name: 'Wednesday', short: 'Wed' },
    { num: 4, name: 'Thursday', short: 'Thu' },
    { num: 5, name: 'Friday', short: 'Fri' },
    { num: 6, name: 'Saturday', short: 'Sat' },
]

// Mobile: show only one day at a time
const selectedDay = ref(props.currentDay)

function getEntry(day: number, periodId: number): TimetableEntry | null {
    return props.timetable[day]?.[periodId] ?? null
}

function formatTime(time: string): string {
    if (!time) return ''
    const [h, m] = time.split(':')
    const hour = parseInt(h)
    const ampm = hour >= 12 ? 'PM' : 'AM'
    const h12 = hour % 12 || 12
    return `${h12}:${m} ${ampm}`
}

const subjectColors: Record<string, string> = {}
const colorPalette = [
    'border-l-blue-500 bg-blue-500/5',
    'border-l-purple-500 bg-purple-500/5',
    'border-l-cyan-500 bg-cyan-500/5',
    'border-l-amber-500 bg-amber-500/5',
    'border-l-emerald-500 bg-emerald-500/5',
    'border-l-pink-500 bg-pink-500/5',
    'border-l-indigo-500 bg-indigo-500/5',
    'border-l-orange-500 bg-orange-500/5',
    'border-l-teal-500 bg-teal-500/5',
]
let colorIndex = 0

function getSubjectColor(subject: string | null): string {
    if (!subject) return 'border-l-gray-300 bg-muted/30'
    if (!subjectColors[subject]) {
        subjectColors[subject] = colorPalette[colorIndex % colorPalette.length]
        colorIndex++
    }
    return subjectColors[subject]
}
</script>

<template>
    <Head title="My Timetable" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <!-- Header -->
        <div>
            <h1 class="text-2xl font-bold">My Timetable</h1>
            <p class="mt-1 text-sm text-muted-foreground">
                {{ className }} - Section {{ sectionName }}
            </p>
        </div>

        <!-- Day selector (mobile) -->
        <div class="flex gap-2 overflow-x-auto pb-2 lg:hidden">
            <button
                v-for="day in days"
                :key="day.num"
                @click="selectedDay = day.num"
                :class="[
                    'flex-shrink-0 rounded-lg px-4 py-2 text-sm font-medium transition-colors',
                    selectedDay === day.num
                        ? 'bg-primary text-primary-foreground'
                        : day.num === currentDay
                            ? 'bg-primary/10 text-primary'
                            : 'bg-muted text-muted-foreground hover:bg-accent',
                ]"
            >
                {{ day.short }}
            </button>
        </div>

        <!-- Desktop: Full grid -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr>
                        <th class="w-32 border border-border bg-muted/50 px-3 py-2.5 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">
                            <Clock class="inline size-3.5 mr-1" /> Period
                        </th>
                        <th
                            v-for="day in days"
                            :key="day.num"
                            :class="[
                                'border border-border px-3 py-2.5 text-center text-sm font-semibold',
                                day.num === currentDay ? 'bg-primary/10 text-primary' : 'bg-muted/50',
                            ]"
                        >
                            {{ day.name }}
                            <span v-if="day.num === currentDay" class="ml-1.5 inline-block size-1.5 rounded-full bg-primary" />
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="period in periods" :key="period.id">
                        <td class="border border-border bg-muted/30 px-3 py-2">
                            <div class="text-sm font-medium">{{ period.name }}</div>
                            <div class="text-xs text-muted-foreground">
                                {{ formatTime(period.start_time) }} - {{ formatTime(period.end_time) }}
                            </div>
                        </td>
                        <td
                            v-for="day in days"
                            :key="day.num"
                            :class="[
                                'border border-border px-3 py-2',
                                day.num === currentDay ? 'bg-primary/[0.03]' : '',
                            ]"
                        >
                            <template v-if="getEntry(day.num, period.id)">
                                <div :class="['rounded-lg border-l-3 px-3 py-2', getSubjectColor(getEntry(day.num, period.id)?.subject)]">
                                    <div class="text-sm font-semibold">{{ getEntry(day.num, period.id)?.subject }}</div>
                                    <div class="mt-0.5 flex items-center gap-2 text-xs text-muted-foreground">
                                        <span v-if="getEntry(day.num, period.id)?.teacher" class="flex items-center gap-1">
                                            <User class="size-3" /> {{ getEntry(day.num, period.id)?.teacher }}
                                        </span>
                                        <span v-if="getEntry(day.num, period.id)?.room" class="flex items-center gap-1">
                                            <MapPin class="size-3" /> {{ getEntry(day.num, period.id)?.room }}
                                        </span>
                                    </div>
                                </div>
                            </template>
                            <template v-else>
                                <div class="rounded-lg bg-muted/20 px-3 py-2.5 text-center text-xs text-muted-foreground/50">—</div>
                            </template>
                        </td>
                    </tr>
                    <!-- Break rows -->
                    <tr v-for="br in breaks" :key="'break-' + br.id" class="bg-amber-50/50 dark:bg-amber-950/10">
                        <td class="border border-border px-3 py-1.5">
                            <div class="text-sm font-medium text-amber-600 dark:text-amber-400">{{ br.name }}</div>
                            <div class="text-xs text-muted-foreground">
                                {{ formatTime(br.start_time) }} - {{ formatTime(br.end_time) }}
                            </div>
                        </td>
                        <td v-for="day in days" :key="day.num" class="border border-border px-3 py-1.5 text-center text-xs text-amber-500/60">
                            ☕
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Mobile: Single day view -->
        <div class="lg:hidden space-y-3">
            <div v-for="period in periods" :key="period.id">
                <div v-if="getEntry(selectedDay, period.id)" :class="['rounded-xl border border-border p-4 border-l-3', getSubjectColor(getEntry(selectedDay, period.id)?.subject)]">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-muted-foreground">{{ period.name }}</span>
                        <span class="text-xs text-muted-foreground">{{ formatTime(period.start_time) }} - {{ formatTime(period.end_time) }}</span>
                    </div>
                    <div class="mt-2 text-base font-semibold">{{ getEntry(selectedDay, period.id)?.subject }}</div>
                    <div class="mt-1 flex items-center gap-3 text-xs text-muted-foreground">
                        <span v-if="getEntry(selectedDay, period.id)?.teacher" class="flex items-center gap-1">
                            <User class="size-3" /> {{ getEntry(selectedDay, period.id)?.teacher }}
                        </span>
                        <span v-if="getEntry(selectedDay, period.id)?.room" class="flex items-center gap-1">
                            <MapPin class="size-3" /> {{ getEntry(selectedDay, period.id)?.room }}
                        </span>
                    </div>
                </div>
                <div v-else class="rounded-xl border border-dashed border-border/50 p-4 text-center text-sm text-muted-foreground/50">
                    {{ period.name }} · Free Period
                </div>
            </div>

            <!-- Breaks on mobile -->
            <div v-for="br in breaks" :key="'m-break-' + br.id" class="rounded-xl border border-amber-200 bg-amber-50/50 p-3 text-center dark:border-amber-800/30 dark:bg-amber-950/10">
                <span class="text-sm text-amber-600 dark:text-amber-400">☕ {{ br.name }}</span>
                <span class="ml-2 text-xs text-muted-foreground">{{ formatTime(br.start_time) }} - {{ formatTime(br.end_time) }}</span>
            </div>
        </div>
    </div>
</template>
