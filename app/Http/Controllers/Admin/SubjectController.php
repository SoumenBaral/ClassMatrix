<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubjectController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/Subjects/Index', [
            'subjects' => Subject::with('department:id,name')
                ->withCount('classLevels')
                ->orderBy('name')
                ->get(),
            'departments' => fn () => Department::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:subjects,code',
            'department_id' => 'nullable|exists:departments,id',
            'type' => 'required|in:theory,practical,lab',
        ]);

        Subject::create($validated);

        return back()->with('flash', ['type' => 'success', 'message' => 'Subject created.']);
    }

    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => "required|string|max:20|unique:subjects,code,{$subject->id}",
            'department_id' => 'nullable|exists:departments,id',
            'type' => 'required|in:theory,practical,lab',
        ]);

        $subject->update($validated);

        return back()->with('flash', ['type' => 'success', 'message' => 'Subject updated.']);
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        $subject->delete();

        return back()->with('flash', ['type' => 'success', 'message' => 'Subject deleted.']);
    }
}
