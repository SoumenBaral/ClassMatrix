<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassLevel;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FeeController extends Controller
{
    public function index(): Response
    {
        $currentYear = AcademicYear::current();

        return Inertia::render('admin/Fees/Index', [
            'structures' => FeeStructure::with(['classLevel:id,name', 'category:id,name'])
                ->where('academic_year_id', $currentYear?->id)
                ->orderBy('class_level_id')
                ->get(),
            'categories' => FeeCategory::all(),
            'classLevels' => ClassLevel::orderBy('numeric_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class_level_id' => 'required|exists:class_levels,id',
            'fee_category_id' => 'required|exists:fee_categories,id',
            'amount' => 'required|numeric|min:0',
            'frequency' => 'required|in:monthly,quarterly,yearly,one-time',
        ]);

        $validated['academic_year_id'] = AcademicYear::current()->id;

        FeeStructure::updateOrCreate(
            [
                'class_level_id' => $validated['class_level_id'],
                'fee_category_id' => $validated['fee_category_id'],
                'academic_year_id' => $validated['academic_year_id'],
            ],
            ['amount' => $validated['amount'], 'frequency' => $validated['frequency']],
        );

        return back()->with('flash', ['type' => 'success', 'message' => 'Fee structure saved.']);
    }

    public function destroy(FeeStructure $feeStructure): RedirectResponse
    {
        $feeStructure->delete();

        return back()->with('flash', ['type' => 'success', 'message' => 'Fee structure removed.']);
    }

    // --- Categories ---
    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:fee_categories,name',
            'description' => 'nullable|string|max:255',
        ]);

        FeeCategory::create($validated);

        return back()->with('flash', ['type' => 'success', 'message' => 'Fee category created.']);
    }

    public function destroyCategory(FeeCategory $feeCategory): RedirectResponse
    {
        $feeCategory->delete();

        return back()->with('flash', ['type' => 'success', 'message' => 'Fee category deleted.']);
    }
}
