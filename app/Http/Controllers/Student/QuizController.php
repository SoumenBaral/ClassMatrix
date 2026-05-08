<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QuizController extends Controller
{
    public function index(): Response
    {
        $student = auth()->user()->student;
        abort_unless($student, 403);

        $quizzes = Quiz::where('section_id', $student->current_section_id)
            ->with('subject:id,name,code')
            ->withCount('questions')
            ->orderByDesc('available_from')
            ->get()
            ->map(function ($quiz) use ($student) {
                $attempt = $quiz->attempts()->where('student_id', $student->id)->latest()->first();
                $now = now();

                $status = 'upcoming';
                if ($attempt?->submitted_at) {
                    $status = 'completed';
                } elseif ($attempt && !$attempt->submitted_at) {
                    $status = 'in_progress';
                } elseif ($quiz->available_from && $quiz->available_until) {
                    if ($now->between($quiz->available_from, $quiz->available_until)) {
                        $status = 'available';
                    } elseif ($now->gt($quiz->available_until)) {
                        $status = 'expired';
                    }
                } elseif ($quiz->available_from && $now->gte($quiz->available_from)) {
                    $status = 'available';
                }

                return [
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                    'subject' => $quiz->subject?->name,
                    'duration_minutes' => $quiz->duration_minutes,
                    'total_marks' => $quiz->total_marks,
                    'questions_count' => $quiz->questions_count,
                    'available_from' => $quiz->available_from?->format('M d, Y h:i A'),
                    'available_until' => $quiz->available_until?->format('M d, Y h:i A'),
                    'status' => $status,
                    'score' => $attempt?->score,
                    'attempt_id' => $attempt?->id,
                ];
            });

        return Inertia::render('student/Quizzes/Index', [
            'quizzes' => $quizzes,
        ]);
    }

    public function show(Quiz $quiz): Response
    {
        $student = auth()->user()->student;
        abort_unless($student, 403);
        abort_unless($quiz->section_id === $student->current_section_id, 403);

        // Check if already completed
        $existingAttempt = $quiz->attempts()
            ->where('student_id', $student->id)
            ->whereNotNull('submitted_at')
            ->first();

        if ($existingAttempt) {
            return redirect()->route('student.quizzes.result', $quiz);
        }

        // Check availability
        $now = now();
        if ($quiz->available_from && $now->lt($quiz->available_from)) {
            return redirect()->route('student.quizzes.index')
                ->with('error', 'This quiz is not available yet.');
        }
        if ($quiz->available_until && $now->gt($quiz->available_until)) {
            return redirect()->route('student.quizzes.index')
                ->with('error', 'This quiz has expired.');
        }

        // Get or create attempt
        $attempt = $quiz->attempts()
            ->where('student_id', $student->id)
            ->whereNull('submitted_at')
            ->first();

        if (!$attempt) {
            $attempt = QuizAttempt::create([
                'quiz_id' => $quiz->id,
                'student_id' => $student->id,
                'started_at' => now(),
            ]);
        }

        $questions = $quiz->questions()
            ->with('options:id,question_id,text')
            ->orderBy('order')
            ->get()
            ->map(fn ($q) => [
                'id' => $q->id,
                'question' => $q->question,
                'type' => $q->type->value,
                'marks' => $q->marks,
                'options' => $q->options->map(fn ($o) => [
                    'id' => $o->id,
                    'text' => $o->text,
                ]),
            ]);

        // Load any existing answers for this attempt
        $existingAnswers = $attempt->answers()->pluck('answer', 'question_id');

        return Inertia::render('student/Quizzes/Show', [
            'quiz' => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'subject' => $quiz->subject?->name,
                'duration_minutes' => $quiz->duration_minutes,
                'total_marks' => $quiz->total_marks,
            ],
            'questions' => $questions,
            'attempt' => [
                'id' => $attempt->id,
                'started_at' => $attempt->started_at->toISOString(),
            ],
            'existingAnswers' => $existingAnswers,
        ]);
    }

    public function submit(Request $request, Quiz $quiz): RedirectResponse
    {
        $student = auth()->user()->student;
        abort_unless($student, 403);
        abort_unless($quiz->section_id === $student->current_section_id, 403);

        $attempt = $quiz->attempts()
            ->where('student_id', $student->id)
            ->whereNull('submitted_at')
            ->firstOrFail();

        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:quiz_questions,id',
            'answers.*.answer' => 'nullable|string|max:5000',
        ]);

        $totalScore = 0;
        $questions = $quiz->questions()->with('options')->get()->keyBy('id');

        foreach ($validated['answers'] as $answerData) {
            $question = $questions[$answerData['question_id']] ?? null;
            if (!$question) continue;

            $answer = $answerData['answer'] ?? '';
            $isCorrect = false;
            $marks = 0;

            if ($question->type->value === 'mcq') {
                $correctOption = $question->options->firstWhere('is_correct', true);
                $isCorrect = $correctOption && (string) $correctOption->id === $answer;
                $marks = $isCorrect ? $question->marks : 0;
            } elseif ($question->type->value === 'true_false') {
                $correctOption = $question->options->firstWhere('is_correct', true);
                $isCorrect = $correctOption && (string) $correctOption->id === $answer;
                $marks = $isCorrect ? $question->marks : 0;
            } else {
                // Short answer — needs manual grading, award 0 for now
                $isCorrect = false;
                $marks = 0;
            }

            $totalScore += $marks;

            QuizAnswer::updateOrCreate(
                ['attempt_id' => $attempt->id, 'question_id' => $question->id],
                ['answer' => $answer, 'is_correct' => $isCorrect, 'marks' => $marks],
            );
        }

        $attempt->update([
            'submitted_at' => now(),
            'score' => $totalScore,
        ]);

        return redirect()->route('student.quizzes.result', $quiz)
            ->with('success', 'Quiz submitted successfully!');
    }

    public function result(Quiz $quiz): Response
    {
        $student = auth()->user()->student;
        abort_unless($student, 403);
        abort_unless($quiz->section_id === $student->current_section_id, 403);

        $attempt = $quiz->attempts()
            ->where('student_id', $student->id)
            ->whereNotNull('submitted_at')
            ->latest()
            ->firstOrFail();

        $answers = $attempt->answers()
            ->with([
                'question:id,question,type,marks,order',
                'question.options:id,question_id,text,is_correct',
            ])
            ->get()
            ->sortBy('question.order')
            ->values()
            ->map(fn ($a) => [
                'question' => $a->question->question,
                'type' => $a->question->type->value,
                'max_marks' => $a->question->marks,
                'answer' => $a->answer,
                'is_correct' => $a->is_correct,
                'marks' => $a->marks,
                'options' => $a->question->options->map(fn ($o) => [
                    'id' => $o->id,
                    'text' => $o->text,
                    'is_correct' => $o->is_correct,
                ]),
            ]);

        return Inertia::render('student/Quizzes/Result', [
            'quiz' => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'subject' => $quiz->subject?->name,
                'total_marks' => $quiz->total_marks,
            ],
            'attempt' => [
                'score' => $attempt->score,
                'started_at' => $attempt->started_at->format('M d, Y h:i A'),
                'submitted_at' => $attempt->submitted_at->format('M d, Y h:i A'),
            ],
            'answers' => $answers,
        ]);
    }
}
