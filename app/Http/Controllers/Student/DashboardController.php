<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $user = auth()->user();
        $student = $user->student;

        $upcomingAssignments = 0;
        $recentMarks = [];
        $attendanceRate = 0;

        if ($student) {
            $upcomingAssignments = \App\Models\Assignment::where('section_id', $student->current_section_id)
                ->where('due_date', '>=', now())
                ->count();

            $recentMarks = $student->marks()
                ->with('examSchedule.subject:id,name', 'examSchedule.exam:id,name')
                ->latest()
                ->take(5)
                ->get()
                ->map(fn ($m) => [
                    'subject' => $m->examSchedule?->subject?->name,
                    'exam' => $m->examSchedule?->exam?->name,
                    'marks' => $m->marks_obtained,
                    'total' => $m->examSchedule?->full_marks,
                    'grade' => $m->grade,
                ]);

            $totalDays = \App\Models\StudentAttendance::where('student_id', $student->id)->distinct('date')->count('date');
            $presentDays = \App\Models\StudentAttendance::where('student_id', $student->id)
                ->whereIn('status', ['present', 'late'])->distinct('date')->count('date');
            $attendanceRate = $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : 0;
        }

        $currentRoutine = $user->routines()->where('status', 'ready')->latest()->first();
        $chatCount = $user->aiChats()->count();

        return Inertia::render('student/Dashboard', [
            'student' => $student ? [
                'name' => $user->name,
                'class' => $student->section?->classLevel?->name,
                'section' => $student->section?->name,
                'roll_no' => $student->roll_no,
            ] : null,
            'stats' => [
                'attendanceRate' => $attendanceRate,
                'upcomingAssignments' => $upcomingAssignments,
                'recentMarks' => $recentMarks,
                'hasRoutine' => $currentRoutine !== null,
                'chatCount' => $chatCount,
            ],
        ]);
    }
}
