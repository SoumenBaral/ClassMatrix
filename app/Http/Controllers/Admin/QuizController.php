<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Section;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class QuizController extends Controller
{
    public function index(): Response
    {
        $currentYear = AcademicYear::current();

        $sections = Section::with('classLevel')
            ->where('academic_year_id', $currentYear?->id)
            ->get()
            ->map(fn ($s) => ['id' => $s->id, 'name' => $s->classLevel->name . ' - ' . $s->name]);

        return Inertia::render('admin/Learning/Quizzes', [
            'quizzes' => Quiz::with(['subject:id,name', 'section.classLevel'])
                ->withCount(['questions', 'attempts'])
                ->orderByDesc('created_at')
                ->paginate(20)
                ->through(fn ($q) => [
                    'id' => $q->id,
                    'title' => $q->title,
                    'subject' => $q->subject->name,
                    'section' => $q->section->classLevel->name . ' - ' . $q->section->name,
                    'duration_minutes' => $q->duration_minutes,
                    'total_marks' => $q->total_marks,
                    'questions_count' => $q->questions_count,
                    'attempts_count' => $q->attempts_count,
                    'available_from' => $q->available_from?->format('Y-m-d H:i'),
                    'available_until' => $q->available_until?->format('Y-m-d H:i'),
                ]),
            'sections' => $sections,
            'subjects' => Subject::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'duration_minutes' => 'required|integer|min:1',
            'available_from' => 'nullable|date',
            'available_until' => 'nullable|date|after:available_from',
        ]);

        $validated['created_by'] = $request->user()->id;
        $validated['total_marks'] = 0;

        Quiz::create($validated);

        return back()->with('flash', ['type' => 'success', 'message' => 'Quiz created.']);
    }

    public function destroy(Quiz $quiz): RedirectResponse
    {
        $quiz->delete();

        return back()->with('flash', ['type' => 'success', 'message' => 'Quiz deleted.']);
    }

    // --- Questions ---

    public function questions(Quiz $quiz): Response
    {
        return Inertia::render('admin/Learning/QuizQuestions', [
            'quiz' => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'total_marks' => $quiz->total_marks,
            ],
            'questions' => $quiz->questions()->with('options')->get()->map(fn ($q) => [
                'id' => $q->id,
                'question' => $q->question,
                'type' => $q->type->value,
                'marks' => $q->marks,
                'order' => $q->order,
                'options' => $q->options->map(fn ($o) => [
                    'id' => $o->id,
                    'text' => $o->text,
                    'is_correct' => $o->is_correct,
                ]),
            ]),
        ]);
    }

    public function storeQuestion(Request $request, Quiz $quiz): RedirectResponse
    {
        $validated = $request->validate([
            'question' => 'required|string',
            'type' => 'required|in:mcq,short,true_false',
            'marks' => 'required|numeric|min:0.5',
            'order' => 'integer|min:0',
            'options' => 'required_if:type,mcq,true_false|array',
            'options.*.text' => 'required|string',
            'options.*.is_correct' => 'boolean',
        ]);

        DB::transaction(function () use ($quiz, $validated) {
            $question = $quiz->questions()->create([
                'question' => $validated['question'],
                'type' => $validated['type'],
                'marks' => $validated['marks'],
                'order' => $validated['order'] ?? $quiz->questions()->count(),
            ]);

            if (! empty($validated['options'])) {
                $question->options()->createMany($validated['options']);
            }

            $quiz->recalculateTotalMarks();
        });

        return back()->with('flash', ['type' => 'success', 'message' => 'Question added.']);
    }

    public function destroyQuestion(QuizQuestion $quizQuestion): RedirectResponse
    {
        $quiz = $quizQuestion->quiz;
        $quizQuestion->delete();
        $quiz->recalculateTotalMarks();

        return back()->with('flash', ['type' => 'success', 'message' => 'Question removed.']);
    }

    // --- Results ---

    public function results(Quiz $quiz): Response
    {
        return Inertia::render('admin/Learning/QuizResults', [
            'quiz' => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'total_marks' => $quiz->total_marks,
            ],
            'attempts' => $quiz->attempts()
                ->with('student.user:id,name')
                ->whereNotNull('submitted_at')
                ->orderByDesc('score')
                ->get()
                ->map(fn ($a) => [
                    'id' => $a->id,
                    'student_name' => $a->student->user->name,
                    'started_at' => $a->started_at->format('Y-m-d H:i'),
                    'submitted_at' => $a->submitted_at->format('Y-m-d H:i'),
                    'score' => $a->score,
                    'percentage' => $quiz->total_marks > 0
                        ? round(($a->score / $quiz->total_marks) * 100, 1)
                        : 0,
                ]),
        ]);
    }
}
