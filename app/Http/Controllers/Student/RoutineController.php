<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\RoutinePreference;
use App\Services\AI\RoutineService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoutineController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        return Inertia::render('student/Routines/Index', [
            'routine' => $user->routines()
                ->where('status', 'ready')
                ->latest()
                ->first(),
            'generatingRoutine' => $user->routines()
                ->where('status', 'generating')
                ->exists(),
            'preferences' => $user->routinePreference,
        ]);
    }

    public function preferences(): Response
    {
        $user = auth()->user();
        $subjects = [];

        if ($user->student?->section) {
            $subjects = $user->student->section->classLevel
                ->subjects()
                ->select('subjects.id', 'subjects.name')
                ->get();
        }

        return Inertia::render('student/Routines/Preferences', [
            'preferences' => $user->routinePreference,
            'subjects' => $subjects,
        ]);
    }

    public function savePreferences(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'wake_up_time' => 'required|date_format:H:i',
            'sleep_time' => 'required|date_format:H:i',
            'focus_minutes' => 'required|integer|min:15|max:120',
            'peak_focus' => 'required|in:morning,afternoon,evening',
            'blocked_times' => 'nullable|array',
            'learning_goals' => 'nullable|array',
            'subject_priorities' => 'nullable|array',
            'include_weekend' => 'boolean',
        ]);

        RoutinePreference::updateOrCreate(
            ['user_id' => $request->user()->id],
            $validated,
        );

        return back()->with('flash', ['type' => 'success', 'message' => 'Preferences saved.']);
    }

    public function generate(Request $request, RoutineService $service): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'in:weekly,monthly',
        ]);

        $type = $validated['type'] ?? 'weekly';
        $weekStart = now()->startOfWeek();

        try {
            $service->generate($request->user(), $weekStart, $type);

            return back()->with('flash', ['type' => 'success', 'message' => 'Routine generated successfully!']);
        } catch (\Throwable $e) {
            return back()->with('flash', ['type' => 'error', 'message' => 'Failed to generate routine. Please try again.']);
        }
    }
}
