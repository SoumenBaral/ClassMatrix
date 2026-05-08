<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentAttendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $student = auth()->user()->student;
        abort_unless($student, 403);

        $month = $request->input('month', now()->format('Y-m'));
        $date = Carbon::createFromFormat('Y-m', $month);
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        // Get daily attendance records for the month
        $records = StudentAttendance::where('student_id', $student->id)
            ->whereNull('period_id')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('date')
            ->get()
            ->map(fn ($a) => [
                'date' => $a->date->format('Y-m-d'),
                'day' => $a->date->format('d'),
                'status' => $a->status->value,
                'label' => $a->status->label(),
                'color' => $a->status->color(),
                'remarks' => $a->remarks,
            ])
            ->keyBy('date');

        // Calculate summary stats
        $totalDays = StudentAttendance::where('student_id', $student->id)
            ->whereNull('period_id')
            ->distinct('date')
            ->count('date');

        $summary = [
            'total_days' => $totalDays,
            'present' => StudentAttendance::where('student_id', $student->id)
                ->whereNull('period_id')
                ->where('status', 'present')
                ->distinct('date')->count('date'),
            'absent' => StudentAttendance::where('student_id', $student->id)
                ->whereNull('period_id')
                ->where('status', 'absent')
                ->distinct('date')->count('date'),
            'late' => StudentAttendance::where('student_id', $student->id)
                ->whereNull('period_id')
                ->where('status', 'late')
                ->distinct('date')->count('date'),
            'excused' => StudentAttendance::where('student_id', $student->id)
                ->whereNull('period_id')
                ->where('status', 'excused')
                ->distinct('date')->count('date'),
            'half_day' => StudentAttendance::where('student_id', $student->id)
                ->whereNull('period_id')
                ->where('status', 'half_day')
                ->distinct('date')->count('date'),
        ];
        $summary['rate'] = $totalDays > 0
            ? round((($summary['present'] + $summary['late'] + $summary['half_day']) / $totalDays) * 100, 1)
            : 0;

        return Inertia::render('student/Attendance/Index', [
            'records' => $records,
            'summary' => $summary,
            'month' => $month,
            'monthLabel' => $date->format('F Y'),
            'daysInMonth' => $endOfMonth->day,
            'firstDayOfWeek' => $startOfMonth->dayOfWeekIso,
        ]);
    }
}
