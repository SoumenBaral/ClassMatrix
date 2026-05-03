<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Lesson;
use App\Models\Section;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LessonController extends Controller
{
    public function index(Request $request): Response
    {
        $currentYear = AcademicYear::current();
        $sectionId = $request->input('section_id');
        $subjectId = $request->input('subject_id');

        $sections = Section::with('classLevel')
            ->where('academic_year_id', $currentYear?->id)
            ->get()
            ->map(fn ($s) => ['id' => $s->id, 'name' => $s->classLevel->name . ' - ' . $s->name]);

        $query = Lesson::with(['subject:id,name,code', 'section.classLevel', 'creator:id,name'])
            ->withCount('materials');

        if ($sectionId) {
            $query->where('section_id', $sectionId);
        }
        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        return Inertia::render('admin/Learning/Lessons', [
            'lessons' => $query->orderByDesc('created_at')->paginate(20)->through(fn ($l) => [
                'id' => $l->id,
                'title' => $l->title,
                'subject' => $l->subject->name,
                'section' => $l->section->classLevel->name . ' - ' . $l->section->name,
                'video_url' => $l->video_url,
                'published' => $l->isPublished(),
                'published_at' => $l->published_at?->format('Y-m-d'),
                'materials_count' => $l->materials_count,
                'creator' => $l->creator->name,
                'order' => $l->order,
            ]),
            'sections' => $sections,
            'subjects' => Subject::select('id', 'name')->orderBy('name')->get(),
            'filters' => $request->only('section_id', 'subject_id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'content' => 'nullable|string',
            'video_url' => 'nullable|url|max:500',
            'order' => 'integer|min:0',
            'published_at' => 'nullable|date',
        ]);

        $validated['created_by'] = $request->user()->id;
        $validated['published_at'] ??= now();

        Lesson::create($validated);

        return back()->with('flash', ['type' => 'success', 'message' => 'Lesson created.']);
    }

    public function update(Request $request, Lesson $lesson): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'video_url' => 'nullable|url|max:500',
            'order' => 'integer|min:0',
            'published_at' => 'nullable|date',
        ]);

        $lesson->update($validated);

        return back()->with('flash', ['type' => 'success', 'message' => 'Lesson updated.']);
    }

    public function destroy(Lesson $lesson): RedirectResponse
    {
        $lesson->delete();

        return back()->with('flash', ['type' => 'success', 'message' => 'Lesson deleted.']);
    }
}
