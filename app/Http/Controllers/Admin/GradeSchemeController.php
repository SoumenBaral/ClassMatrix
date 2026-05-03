<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GradeScheme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GradeSchemeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/Grades/Index', [
            'schemes' => GradeScheme::with('ranges')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'is_default' => 'boolean',
            'ranges' => 'required|array|min:1',
            'ranges.*.grade' => 'required|string|max:5',
            'ranges.*.min_pct' => 'required|numeric|min:0|max:100',
            'ranges.*.max_pct' => 'required|numeric|min:0|max:100|gte:ranges.*.min_pct',
            'ranges.*.gpa' => 'nullable|numeric|min:0|max:10',
        ]);

        if ($validated['is_default'] ?? false) {
            GradeScheme::where('is_default', true)->update(['is_default' => false]);
        }

        $scheme = GradeScheme::create([
            'name' => $validated['name'],
            'is_default' => $validated['is_default'] ?? false,
        ]);

        $scheme->ranges()->createMany($validated['ranges']);

        return back()->with('flash', ['type' => 'success', 'message' => 'Grade scheme created.']);
    }

    public function update(Request $request, GradeScheme $gradeScheme): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'is_default' => 'boolean',
            'ranges' => 'required|array|min:1',
            'ranges.*.grade' => 'required|string|max:5',
            'ranges.*.min_pct' => 'required|numeric|min:0|max:100',
            'ranges.*.max_pct' => 'required|numeric|min:0|max:100|gte:ranges.*.min_pct',
            'ranges.*.gpa' => 'nullable|numeric|min:0|max:10',
        ]);

        if ($validated['is_default'] ?? false) {
            GradeScheme::where('is_default', true)->where('id', '!=', $gradeScheme->id)->update(['is_default' => false]);
        }

        $gradeScheme->update([
            'name' => $validated['name'],
            'is_default' => $validated['is_default'] ?? false,
        ]);

        $gradeScheme->ranges()->delete();
        $gradeScheme->ranges()->createMany($validated['ranges']);

        return back()->with('flash', ['type' => 'success', 'message' => 'Grade scheme updated.']);
    }

    public function destroy(GradeScheme $gradeScheme): RedirectResponse
    {
        $gradeScheme->delete();

        return back()->with('flash', ['type' => 'success', 'message' => 'Grade scheme deleted.']);
    }
}
