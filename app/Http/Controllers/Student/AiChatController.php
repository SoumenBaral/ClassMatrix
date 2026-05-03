<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AiChat;
use App\Services\AI\ChatService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AiChatController extends Controller
{
    public function index(): Response
    {
        $chats = auth()->user()->aiChats()
            ->orderByDesc('updated_at')
            ->take(50)
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'title' => $c->title,
                'subject_context' => $c->subject_context,
                'updated_at' => $c->updated_at->diffForHumans(),
            ]);

        return Inertia::render('student/Chat/Index', [
            'chats' => $chats,
        ]);
    }

    public function show(AiChat $aiChat): Response
    {
        abort_unless($aiChat->user_id === auth()->id(), 403);

        $chats = auth()->user()->aiChats()
            ->orderByDesc('updated_at')
            ->take(50)
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'title' => $c->title,
                'subject_context' => $c->subject_context,
                'updated_at' => $c->updated_at->diffForHumans(),
            ]);

        return Inertia::render('student/Chat/Show', [
            'chat' => [
                'id' => $aiChat->id,
                'title' => $aiChat->title,
                'subject_context' => $aiChat->subject_context,
            ],
            'messages' => $aiChat->messages()->orderBy('created_at')->get()->map(fn ($m) => [
                'id' => $m->id,
                'role' => $m->role,
                'content' => $m->content,
                'created_at' => $m->created_at->format('H:i'),
            ]),
            'chats' => $chats,
        ]);
    }

    public function create(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject_context' => 'nullable|string|max:100',
        ]);

        $chat = auth()->user()->aiChats()->create([
            'title' => 'New Chat',
            'subject_context' => $validated['subject_context'] ?? null,
        ]);

        return redirect()->route('student.chat.show', $chat);
    }

    public function send(Request $request, AiChat $aiChat, ChatService $service): RedirectResponse
    {
        abort_unless($aiChat->user_id === auth()->id(), 403);

        $validated = $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        // ChatService now handles errors gracefully — saves error as assistant message
        $service->sendMessage($request->user(), $aiChat, $validated['message']);

        return back();
    }

    public function destroy(AiChat $aiChat): RedirectResponse
    {
        abort_unless($aiChat->user_id === auth()->id(), 403);

        $aiChat->delete();

        return redirect()->route('student.chat.index')
            ->with('flash', ['type' => 'success', 'message' => 'Chat deleted.']);
    }
}
