<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\GradeScheme;
use App\Models\Mark;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MarkController extends Controller
{
    public function index(Request $request): Response
    {
        $currentYear = AcademicYear::current();
        $examId = $request->input('exam_id');
        $scheduleId = $request->input('schedule_id');
        $sectionId = $request->input('section_id');

        $exams = Exam::with('examType:id,name')
            ->where('academic_year_id', $currentYear?->id)
            ->orderByDesc('start_date')
            ->get();

        $schedules = [];
        if ($examId) {
            $schedules = ExamSchedule::with(['classLevel:id,name', 'subject:id,name,code'])
                ->where('exam_id', $examId)
                ->get();
        }

        $sections = [];
        $schedule = null;
        if ($scheduleId) {
            $schedule = ExamSchedule::with(['classLevel', 'subject'])->find($scheduleId);
            if ($schedule) {
                $sections = Section::where('class_level_id', $schedule->class_level_id)
                    ->where('academic_year_id', $currentYear?->id)
                    ->get()
                    ->map(fn ($s) => ['id' => $s->id, 'name' => $s->name]);
            }
        }

        $students = [];
        $marks = [];
        if ($sectionId && $scheduleId) {
            $students = Student::where('current_section_id', $sectionId)
                ->where('status', 'active')
                ->with('user:id,name')
                ->orderBy('roll_no')
                ->get()
                ->map(fn ($s) => [
                    'id' => $s->id,
                    'name' => $s->user->name,
                    'roll_no' => $s->roll_no,
                ]);

            $marks = Mark::where('exam_schedule_id', $scheduleId)
                ->whereIn('student_id', $students->pluck('id'))
                ->get()
                ->keyBy('student_id')
                ->map(fn ($m) => [
                    'marks_obtained' => $m->marks_obtained,
                    'grade' => $m->grade,
                    'remarks' => $m->remarks,
                ]);
        }

        return Inertia::render('admin/Marks/Index', [
            'exams' => $exams,
            'schedules' => $schedules,
            'sections' => $sections,
            'students' => $students,
            'marks' => $marks,
            'selectedExamId' => (int) $examId,
            'selectedScheduleId' => (int) $scheduleId,
            'selectedSectionId' => (int) $sectionId,
            'schedule' => $schedule,
        ]);
    }

    public function bulkStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'exam_schedule_id' => 'required|exists:exam_schedules,id',
            'records' => 'required|array',
            'records.*.student_id' => 'required|exists:students,id',
            'records.*.marks_obtained' => 'nullable|numeric|min:0',
            'records.*.remarks' => 'nullable|string|max:255',
        ]);

        $schedule = ExamSchedule::findOrFail($validated['exam_schedule_id']);
        $gradeScheme = GradeScheme::default();

        foreach ($validated['records'] as $record) {
            $marksObtained = $record['marks_obtained'];
            $grade = null;

            if ($marksObtained !== null && $gradeScheme) {
                $pct = ($marksObtained / $schedule->full_marks) * 100;
                $grade = $gradeScheme->gradeFor($pct);
            }

            Mark::updateOrCreate(
                [
                    'exam_schedule_id' => $validated['exam_schedule_id'],
                    'student_id' => $record['student_id'],
                ],
                [
                    'marks_obtained' => $marksObtained,
                    'grade' => $grade,
                    'remarks' => $record['remarks'] ?? null,
                    'entered_by' => $request->user()->id,
                ],
            );
        }

        return back()->with('flash', ['type' => 'success', 'message' => 'Marks saved for ' . count($validated['records']) . ' students.']);
    }

    // --- Results / Report Card view ---

    public function results(Request $request): Response
    {
        $currentYear = AcademicYear::current();
        $examId = $request->input('exam_id');
        $sectionId = $request->input('section_id');

        $exams = Exam::with('examType:id,name')
            ->where('academic_year_id', $currentYear?->id)
            ->get();

        $sections = Section::with('classLevel')
            ->where('academic_year_id', $currentYear?->id)
            ->get()
            ->map(fn ($s) => ['id' => $s->id, 'name' => $s->classLevel->name . ' - ' . $s->name, 'class_level_id' => $s->class_level_id]);

        $results = [];
        $subjects = [];

        if ($examId && $sectionId) {
            $section = Section::with('classLevel')->find($sectionId);
            $schedules = ExamSchedule::with('subject:id,name,code')
                ->where('exam_id', $examId)
                ->where('class_level_id', $section->class_level_id)
                ->get();

            $subjects = $schedules->map(fn ($s) => [
                'id' => $s->subject_id,
                'name' => $s->subject->name,
                'code' => $s->subject->code,
                'full_marks' => $s->full_marks,
                'pass_marks' => $s->pass_marks,
                'schedule_id' => $s->id,
            ]);

            $students = Student::where('current_section_id', $sectionId)
                ->where('status', 'active')
                ->with('user:id,name')
                ->orderBy('roll_no')
                ->get();

            $allMarks = Mark::whereIn('exam_schedule_id', $schedules->pluck('id'))
                ->get()
                ->groupBy('student_id');

            $gradeScheme = GradeScheme::default()?->load('ranges');

            $results = $students->map(function ($student) use ($schedules, $allMarks, $gradeScheme) {
                $studentMarks = $allMarks->get($student->id, collect());
                $totalObtained = 0;
                $totalFull = 0;
                $subjectResults = [];

                foreach ($schedules as $schedule) {
                    $mark = $studentMarks->firstWhere('exam_schedule_id', $schedule->id);
                    $obtained = $mark?->marks_obtained;
                    $subjectResults[$schedule->subject_id] = [
                        'marks' => $obtained,
                        'grade' => $mark?->grade,
                        'pass' => $obtained !== null ? $obtained >= $schedule->pass_marks : null,
                    ];
                    if ($obtained !== null) {
                        $totalObtained += $obtained;
                        $totalFull += $schedule->full_marks;
                    }
                }

                $pct = $totalFull > 0 ? round(($totalObtained / $totalFull) * 100, 2) : 0;

                return [
                    'id' => $student->id,
                    'name' => $student->user->name,
                    'roll_no' => $student->roll_no,
                    'subjects' => $subjectResults,
                    'total_obtained' => $totalObtained,
                    'total_full' => $totalFull,
                    'percentage' => $pct,
                    'grade' => $gradeScheme?->gradeFor($pct),
                    'gpa' => $gradeScheme?->gpaFor($pct),
                ];
            })->sortByDesc('percentage')->values();
        }

        return Inertia::render('admin/Marks/Results', [
            'exams' => $exams,
            'sections' => $sections,
            'selectedExamId' => (int) $examId,
            'selectedSectionId' => (int) $sectionId,
            'results' => $results,
            'subjects' => $subjects,
        ]);
    }
}
