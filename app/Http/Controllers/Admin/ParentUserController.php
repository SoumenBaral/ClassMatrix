<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ParentUserController extends Controller
{
    public function index(Request $request): Response
    {
        $query = User::where('user_type', 'parent')->withCount('children');

        if ($search = $request->input('search')) {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%"));
        }

        return Inertia::render('admin/Parents/Index', [
            'parents' => $query->orderByDesc('created_at')->paginate(20)->through(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'email' => $p->email,
                'phone' => $p->phone,
                'status' => $p->status->value,
                'children_count' => $p->children_count,
                'created_at' => $p->created_at->format('Y-m-d'),
            ]),
            'filters' => $request->only('search'),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->isParent(), 404);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|max:255|unique:users,email,{$user->id}",
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update($validated);

        return back()->with('flash', ['type' => 'success', 'message' => 'Parent updated.']);
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_unless($user->isParent(), 404);

        $user->delete();

        return back()->with('flash', ['type' => 'success', 'message' => 'Parent deleted.']);
    }
}
