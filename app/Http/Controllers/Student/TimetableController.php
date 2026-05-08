<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Period;
use App\Models\Timetable;
use Inertia\Inertia;
use Inertia\Response;

class TimetableController extends Controller
{
    public function __invoke(): Response
    {
        $student = auth()->user()->student;
        abort_unless($student, 403);

        $periods = Period::where('is_break', false)
            ->orderBy('order')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'start_time' => $p->start_time,
                'end_time' => $p->end_time,
            ]);

        $timetable = Timetable::where('section_id', $student->current_section_id)
            ->with([
                'period:id,name,start_time,end_time,order',
                'subject:id,name,code',
                'teacher:id,name',
            ])
            ->get()
            ->groupBy('day_of_week')
            ->map(fn ($entries) => $entries->keyBy('period_id')->map(fn ($entry) => [
                'subject' => $entry->subject?->name,
                'subject_code' => $entry->subject?->code,
                'teacher' => $entry->teacher?->name,
                'room' => $entry->room,
            ]));

        $breaks = Period::where('is_break', true)
            ->orderBy('order')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'start_time' => $p->start_time,
                'end_time' => $p->end_time,
                'order' => $p->order,
            ]);

        return Inertia::render('student/Timetable/Index', [
            'periods' => $periods,
            'timetable' => $timetable,
            'breaks' => $breaks,
            'currentDay' => now()->dayOfWeekIso,
            'className' => $student->section?->classLevel?->name,
            'sectionName' => $student->section?->name,
        ]);
    }
}
