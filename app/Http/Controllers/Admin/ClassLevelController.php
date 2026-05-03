<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassLevel;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClassLevelController extends Controller
{
    public function index(): Response
    {
        $currentYear = AcademicYear::current();

        return Inertia::render('admin/Classes/Index', [
            'classLevels' => ClassLevel::with(['sections' => fn ($q) => $q->where('academic_year_id', $currentYear?->id)->withCount('students')])
                ->orderBy('numeric_order')
                ->get(),
            'teachers' => fn () => User::where('user_type', 'teacher')
                ->select('id', 'name')
                ->orderBy('name')
                ->get(),
            'currentAcademicYear' => $currentYear,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'numeric_order' => 'required|integer|min:1|unique:class_levels,numeric_order',
        ]);

        ClassLevel::create($validated);

        return back()->with('flash', ['type' => 'success', 'message' => 'Class created.']);
    }

    public function update(Request $request, ClassLevel $classLevel): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'numeric_order' => "required|integer|min:1|unique:class_levels,numeric_order,{$classLevel->id}",
        ]);

        $classLevel->update($validated);

        return back()->with('flash', ['type' => 'success', 'message' => 'Class updated.']);
    }

    public function destroy(ClassLevel $classLevel): RedirectResponse
    {
        $classLevel->delete();

        return back()->with('flash', ['type' => 'success', 'message' => 'Class deleted.']);
    }
}
