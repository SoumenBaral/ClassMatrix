<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\Section;
use App\Models\TeacherSubject;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $user = auth()->user();
        $currentYear = AcademicYear::current();

        $teachingSections = TeacherSubject::with(['section.classLevel', 'subject:id,name'])
            ->where('teacher_id', $user->id)
            ->where('academic_year_id', $currentYear?->id)
            ->get()
            ->map(fn ($ts) => [
                'section' => $ts->section->classLevel->name . ' - ' . $ts->section->name,
                'subject' => $ts->subject->name,
            ]);

        $classSections = Section::with('classLevel')
            ->where('class_teacher_id', $user->id)
            ->where('academic_year_id', $currentYear?->id)
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->classLevel->name . ' - ' . $s->name,
                'students' => $s->students()->count(),
            ]);

        $pendingAssignments = Assignment::where('teacher_id', $user->id)
            ->where('due_date', '>=', now())
            ->withCount('submissions')
            ->orderBy('due_date')
            ->take(5)
            ->get()
            ->map(fn ($a) => [
                'title' => $a->title,
                'due_date' => $a->due_date->format('M d'),
                'submissions' => $a->submissions_count,
            ]);

        return Inertia::render('teacher/Dashboard', [
            'teacher' => [
                'name' => $user->name,
                'designation' => $user->staff?->designation ?? 'Teacher',
            ],
            'stats' => [
                'teachingSections' => $teachingSections->count(),
                'classSections' => $classSections,
                'subjects' => $teachingSections,
                'pendingAssignments' => $pendingAssignments,
            ],
        ]);
    }
}
