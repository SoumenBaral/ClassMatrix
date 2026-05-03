<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Period;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Timetable;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TimetableController extends Controller
{
    public function index(Request $request): Response
    {
        $currentYear = AcademicYear::current();
        $sectionId = $request->input('section_id');

        $sections = Section::with('classLevel')
            ->where('academic_year_id', $currentYear?->id)
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->classLevel->name . ' - ' . $s->name,
            ]);

        $timetable = [];
        if ($sectionId) {
            $timetable = Timetable::with(['period', 'subject:id,name,code', 'teacher:id,name'])
                ->where('section_id', $sectionId)
                ->get()
                ->groupBy('day_of_week')
                ->map(fn ($entries) => $entries->keyBy('period_id'));
        }

        return Inertia::render('admin/Timetable/Index', [
            'sections' => $sections,
            'selectedSectionId' => (int) $sectionId,
            'timetable' => $timetable,
            'periods' => Period::orderBy('order')->get(),
            'subjects' => fn () => Subject::select('id', 'name', 'code')->orderBy('name')->get(),
            'teachers' => fn () => User::where('user_type', 'teacher')->select('id', 'name')->orderBy('name')->get(),
            'days' => [
                ['id' => 1, 'name' => 'Monday'],
                ['id' => 2, 'name' => 'Tuesday'],
                ['id' => 3, 'name' => 'Wednesday'],
                ['id' => 4, 'name' => 'Thursday'],
                ['id' => 5, 'name' => 'Friday'],
                ['id' => 6, 'name' => 'Saturday'],
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'section_id' => 'required|exists:sections,id',
            'day_of_week' => 'required|integer|between:1,7',
            'period_id' => 'required|exists:periods,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'teacher_id' => 'nullable|exists:users,id',
            'room' => 'nullable|string|max:20',
        ]);

        // Check for teacher conflict
        if ($validated['teacher_id']) {
            $conflict = Timetable::where('teacher_id', $validated['teacher_id'])
                ->where('day_of_week', $validated['day_of_week'])
                ->where('period_id', $validated['period_id'])
                ->where('section_id', '!=', $validated['section_id'])
                ->with('section.classLevel')
                ->first();

            if ($conflict) {
                return back()->withErrors([
                    'teacher_id' => "Teacher is already assigned to {$conflict->section->classLevel->name} - {$conflict->section->name} for this period.",
                ]);
            }
        }

        Timetable::updateOrCreate(
            [
                'section_id' => $validated['section_id'],
                'day_of_week' => $validated['day_of_week'],
                'period_id' => $validated['period_id'],
            ],
            [
                'subject_id' => $validated['subject_id'],
                'teacher_id' => $validated['teacher_id'],
                'room' => $validated['room'],
            ],
        );

        return back()->with('flash', ['type' => 'success', 'message' => 'Timetable slot saved.']);
    }

    public function destroy(Timetable $timetable): RedirectResponse
    {
        $timetable->delete();

        return back()->with('flash', ['type' => 'success', 'message' => 'Timetable slot removed.']);
    }

    public function bulkStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'section_id' => 'required|exists:sections,id',
            'entries' => 'required|array',
            'entries.*.day_of_week' => 'required|integer|between:1,7',
            'entries.*.period_id' => 'required|exists:periods,id',
            'entries.*.subject_id' => 'nullable|exists:subjects,id',
            'entries.*.teacher_id' => 'nullable|exists:users,id',
            'entries.*.room' => 'nullable|string|max:20',
        ]);

        foreach ($validated['entries'] as $entry) {
            Timetable::updateOrCreate(
                [
                    'section_id' => $validated['section_id'],
                    'day_of_week' => $entry['day_of_week'],
                    'period_id' => $entry['period_id'],
                ],
                [
                    'subject_id' => $entry['subject_id'] ?? null,
                    'teacher_id' => $entry['teacher_id'] ?? null,
                    'room' => $entry['room'] ?? null,
                ],
            );
        }

        return back()->with('flash', ['type' => 'success', 'message' => 'Timetable saved.']);
    }
}
