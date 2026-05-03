<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ParentLinkController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');

        $parentsQuery = User::where('user_type', 'parent')
            ->with(['children.user:id,name', 'children:id,user_id,admission_no,roll_no']);

        if ($search) {
            $parentsQuery->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"));
        }

        return Inertia::render('admin/ParentLinks/Index', [
            'parents' => $parentsQuery->orderBy('name')->paginate(20)->through(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'email' => $p->email,
                'phone' => $p->phone,
                'children' => $p->children->map(fn ($s) => [
                    'id' => $s->id,
                    'name' => $s->user->name,
                    'admission_no' => $s->admission_no,
                    'relation' => $s->pivot->relation,
                    'is_primary' => $s->pivot->is_primary,
                ]),
            ]),
            'students' => fn () => Student::with('user:id,name')
                ->where('status', 'active')
                ->get()
                ->map(fn ($s) => [
                    'id' => $s->id,
                    'name' => $s->user->name,
                    'admission_no' => $s->admission_no,
                ]),
            'filters' => $request->only('search'),
        ]);
    }

    public function link(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'parent_id' => 'required|exists:users,id',
            'student_id' => 'required|exists:students,id',
            'relation' => 'required|in:father,mother,guardian,other',
            'is_primary' => 'boolean',
        ]);

        // Verify user is actually a parent
        $parent = User::findOrFail($validated['parent_id']);
        if (! $parent->isParent()) {
            return back()->withErrors(['parent_id' => 'Selected user is not a parent.']);
        }

        // Check if already linked
        if ($parent->children()->where('student_id', $validated['student_id'])->exists()) {
            return back()->withErrors(['student_id' => 'This student is already linked to this parent.']);
        }

        $parent->children()->attach($validated['student_id'], [
            'relation' => $validated['relation'],
            'is_primary' => $validated['is_primary'] ?? false,
        ]);

        return back()->with('flash', ['type' => 'success', 'message' => 'Student linked to parent.']);
    }

    public function unlink(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'parent_id' => 'required|exists:users,id',
            'student_id' => 'required|exists:students,id',
        ]);

        $parent = User::findOrFail($validated['parent_id']);
        $parent->children()->detach($validated['student_id']);

        return back()->with('flash', ['type' => 'success', 'message' => 'Student unlinked from parent.']);
    }
}
