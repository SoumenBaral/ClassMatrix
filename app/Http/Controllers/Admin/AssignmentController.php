<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Section;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AssignmentController extends Controller
{
    public function index(Request $request): Response
    {
        $currentYear = AcademicYear::current();

        $sections = Section::with('classLevel')
            ->where('academic_year_id', $currentYear?->id)
            ->get()
            ->map(fn ($s) => ['id' => $s->id, 'name' => $s->classLevel->name . ' - ' . $s->name]);

        $query = Assignment::with(['subject:id,name', 'section.classLevel', 'teacher:id,name'])
            ->withCount('submissions');

        if ($sectionId = $request->input('section_id')) {
            $query->where('section_id', $sectionId);
        }

        return Inertia::render('admin/Learning/Assignments', [
            'assignments' => $query->orderByDesc('due_date')->paginate(20)->through(fn ($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'subject' => $a->subject->name,
                'section' => $a->section->classLevel->name . ' - ' . $a->section->name,
                'teacher' => $a->teacher->name,
                'due_date' => $a->due_date->format('Y-m-d'),
                'total_marks' => $a->total_marks,
                'submissions_count' => $a->submissions_count,
                'is_overdue' => $a->isOverdue(),
            ]),
            'sections' => $sections,
            'subjects' => Subject::select('id', 'name')->orderBy('name')->get(),
            'filters' => $request->only('section_id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'due_date' => 'required|date',
            'total_marks' => 'required|numeric|min:1',
        ]);

        $validated['teacher_id'] = $request->user()->id;

        Assignment::create($validated);

        return back()->with('flash', ['type' => 'success', 'message' => 'Assignment created.']);
    }

    public function update(Request $request, Assignment $assignment): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'total_marks' => 'required|numeric|min:1',
        ]);

        $assignment->update($validated);

        return back()->with('flash', ['type' => 'success', 'message' => 'Assignment updated.']);
    }

    public function destroy(Assignment $assignment): RedirectResponse
    {
        $assignment->delete();

        return back()->with('flash', ['type' => 'success', 'message' => 'Assignment deleted.']);
    }

    // --- Submissions ---

    public function submissions(Assignment $assignment): Response
    {
        return Inertia::render('admin/Learning/Submissions', [
            'assignment' => [
                'id' => $assignment->id,
                'title' => $assignment->title,
                'subject' => $assignment->subject->name,
                'section' => $assignment->section->classLevel->name . ' - ' . $assignment->section->name,
                'due_date' => $assignment->due_date->format('Y-m-d'),
                'total_marks' => $assignment->total_marks,
            ],
            'submissions' => $assignment->submissions()
                ->with(['student.user:id,name', 'student:id,user_id,roll_no', 'grader:id,name'])
                ->orderBy('submitted_at')
                ->get()
                ->map(fn ($s) => [
                    'id' => $s->id,
                    'student_name' => $s->student->user->name,
                    'roll_no' => $s->student->roll_no,
                    'submitted_at' => $s->submitted_at->format('Y-m-d H:i'),
                    'comment' => $s->comment,
                    'marks' => $s->marks,
                    'feedback' => $s->feedback,
                    'graded' => $s->isGraded(),
                    'grader' => $s->grader?->name,
                ]),
        ]);
    }

    public function gradeSubmission(Request $request, AssignmentSubmission $submission): RedirectResponse
    {
        $validated = $request->validate([
            'marks' => 'required|numeric|min:0|max:' . $submission->assignment->total_marks,
            'feedback' => 'nullable|string|max:1000',
        ]);

        $submission->update([
            'marks' => $validated['marks'],
            'feedback' => $validated['feedback'] ?? null,
            'graded_at' => now(),
            'graded_by' => $request->user()->id,
        ]);

        return back()->with('flash', ['type' => 'success', 'message' => 'Submission graded.']);
    }
}
