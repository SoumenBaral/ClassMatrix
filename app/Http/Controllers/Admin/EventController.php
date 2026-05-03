<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Holiday;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function index(Request $request): Response
    {
        $month = $request->input('month', now()->format('Y-m'));
        $start = \Carbon\Carbon::parse($month . '-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $events = Event::whereBetween('start_at', [$start, $end])
            ->orderBy('start_at')
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'title' => $e->title,
                'description' => $e->description,
                'start_at' => $e->start_at->format('Y-m-d H:i'),
                'end_at' => $e->end_at->format('Y-m-d H:i'),
                'start_date' => $e->start_at->format('Y-m-d'),
                'type' => $e->type->value,
                'color' => $e->color ?? $e->type->color(),
                'location' => $e->location,
            ]);

        $holidays = Holiday::whereBetween('date', [$start, $end])
            ->orderBy('date')
            ->get();

        return Inertia::render('admin/Calendar/Index', [
            'events' => $events,
            'holidays' => $holidays,
            'month' => $month,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after_or_equal:start_at',
            'type' => 'required|in:holiday,exam,meeting,cultural,sports,other',
            'audience' => 'nullable|in:all,students,teachers,parents',
            'color' => 'nullable|string|max:10',
            'location' => 'nullable|string|max:255',
        ]);

        $validated['created_by'] = $request->user()->id;

        Event::create($validated);

        return back()->with('flash', ['type' => 'success', 'message' => 'Event created.']);
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after_or_equal:start_at',
            'type' => 'required|in:holiday,exam,meeting,cultural,sports,other',
            'audience' => 'nullable|in:all,students,teachers,parents',
            'color' => 'nullable|string|max:10',
            'location' => 'nullable|string|max:255',
        ]);

        $event->update($validated);

        return back()->with('flash', ['type' => 'success', 'message' => 'Event updated.']);
    }

    public function destroy(Event $event): RedirectResponse
    {
        $event->delete();

        return back()->with('flash', ['type' => 'success', 'message' => 'Event deleted.']);
    }

    // --- Holidays ---

    public function storeHoliday(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'recurring' => 'boolean',
        ]);

        Holiday::create($validated);

        return back()->with('flash', ['type' => 'success', 'message' => 'Holiday added.']);
    }

    public function destroyHoliday(Holiday $holiday): RedirectResponse
    {
        $holiday->delete();

        return back()->with('flash', ['type' => 'success', 'message' => 'Holiday removed.']);
    }
}
