<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use Inertia\Inertia;
use Inertia\Response;

class LessonController extends Controller
{
    public function index(): Response
    {
        $student = auth()->user()->student;
        abort_unless($student, 403);

        $lessons = Lesson::where('section_id', $student->current_section_id)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with('subject:id,name,code', 'creator:id,name')
            ->withCount('materials')
            ->orderBy('subject_id')
            ->orderBy('order')
            ->get()
            ->map(fn ($lesson) => [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'subject' => $lesson->subject?->name,
                'subject_code' => $lesson->subject?->code,
                'teacher' => $lesson->creator?->name,
                'has_video' => !empty($lesson->video_url),
                'materials_count' => $lesson->materials_count,
                'published_at' => $lesson->published_at->format('M d, Y'),
            ])
            ->groupBy('subject');

        $subjects = $lessons->keys()->values();

        return Inertia::render('student/Lessons/Index', [
            'lessons' => $lessons,
            'subjects' => $subjects,
        ]);
    }

    public function show(Lesson $lesson): Response
    {
        $student = auth()->user()->student;
        abort_unless($student, 403);
        abort_unless($lesson->section_id === $student->current_section_id, 403);
        abort_unless($lesson->isPublished(), 404);

        $materials = $lesson->materials()->get()->map(fn ($m) => [
            'id' => $m->id,
            'name' => $m->name,
            'file_path' => $m->file_path,
            'type' => $m->type,
        ]);

        return Inertia::render('student/Lessons/Show', [
            'lesson' => [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'subject' => $lesson->subject?->name,
                'teacher' => $lesson->creator?->name,
                'content' => $lesson->content,
                'video_url' => $lesson->video_url,
                'published_at' => $lesson->published_at->format('M d, Y'),
            ],
            'materials' => $materials,
        ]);
    }
}
