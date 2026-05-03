<?php

namespace App\Services\AI;

use App\Models\AiLog;
use App\Models\Routine;
use App\Models\User;
use Carbon\Carbon;
use OpenAI\Laravel\Facades\OpenAI;

class RoutineService
{
    public function generate(User $user, Carbon $weekStart, string $type = 'weekly'): Routine
    {
        $routine = Routine::create([
            'user_id' => $user->id,
            'academic_year_id' => current_academic_year()?->id,
            'week_start_date' => $weekStart,
            'type' => $type,
            'status' => 'generating',
        ]);

        $model = config('services.openai.model');
        $startMs = hrtime(true);

        try {
            $response = OpenAI::chat()->create([
                'model' => $model,
                'max_tokens' => config('services.openai.max_tokens'),
                'response_format' => [
                    'type' => 'json_schema',
                    'json_schema' => [
                        'name' => 'study_routine',
                        'strict' => true,
                        'schema' => self::jsonSchema(),
                    ],
                ],
                'messages' => [
                    ['role' => 'system', 'content' => $this->systemPrompt($user)],
                    ['role' => 'user', 'content' => $this->userPrompt($user, $weekStart, $type)],
                ],
            ]);

            $data = json_decode($response->choices[0]->message->content, true);
            $usage = $response->usage;
            $cost = $this->cost($model, $usage->promptTokens, $usage->completionTokens);
            $latency = (int) ((hrtime(true) - $startMs) / 1e6);

            $routine->update([
                'status' => 'ready',
                'weekly_goals' => $data['weekly_goals'],
                'study_tips' => $data['study_tips'],
                'ai_summary' => $data['summary'],
                'blocks' => $data['days'],
                'model_used' => $model,
                'total_tokens' => $usage->totalTokens,
                'cost_usd' => $cost,
            ]);

            AiLog::create([
                'user_id' => $user->id,
                'feature' => 'routine',
                'model' => $model,
                'prompt_tokens' => $usage->promptTokens,
                'completion_tokens' => $usage->completionTokens,
                'cost_usd' => $cost,
                'latency_ms' => min($latency, 65535),
                'status' => 'success',
            ]);
        } catch (\Throwable $e) {
            $routine->update(['status' => 'failed']);

            AiLog::create([
                'user_id' => $user->id,
                'feature' => 'routine',
                'model' => $model,
                'prompt_tokens' => 0,
                'completion_tokens' => 0,
                'cost_usd' => 0,
                'latency_ms' => min((int) ((hrtime(true) - $startMs) / 1e6), 65535),
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }

        return $routine;
    }

    protected function systemPrompt(User $user): string
    {
        return "You are an expert academic coach and personal study planner for school students.

Rules:
- Never schedule during blocked times, school hours, or sleep
- Allocate MORE time to weak subjects (lower percentage scores)
- Schedule difficult subjects during the student's peak focus time
- Mix activities: focused study, revision, practice problems, breaks, exercise, free time
- Use 30-60 minute study blocks with breaks in between
- Include meals (breakfast, lunch, dinner) and at least 30 min daily physical activity
- Don't repeat the same subject more than 2 hours consecutively
- Include 15-30 min daily revision of school lessons
- Keep weekends lighter but include exam prep if exams are within 14 days
- Provide motivational, specific, and actionable goals and tips
- Respond ONLY with the JSON schema provided";
    }

    protected function userPrompt(User $user, Carbon $weekStart, string $type): string
    {
        $student = $user->student;
        $prefs = $user->routinePreference;
        $period = $type === 'monthly' ? '4 weeks starting' : 'the week of';

        // Build performance summary
        $performance = '{}';
        if ($student) {
            $marks = $student->marks()
                ->with('examSchedule.subject')
                ->latest()
                ->take(30)
                ->get();

            if ($marks->isNotEmpty()) {
                $perf = $marks->groupBy(fn ($m) => $m->examSchedule?->subject?->name ?? 'Unknown')
                    ->map(fn ($group) => round($group->avg(fn ($m) => $m->examSchedule && $m->examSchedule->full_marks > 0
                        ? ($m->marks_obtained / $m->examSchedule->full_marks) * 100 : 0), 1));
                $performance = json_encode($perf);
            }
        }

        $subjects = 'Not enrolled';
        $schoolHours = 'Mon-Fri 8:00-14:30';
        if ($student?->section) {
            $section = $student->section->load('classLevel');
            $subjects = $section->classLevel->subjects->pluck('name')->join(', ') ?: 'General';
        }

        $blocked = json_encode($prefs?->blocked_times ?? []);
        $priorities = json_encode($prefs?->subject_priorities ?? []);
        $goals = $prefs?->learning_goals ? implode('; ', $prefs->learning_goals) : 'None set';

        return "Generate a personalized study routine for {$period} {$weekStart->format('Y-m-d (l)')}.

STUDENT INFO:
- Class: " . ($student?->section?->classLevel?->name ?? 'Unknown') . "
- Subjects: {$subjects}

RECENT PERFORMANCE (% by subject):
{$performance}

SCHEDULE:
- School hours: {$schoolHours}
- Wake up: " . ($prefs?->wake_up_time ?? '06:00') . "
- Sleep: " . ($prefs?->sleep_time ?? '22:00') . "
- Blocked times: {$blocked}

PREFERENCES:
- Peak focus time: " . ($prefs?->peak_focus ?? 'morning') . "
- Focus block duration: " . ($prefs?->focus_minutes ?? 45) . " minutes
- Subject priorities (1-5): {$priorities}
- Learning goals: {$goals}
- Include weekends: " . ($prefs?->include_weekend ? 'Yes' : 'No') . "

Generate " . ($type === 'monthly' ? '28 days (4 weeks)' : '7 days') . " of routine.";
    }

    public static function jsonSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'weekly_goals' => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => '3-5 measurable goals'],
                'study_tips' => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => '3-5 actionable tips'],
                'summary' => ['type' => 'string', 'description' => '2-3 sentence overview'],
                'days' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'day' => ['type' => 'string'],
                            'date' => ['type' => 'string'],
                            'blocks' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'start' => ['type' => 'string'],
                                        'end' => ['type' => 'string'],
                                        'type' => ['type' => 'string', 'enum' => ['study', 'revision', 'practice', 'homework', 'reading', 'break', 'exercise', 'meal', 'sleep', 'school', 'extracurricular', 'free']],
                                        'subject' => ['type' => ['string', 'null']],
                                        'title' => ['type' => 'string'],
                                        'priority' => ['type' => 'integer'],
                                    ],
                                    'required' => ['start', 'end', 'type', 'title'],
                                    'additionalProperties' => false,
                                ],
                            ],
                        ],
                        'required' => ['day', 'date', 'blocks'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
            'required' => ['weekly_goals', 'study_tips', 'summary', 'days'],
            'additionalProperties' => false,
        ];
    }

    protected function cost(string $model, int $in, int $out): float
    {
        $rates = [
            'gpt-4o' => ['in' => 2.50, 'out' => 10.00],
            'gpt-4o-mini' => ['in' => 0.15, 'out' => 0.60],
        ];
        $r = $rates[$model] ?? $rates['gpt-4o'];

        return round(($in / 1e6) * $r['in'] + ($out / 1e6) * $r['out'], 6);
    }
}
