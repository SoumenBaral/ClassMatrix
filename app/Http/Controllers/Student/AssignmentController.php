<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AssignmentController extends Controller
{
    public function index(): Response
    {
        $student = auth()->user()->student;
        abort_unless($student, 403);

        $assignments = Assignment::where('section_id', $student->current_section_id)
            ->with('subject:id,name,code', 'teacher:id,name')
            ->withCount('submissions')
            ->orderByDesc('due_date')
            ->paginate(15)
            ->through(function ($assignment) use ($student) {
                $submission = $assignment->submissions()
                    ->where('student_id', $student->id)
                    ->first();

                return [
                    'id' => $assignment->id,
                    'title' => $assignment->title,
                    'subject' => $assignment->subject?->name,
                    'teacher' => $assignment->teacher?->name,
                    'due_date' => $assignment->due_date->format('M d, Y'),
                    'due_date_raw' => $assignment->due_date->toDateString(),
                    'total_marks' => $assignment->total_marks,
                    'is_overdue' => $assignment->isOverdue(),
                    'submission_count' => $assignment->submissions_count,
                    'status' => $submission
                        ? ($submission->isGraded() ? 'graded' : 'submitted')
                        : ($assignment->isOverdue() ? 'overdue' : 'pending'),
                    'marks_obtained' => $submission?->marks,
                ];
            });

        return Inertia::render('student/Assignments/Index', [
            'assignments' => $assignments,
        ]);
    }

    public function show(Assignment $assignment): Response
    {
        $student = auth()->user()->student;
        abort_unless($student, 403);
        abort_unless($assignment->section_id === $student->current_section_id, 403);

        $submission = $assignment->submissions()
            ->where('student_id', $student->id)
            ->first();

        return Inertia::render('student/Assignments/Show', [
            'assignment' => [
                'id' => $assignment->id,
                'title' => $assignment->title,
                'description' => $assignment->description,
                'subject' => $assignment->subject?->name,
                'teacher' => $assignment->teacher?->name,
                'due_date' => $assignment->due_date->format('M d, Y'),
                'is_overdue' => $assignment->isOverdue(),
                'total_marks' => $assignment->total_marks,
                'attachment' => $assignment->attachment,
            ],
            'submission' => $submission ? [
                'id' => $submission->id,
                'submitted_at' => $submission->submitted_at?->format('M d, Y h:i A'),
                'file' => $submission->file,
                'comment' => $submission->comment,
                'marks' => $submission->marks,
                'feedback' => $submission->feedback,
                'graded_at' => $submission->graded_at?->format('M d, Y'),
                'is_graded' => $submission->isGraded(),
            ] : null,
        ]);
    }

    public function submit(Request $request, Assignment $assignment): RedirectResponse
    {
        $student = auth()->user()->student;
        abort_unless($student, 403);
        abort_unless($assignment->section_id === $student->current_section_id, 403);

        $existing = $assignment->submissions()->where('student_id', $student->id)->exists();
        abort_if($existing, 403, 'Already submitted.');

        $validated = $request->validate([
            'comment' => 'nullable|string|max:2000',
            'file' => 'nullable|file|max:10240',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('submissions', 'public');
        }

        AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'submitted_at' => now(),
            'file' => $filePath,
            'comment' => $validated['comment'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Assignment submitted successfully.');
    }
}
