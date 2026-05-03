<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentAttendance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceController extends Controller
{
    public function index(Request $request): Response
    {
        $currentYear = AcademicYear::current();
        $sectionId = $request->input('section_id');
        $date = $request->input('date', now()->format('Y-m-d'));

        $sections = Section::with('classLevel')
            ->where('academic_year_id', $currentYear?->id)
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->classLevel->name . ' - ' . $s->name,
            ]);

        $students = [];
        $attendances = [];

        if ($sectionId) {
            $students = Student::where('current_section_id', $sectionId)
                ->where('status', 'active')
                ->with('user:id,name')
                ->orderBy('roll_no')
                ->get()
                ->map(fn ($s) => [
                    'id' => $s->id,
                    'name' => $s->user->name,
                    'roll_no' => $s->roll_no,
                    'admission_no' => $s->admission_no,
                ]);

            $attendances = StudentAttendance::where('section_id', $sectionId)
                ->where('date', $date)
                ->whereNull('period_id')
                ->get()
                ->keyBy('student_id')
                ->map(fn ($a) => [
                    'id' => $a->id,
                    'status' => $a->status->value,
                    'remarks' => $a->remarks,
                ]);
        }

        return Inertia::render('admin/Attendance/Index', [
            'sections' => $sections,
            'selectedSectionId' => (int) $sectionId,
            'date' => $date,
            'students' => $students,
            'attendances' => $attendances,
        ]);
    }

    public function bulkStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'section_id' => 'required|exists:sections,id',
            'date' => 'required|date',
            'records' => 'required|array',
            'records.*.student_id' => 'required|exists:students,id',
            'records.*.status' => 'required|in:present,absent,late,excused,half_day',
            'records.*.remarks' => 'nullable|string|max:255',
        ]);

        foreach ($validated['records'] as $record) {
            StudentAttendance::updateOrCreate(
                [
                    'student_id' => $record['student_id'],
                    'date' => $validated['date'],
                    'period_id' => null,
                ],
                [
                    'section_id' => $validated['section_id'],
                    'status' => $record['status'],
                    'remarks' => $record['remarks'] ?? null,
                    'marked_by' => $request->user()->id,
                ],
            );
        }

        return back()->with('flash', ['type' => 'success', 'message' => 'Attendance saved for ' . count($validated['records']) . ' students.']);
    }

    public function report(Request $request): Response
    {
        $currentYear = AcademicYear::current();
        $sectionId = $request->input('section_id');
        $month = $request->input('month', now()->format('Y-m'));

        $sections = Section::with('classLevel')
            ->where('academic_year_id', $currentYear?->id)
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->classLevel->name . ' - ' . $s->name,
            ]);

        $report = [];
        if ($sectionId) {
            $startDate = \Carbon\Carbon::parse($month . '-01');
            $endDate = $startDate->copy()->endOfMonth();

            $students = Student::where('current_section_id', $sectionId)
                ->where('status', 'active')
                ->with('user:id,name')
                ->orderBy('roll_no')
                ->get();

            $attendances = StudentAttendance::where('section_id', $sectionId)
                ->whereBetween('date', [$startDate, $endDate])
                ->whereNull('period_id')
                ->get()
                ->groupBy('student_id');

            $workingDays = StudentAttendance::where('section_id', $sectionId)
                ->whereBetween('date', [$startDate, $endDate])
                ->whereNull('period_id')
                ->distinct('date')
                ->count('date');

            $report = $students->map(function ($student) use ($attendances, $workingDays) {
                $records = $attendances->get($student->id, collect());
                $present = $records->whereIn('status', ['present', 'late', 'half_day'])->count();

                return [
                    'id' => $student->id,
                    'name' => $student->user->name,
                    'roll_no' => $student->roll_no,
                    'total_days' => $workingDays,
                    'present' => $present,
                    'absent' => $records->where('status', 'absent')->count(),
                    'late' => $records->where('status', 'late')->count(),
                    'excused' => $records->where('status', 'excused')->count(),
                    'percentage' => $workingDays > 0 ? round(($present / $workingDays) * 100, 1) : 0,
                ];
            });
        }

        return Inertia::render('admin/Attendance/Report', [
            'sections' => $sections,
            'selectedSectionId' => (int) $sectionId,
            'month' => $month,
            'report' => $report,
        ]);
    }
}
