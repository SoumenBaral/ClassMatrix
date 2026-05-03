<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Section;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $currentYear = AcademicYear::current();

        $validated = $request->validate([
            'class_level_id' => 'required|exists:class_levels,id',
            'name' => 'required|string|max:10',
            'capacity' => 'integer|min:1|max:200',
            'class_teacher_id' => 'nullable|exists:users,id',
            'room_no' => 'nullable|string|max:20',
        ]);

        $validated['academic_year_id'] = $currentYear->id;

        Section::create($validated);

        return back()->with('flash', ['type' => 'success', 'message' => 'Section created.']);
    }

    public function update(Request $request, Section $section): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:10',
            'capacity' => 'integer|min:1|max:200',
            'class_teacher_id' => 'nullable|exists:users,id',
            'room_no' => 'nullable|string|max:20',
        ]);

        $section->update($validated);

        return back()->with('flash', ['type' => 'success', 'message' => 'Section updated.']);
    }

    public function destroy(Section $section): RedirectResponse
    {
        $section->delete();

        return back()->with('flash', ['type' => 'success', 'message' => 'Section deleted.']);
    }
}
