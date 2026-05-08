<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { CalendarCheck, ChevronLeft, ChevronRight, Clock, CheckCircle, XCircle, AlertTriangle } from 'lucide-vue-next'

interface AttendanceRecord {
    date: string
    day: string
    status: string
    label: string
    color: string
    remarks: string | null
}

const props = defineProps<{
    records: Record<string, AttendanceRecord>
    summary: {
        total_days: number
        present: number
        absent: number
        late: number
        excused: number
        half_day: number
        rate: number
    }
    month: string
    monthLabel: string
    daysInMonth: number
    firstDayOfWeek: number
}>()

const weekDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']

// Build calendar grid
const calendarDays = computed(() => {
    const days: (AttendanceRecord | null)[] = []

    // Padding for first day
    for (let i = 1; i < props.firstDayOfWeek; i++) {
        days.push(null)
    }

    // Actual days
    for (let d = 1; d <= props.daysInMonth; d++) {
        const dateStr = `${props.month}-${String(d).padStart(2, '0')}`
        days.push(props.records[dateStr] ?? {
            date: dateStr,
            day: String(d),
            status: 'none',
            label: '',
            color: '',
            remarks: null,
        })
    }

    return days
})

function navigateMonth(direction: number) {
    const [y, m] = props.month.split('-').map(Number)
    const date = new Date(y, m - 1 + direction, 1)
    const newMonth = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`
    router.visit(`/student/attendance?month=${newMonth}`, { preserveState: true })
}

function statusColor(status: string): string {
    const colors: Record<string, string> = {
        present: 'bg-green-500 text-white',
        absent: 'bg-red-500 text-white',
        late: 'bg-amber-500 text-white',
        excused: 'bg-blue-500 text-white',
        half_day: 'bg-orange-400 text-white',
        none: 'bg-muted/50 text-muted-foreground/30',
    }
    return colors[status] ?? colors.none
}

function statusDot(status: string): string {
    const colors: Record<string, string> = {
        present: 'bg-green-500',
        absent: 'bg-red-500',
        late: 'bg-amber-500',
        excused: 'bg-blue-500',
        half_day: 'bg-orange-400',
    }
    return colors[status] ?? ''
}

const rateColor = computed(() => {
    if (props.summary.rate >= 90) return 'text-green-600 dark:text-green-400'
    if (props.summary.rate >= 75) return 'text-amber-600 dark:text-amber-400'
    return 'text-red-600 dark:text-red-400'
})
</script>

<template>
    <Head title="My Attendance" />
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div>
            <h1 class="text-2xl font-bold">My Attendance</h1>
            <p class="mt-1 text-sm text-muted-foreground">Track your daily attendance records</p>
        </div>

        <!-- Summary cards -->
        <div class="grid gap-4 grid-cols-2 sm:grid-cols-3 lg:grid-cols-6">
            <div class="rounded-xl border border-border bg-card p-4">
                <div class="text-xs text-muted-foreground mb-1">Attendance Rate</div>
                <div :class="['text-2xl font-bold', rateColor]">{{ summary.rate }}%</div>
            </div>
            <div class="rounded-xl border border-border bg-card p-4">
                <div class="text-xs text-muted-foreground mb-1 flex items-center gap-1"><CheckCircle class="size-3 text-green-500" /> Present</div>
                <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ summary.present }}</div>
            </div>
            <div class="rounded-xl border border-border bg-card p-4">
                <div class="text-xs text-muted-foreground mb-1 flex items-center gap-1"><XCircle class="size-3 text-red-500" /> Absent</div>
                <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ summary.absent }}</div>
            </div>
            <div class="rounded-xl border border-border bg-card p-4">
                <div class="text-xs text-muted-foreground mb-1 flex items-center gap-1"><Clock class="size-3 text-amber-500" /> Late</div>
                <div class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ summary.late }}</div>
            </div>
            <div class="rounded-xl border border-border bg-card p-4">
                <div class="text-xs text-muted-foreground mb-1 flex items-center gap-1"><AlertTriangle class="size-3 text-blue-500" /> Excused</div>
                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ summary.excused }}</div>
            </div>
            <div class="rounded-xl border border-border bg-card p-4">
                <div class="text-xs text-muted-foreground mb-1">Total Days</div>
                <div class="text-2xl font-bold">{{ summary.total_days }}</div>
            </div>
        </div>

        <!-- Month navigation -->
        <div class="flex items-center justify-between">
            <button @click="navigateMonth(-1)" class="inline-flex items-center gap-1 rounded-lg border border-border px-3 py-2 text-sm hover:bg-accent transition-colors">
                <ChevronLeft class="size-4" /> Previous
            </button>
            <h2 class="text-lg font-semibold">{{ monthLabel }}</h2>
            <button @click="navigateMonth(1)" class="inline-flex items-center gap-1 rounded-lg border border-border px-3 py-2 text-sm hover:bg-accent transition-colors">
                Next <ChevronRight class="size-4" />
            </button>
        </div>

        <!-- Calendar grid -->
        <div class="rounded-xl border border-border bg-card p-4 overflow-hidden">
            <!-- Weekday headers -->
            <div class="grid grid-cols-7 gap-1 mb-2">
                <div v-for="day in weekDays" :key="day" class="text-center text-xs font-medium text-muted-foreground py-2">
                    {{ day }}
                </div>
            </div>

            <!-- Calendar cells -->
            <div class="grid grid-cols-7 gap-1">
                <div
                    v-for="(day, i) in calendarDays"
                    :key="i"
                    :class="[
                        'relative aspect-square rounded-lg flex flex-col items-center justify-center text-sm transition-colors',
                        day ? 'cursor-default' : '',
                        day?.status === 'none' ? 'bg-muted/20' : '',
                        day?.status && day.status !== 'none' ? statusColor(day.status) : '',
                    ]"
                    :title="day?.label ? `${day.label}${day.remarks ? ': ' + day.remarks : ''}` : ''"
                >
                    <template v-if="day">
                        <span :class="['font-medium', day.status === 'none' ? 'text-muted-foreground/40' : '']">
                            {{ day.day }}
                        </span>
                        <span v-if="day.status !== 'none'" class="text-[10px] leading-tight mt-0.5 opacity-80 hidden sm:block">
                            {{ day.label }}
                        </span>
                    </template>
                </div>
            </div>
        </div>

        <!-- Legend -->
        <div class="flex flex-wrap items-center gap-4 text-xs text-muted-foreground">
            <span class="font-medium">Legend:</span>
            <span class="flex items-center gap-1.5"><span :class="['size-3 rounded-full', statusDot('present')]" /> Present</span>
            <span class="flex items-center gap-1.5"><span :class="['size-3 rounded-full', statusDot('absent')]" /> Absent</span>
            <span class="flex items-center gap-1.5"><span :class="['size-3 rounded-full', statusDot('late')]" /> Late</span>
            <span class="flex items-center gap-1.5"><span :class="['size-3 rounded-full', statusDot('excused')]" /> Excused</span>
            <span class="flex items-center gap-1.5"><span :class="['size-3 rounded-full', statusDot('half_day')]" /> Half Day</span>
        </div>
    </div>
</template>
