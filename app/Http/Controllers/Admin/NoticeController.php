<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NoticeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/Notices/Index', [
            'notices' => Notice::with('creator:id,name')
                ->orderByDesc('created_at')
                ->paginate(20)
                ->through(fn ($n) => [
                    'id' => $n->id,
                    'title' => $n->title,
                    'body' => $n->body,
                    'target' => $n->target->value,
                    'published_at' => $n->published_at?->format('Y-m-d H:i'),
                    'expires_at' => $n->expires_at?->format('Y-m-d H:i'),
                    'is_published' => $n->isPublished(),
                    'creator' => $n->creator?->name,
                    'created_at' => $n->created_at->format('Y-m-d'),
                ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'target' => 'required|in:all,students,teachers,parents,class',
            'target_id' => 'nullable|integer',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:published_at',
        ]);

        $validated['created_by'] = $request->user()->id;
        $validated['published_at'] ??= now();

        Notice::create($validated);

        return back()->with('flash', ['type' => 'success', 'message' => 'Notice created.']);
    }

    public function update(Request $request, Notice $notice): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'target' => 'required|in:all,students,teachers,parents,class',
            'target_id' => 'nullable|integer',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
        ]);

        $notice->update($validated);

        return back()->with('flash', ['type' => 'success', 'message' => 'Notice updated.']);
    }

    public function destroy(Notice $notice): RedirectResponse
    {
        $notice->delete();

        return back()->with('flash', ['type' => 'success', 'message' => 'Notice deleted.']);
    }
}
