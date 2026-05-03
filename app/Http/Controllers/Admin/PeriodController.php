<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Period;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PeriodController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/Timetable/Periods', [
            'periods' => Period::orderBy('order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'order' => 'required|integer|min:1',
            'is_break' => 'boolean',
        ]);

        Period::create($validated);

        return back()->with('flash', ['type' => 'success', 'message' => 'Period created.']);
    }

    public function update(Request $request, Period $period): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'order' => 'required|integer|min:1',
            'is_break' => 'boolean',
        ]);

        $period->update($validated);

        return back()->with('flash', ['type' => 'success', 'message' => 'Period updated.']);
    }

    public function destroy(Period $period): RedirectResponse
    {
        $period->delete();

        return back()->with('flash', ['type' => 'success', 'message' => 'Period deleted.']);
    }
}
