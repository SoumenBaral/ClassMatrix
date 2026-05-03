<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AcademicYearController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/AcademicYears/Index', [
            'academicYears' => AcademicYear::withCount('terms', 'sections')
                ->orderByDesc('start_date')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:academic_years,name',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_current' => 'boolean',
        ]);

        $year = AcademicYear::create($validated);

        if ($validated['is_current'] ?? false) {
            $year->markAsCurrent();
        }

        return back()->with('flash', ['type' => 'success', 'message' => 'Academic year created.']);
    }

    public function update(Request $request, AcademicYear $academicYear): RedirectResponse
    {
        $validated = $request->validate([
            'name' => "required|string|max:50|unique:academic_years,name,{$academicYear->id}",
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_current' => 'boolean',
        ]);

        $academicYear->update($validated);

        if ($validated['is_current'] ?? false) {
            $academicYear->markAsCurrent();
        }

        return back()->with('flash', ['type' => 'success', 'message' => 'Academic year updated.']);
    }

    public function destroy(AcademicYear $academicYear): RedirectResponse
    {
        $academicYear->delete();

        return back()->with('flash', ['type' => 'success', 'message' => 'Academic year deleted.']);
    }

    public function setCurrent(AcademicYear $academicYear): RedirectResponse
    {
        $academicYear->markAsCurrent();
        cache()->forget('current_academic_year');

        return back()->with('flash', ['type' => 'success', 'message' => "'{$academicYear->name}' set as current."]);
    }
}
