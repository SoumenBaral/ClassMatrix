<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Mark;
use Inertia\Inertia;
use Inertia\Response;

class ResultController extends Controller
{
    public function __invoke(): Response
    {
        $student = auth()->user()->student;
        abort_unless($student, 403);

        $classLevelId = $student->section?->classLevel?->id;

        // Get all exams that have schedules for student's class level
        $exams = Exam::whereHas('schedules', fn ($q) => $q->where('class_level_id', $classLevelId))
            ->with('examType:id,name')
            ->orderByDesc('start_date')
            ->get()
            ->map(fn ($exam) => [
                'id' => $exam->id,
                'name' => $exam->name,
                'type' => $exam->examType?->name,
                'start_date' => $exam->start_date?->format('M d, Y'),
            ]);

        // Get all marks grouped by exam
        $marks = Mark::where('student_id', $student->id)
            ->with([
                'examSchedule:id,exam_id,subject_id,full_marks,pass_marks',
                'examSchedule.exam:id,name',
                'examSchedule.subject:id,name,code',
            ])
            ->get()
            ->groupBy(fn ($m) => $m->examSchedule?->exam_id)
            ->map(function ($examMarks) {
                $subjects = $examMarks->map(fn ($m) => [
                    'subject' => $m->examSchedule?->subject?->name,
                    'marks_obtained' => $m->marks_obtained,
                    'full_marks' => $m->examSchedule?->full_marks,
                    'pass_marks' => $m->examSchedule?->pass_marks,
                    'grade' => $m->grade,
                    'percentage' => $m->percentage(),
                    'is_passing' => $m->isPassing(),
                ]);

                $totalObtained = $subjects->sum('marks_obtained');
                $totalFull = $subjects->sum('full_marks');
                $passedCount = $subjects->where('is_passing', true)->count();

                return [
                    'subjects' => $subjects->values(),
                    'total_obtained' => $totalObtained,
                    'total_full' => $totalFull,
                    'percentage' => $totalFull > 0 ? round(($totalObtained / $totalFull) * 100, 2) : 0,
                    'passed_count' => $passedCount,
                    'total_subjects' => $subjects->count(),
                ];
            });

        return Inertia::render('student/Results/Index', [
            'exams' => $exams,
            'results' => $marks,
            'className' => $student->section?->classLevel?->name,
            'sectionName' => $student->section?->name,
        ]);
    }
}
