<?php

namespace App\Http\Controllers;

use App\Models\Mark;
use App\Models\Notice;
use App\Models\Student;
use App\Models\StudentAttendance;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;

class ParentController extends Controller
{
    public function dashboard(): Response
    {
        $user = auth()->user();

        $children = $this->getChildren($user);

        return Inertia::render('parent/Dashboard', [
            'parent' => ['name' => $user->name],
            'children' => $children,
        ]);
    }

    public function children(): Response
    {
        $user = auth()->user();

        $children = $user->children()
            ->with(['user:id,name,email', 'section.classLevel', 'enrollments.academicYear'])
            ->get()
            ->map(function ($student) {
                $totalDays = StudentAttendance::where('student_id', $student->id)->distinct('date')->count('date');
                $presentDays = StudentAttendance::where('student_id', $student->id)
                    ->whereIn('status', ['present', 'late'])->distinct('date')->count('date');

                return [
                    'id' => $student->id,
                    'name' => $student->user->name,
                    'email' => $student->user->email,
                    'admission_no' => $student->admission_no,
                    'roll_no' => $student->roll_no,
                    'dob' => $student->dob?->format('Y-m-d'),
                    'gender' => $student->gender,
                    'blood_group' => $student->blood_group,
                    'class' => $student->section?->classLevel?->name ?? 'Not assigned',
                    'section' => $student->section?->name ?? '-',
                    'admission_date' => $student->admission_date?->format('Y-m-d'),
                    'relation' => $student->pivot->relation,
                    'attendance_rate' => $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : 0,
                    'total_days' => $totalDays,
                    'present_days' => $presentDays,
                ];
            });

        return Inertia::render('parent/Children', [
            'children' => $children,
        ]);
    }

    public function attendance(Request $request): Response
    {
        $user = auth()->user();
        $studentId = $request->input('student_id');
        $month = $request->input('month', now()->format('Y-m'));

        $childrenList = $user->children()->with('user:id,name')->get()
            ->map(fn ($s) => ['id' => $s->id, 'name' => $s->user->name]);

        $records = [];
        $summary = null;

        if ($studentId) {
            // Verify this student belongs to this parent
            abort_unless($user->children()->where('student_id', $studentId)->exists(), 403);

            $startDate = Carbon::parse($month . '-01')->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();

            $records = StudentAttendance::where('student_id', $studentId)
                ->whereBetween('date', [$startDate, $endDate])
                ->whereNull('period_id')
                ->orderBy('date')
                ->get()
                ->map(fn ($a) => [
                    'date' => $a->date->format('Y-m-d'),
                    'day' => $a->date->format('D'),
                    'status' => $a->status->value,
                    'remarks' => $a->remarks,
                ]);

            $total = $records->count();
            $present = $records->whereIn('status', ['present', 'late'])->count();
            $summary = [
                'total' => $total,
                'present' => $present,
                'absent' => $records->where('status', 'absent')->count(),
                'late' => $records->where('status', 'late')->count(),
                'excused' => $records->where('status', 'excused')->count(),
                'percentage' => $total > 0 ? round(($present / $total) * 100, 1) : 0,
            ];
        }

        return Inertia::render('parent/Attendance', [
            'children' => $childrenList,
            'selectedStudentId' => (int) $studentId,
            'month' => $month,
            'records' => $records,
            'summary' => $summary,
        ]);
    }

    public function results(Request $request): Response
    {
        $user = auth()->user();
        $studentId = $request->input('student_id');

        $childrenList = $user->children()->with('user:id,name')->get()
            ->map(fn ($s) => ['id' => $s->id, 'name' => $s->user->name]);

        $results = [];

        if ($studentId) {
            abort_unless($user->children()->where('student_id', $studentId)->exists(), 403);

            $results = Mark::where('student_id', $studentId)
                ->with([
                    'examSchedule.subject:id,name',
                    'examSchedule.exam:id,name',
                    'examSchedule:id,exam_id,subject_id,full_marks,pass_marks',
                ])
                ->orderByDesc('created_at')
                ->get()
                ->map(fn ($m) => [
                    'exam' => $m->examSchedule?->exam?->name,
                    'subject' => $m->examSchedule?->subject?->name,
                    'marks' => $m->marks_obtained,
                    'full_marks' => $m->examSchedule?->full_marks,
                    'pass_marks' => $m->examSchedule?->pass_marks,
                    'grade' => $m->grade,
                    'passed' => $m->marks_obtained !== null && $m->marks_obtained >= ($m->examSchedule?->pass_marks ?? 0),
                    'percentage' => $m->examSchedule?->full_marks > 0
                        ? round(($m->marks_obtained / $m->examSchedule->full_marks) * 100, 1)
                        : 0,
                ]);
        }

        return Inertia::render('parent/Results', [
            'children' => $childrenList,
            'selectedStudentId' => (int) $studentId,
            'results' => $results,
        ]);
    }

    public function notices(): Response
    {
        $notices = Notice::active()
            ->whereIn('target', ['all', 'parents'])
            ->with('creator:id,name')
            ->orderByDesc('published_at')
            ->take(30)
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'title' => $n->title,
                'body' => $n->body,
                'published_at' => $n->published_at->format('M d, Y'),
                'creator' => $n->creator?->name,
            ]);

        return Inertia::render('parent/Notices', [
            'notices' => $notices,
        ]);
    }

    // --- Helper ---

    protected function getChildren($user)
    {
        return $user->children()
            ->with(['user:id,name', 'section.classLevel'])
            ->get()
            ->map(function ($student) {
                $totalDays = StudentAttendance::where('student_id', $student->id)->distinct('date')->count('date');
                $presentDays = StudentAttendance::where('student_id', $student->id)
                    ->whereIn('status', ['present', 'late'])->distinct('date')->count('date');

                $recentMarks = $student->marks()
                    ->with('examSchedule.subject:id,name', 'examSchedule.exam:id,name')
                    ->latest()->take(3)->get()
                    ->map(fn ($m) => [
                        'subject' => $m->examSchedule?->subject?->name,
                        'exam' => $m->examSchedule?->exam?->name,
                        'marks' => $m->marks_obtained,
                        'total' => $m->examSchedule?->full_marks,
                        'grade' => $m->grade,
                    ]);

                return [
                    'id' => $student->id,
                    'name' => $student->user->name,
                    'admission_no' => $student->admission_no,
                    'class' => $student->section?->classLevel?->name ?? 'Not assigned',
                    'section' => $student->section?->name ?? '-',
                    'roll_no' => $student->roll_no,
                    'relation' => $student->pivot->relation,
                    'attendance' => $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : 0,
                    'recent_marks' => $recentMarks,
                ];
            });
    }
}
