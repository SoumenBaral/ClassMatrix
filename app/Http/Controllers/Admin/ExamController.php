<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassLevel;
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\ExamType;
use App\Models\Subject;
use App\Models\Term;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExamController extends Controller
{
    public function index(): Response
    {
        $currentYear = AcademicYear::current();

        return Inertia::render('admin/Exams/Index', [
            'exams' => Exam::with(['examType:id,name', 'term:id,name'])
                ->withCount('schedules')
                ->where('academic_year_id', $currentYear?->id)
                ->orderByDesc('start_date')
                ->get(),
            'examTypes' => ExamType::all(),
            'terms' => $currentYear ? Term::where('academic_year_id', $currentYear->id)->get() : [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'exam_type_id' => 'required|exists:exam_types,id',
            'name' => 'required|string|max:100',
            'term_id' => 'nullable|exists:terms,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:upcoming,ongoing,completed,cancelled',
        ]);

        $validated['academic_year_id'] = AcademicYear::current()->id;

        Exam::create($validated);

        return back()->with('flash', ['type' => 'success', 'message' => 'Exam created.']);
    }

    public function update(Request $request, Exam $exam): RedirectResponse
    {
        $validated = $request->validate([
            'exam_type_id' => 'required|exists:exam_types,id',
            'name' => 'required|string|max:100',
            'term_id' => 'nullable|exists:terms,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:upcoming,ongoing,completed,cancelled',
        ]);

        $exam->update($validated);

        return back()->with('flash', ['type' => 'success', 'message' => 'Exam updated.']);
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        $exam->delete();

        return back()->with('flash', ['type' => 'success', 'message' => 'Exam deleted.']);
    }

    // --- Exam Schedule ---

    public function schedules(Exam $exam): Response
    {
        return Inertia::render('admin/Exams/Schedules', [
            'exam' => $exam->load('examType:id,name'),
            'schedules' => ExamSchedule::with(['classLevel:id,name', 'subject:id,name,code'])
                ->where('exam_id', $exam->id)
                ->orderBy('exam_date')
                ->get(),
            'classLevels' => ClassLevel::orderBy('numeric_order')->get(),
            'subjects' => Subject::orderBy('name')->get(),
        ]);
    }

    public function storeSchedule(Request $request, Exam $exam): RedirectResponse
    {
        $validated = $request->validate([
            'class_level_id' => 'required|exists:class_levels,id',
            'subject_id' => 'required|exists:subjects,id',
            'exam_date' => 'nullable|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'full_marks' => 'required|numeric|min:1',
            'pass_marks' => 'required|numeric|min:0|lte:full_marks',
            'room' => 'nullable|string|max:20',
        ]);

        $validated['exam_id'] = $exam->id;

        ExamSchedule::create($validated);

        return back()->with('flash', ['type' => 'success', 'message' => 'Schedule added.']);
    }

    public function destroySchedule(ExamSchedule $examSchedule): RedirectResponse
    {
        $examSchedule->delete();

        return back()->with('flash', ['type' => 'success', 'message' => 'Schedule removed.']);
    }
}
