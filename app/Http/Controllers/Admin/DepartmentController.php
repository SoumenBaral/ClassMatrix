<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DepartmentController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/Departments/Index', [
            'departments' => Department::with('head:id,name')
                ->withCount('subjects', 'staff')
                ->orderBy('name')
                ->get(),
            'teachers' => fn () => User::where('user_type', 'teacher')
                ->select('id', 'name')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:departments,code',
            'head_id' => 'nullable|exists:users,id',
        ]);

        Department::create($validated);

        return back()->with('flash', ['type' => 'success', 'message' => 'Department created.']);
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => "required|string|max:20|unique:departments,code,{$department->id}",
            'head_id' => 'nullable|exists:users,id',
        ]);

        $department->update($validated);

        return back()->with('flash', ['type' => 'success', 'message' => 'Department updated.']);
    }

    public function destroy(Department $department): RedirectResponse
    {
        $department->delete();

        return back()->with('flash', ['type' => 'success', 'message' => 'Department deleted.']);
    }
}
