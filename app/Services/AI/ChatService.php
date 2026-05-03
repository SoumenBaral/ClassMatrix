<?php

namespace App\Services\AI;

use App\Models\AiChat;
use App\Models\AiLog;
use App\Models\AiMessage;
use App\Models\User;
use OpenAI\Laravel\Facades\OpenAI;

class ChatService
{
    public function sendMessage(User $user, AiChat $chat, string $message): AiMessage
    {
        // Save user message
        $chat->messages()->create([
            'role' => 'user',
            'content' => $message,
        ]);

        // Build conversation context
        $messages = $this->buildMessages($user, $chat);

        $model = config('services.openai.chat_model', 'gpt-4o-mini');
        $startMs = hrtime(true);

        try {
            $response = $this->callWithRetry($model, $messages);

            $content = $response->choices[0]->message->content;
            $usage = $response->usage;
            $cost = $this->cost($model, $usage->promptTokens, $usage->completionTokens);
            $latency = (int) ((hrtime(true) - $startMs) / 1e6);

            // Save assistant message
            $aiMessage = $chat->messages()->create([
                'role' => 'assistant',
                'content' => $content,
                'tokens' => $usage->completionTokens,
            ]);

            // Auto-title on first message
            if ($chat->title === 'New Chat' && $chat->messages()->count() <= 3) {
                $chat->update(['title' => $this->generateTitle($message)]);
            }

            AiLog::create([
                'user_id' => $user->id,
                'feature' => 'chat',
                'model' => $model,
                'prompt_tokens' => $usage->promptTokens,
                'completion_tokens' => $usage->completionTokens,
                'cost_usd' => $cost,
                'latency_ms' => min($latency, 65535),
                'status' => 'success',
            ]);

            return $aiMessage;
        } catch (\Throwable $e) {
            $latency = (int) ((hrtime(true) - $startMs) / 1e6);

            AiLog::create([
                'user_id' => $user->id,
                'feature' => 'chat',
                'model' => $model,
                'prompt_tokens' => 0,
                'completion_tokens' => 0,
                'cost_usd' => 0,
                'latency_ms' => min($latency, 65535),
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);

            // Save error as assistant message so user sees feedback in chat
            $errorContent = $this->friendlyError($e);

            $aiMessage = $chat->messages()->create([
                'role' => 'assistant',
                'content' => $errorContent,
            ]);

            return $aiMessage;
        }
    }

    /**
     * Call OpenAI with retry on rate limit (up to 2 retries with backoff).
     */
    protected function callWithRetry(string $model, array $messages, int $maxRetries = 2): mixed
    {
        $lastException = null;

        for ($attempt = 0; $attempt <= $maxRetries; $attempt++) {
            try {
                return OpenAI::chat()->create([
                    'model' => $model,
                    'max_tokens' => 2048,
                    'messages' => $messages,
                ]);
            } catch (\OpenAI\Exceptions\RateLimitException $e) {
                $lastException = $e;
                if ($attempt < $maxRetries) {
                    sleep(pow(2, $attempt + 1)); // 2s, 4s backoff
                }
            }
        }

        throw $lastException;
    }

    /**
     * Generate a user-friendly error message for the chat.
     */
    protected function friendlyError(\Throwable $e): string
    {
        $msg = $e->getMessage();

        if (str_contains($msg, 'rate limit') || str_contains($msg, 'Rate limit')) {
            return "I'm sorry, I'm getting too many requests right now. Please wait a moment and try again. This usually resolves in 30-60 seconds.\n\n*If this keeps happening, the school admin may need to check the AI service billing/quota.*";
        }

        if (str_contains($msg, 'API Key') || str_contains($msg, 'api_key') || str_contains($msg, 'Incorrect API')) {
            return "I'm sorry, I can't connect right now. The AI service needs to be configured. Please ask your school admin to check the API key settings.";
        }

        if (str_contains($msg, 'timeout') || str_contains($msg, 'Timeout')) {
            return "I'm sorry, my response took too long. Please try asking again with a shorter question.";
        }

        return "I'm sorry, I encountered an error and couldn't respond. Please try again in a moment.\n\n*Error: " . mb_substr($msg, 0, 100) . "*";
    }

    protected function buildMessages(User $user, AiChat $chat): array
    {
        $systemPrompt = $this->buildSystemPrompt($user, $chat);

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        // Load last 20 messages for context (keep token usage reasonable)
        $history = $chat->messages()
            ->orderByDesc('created_at')
            ->take(20)
            ->get()
            ->reverse()
            ->values();

        foreach ($history as $msg) {
            $messages[] = [
                'role' => $msg->role,
                'content' => $msg->content,
            ];
        }

        return $messages;
    }

    protected function buildSystemPrompt(User $user, AiChat $chat): string
    {
        $student = $user->student;
        $studentInfo = '';

        if ($student) {
            $section = $student->section?->load('classLevel');
            $className = $section?->classLevel?->name ?? 'Unknown';

            $marks = $student->marks()
                ->with('examSchedule.subject')
                ->latest()
                ->take(20)
                ->get();

            $perfSummary = '';
            if ($marks->isNotEmpty()) {
                $perf = $marks->groupBy(fn ($m) => $m->examSchedule?->subject?->name ?? 'Unknown')
                    ->map(fn ($group) => round($group->avg(fn ($m) => $m->examSchedule && $m->examSchedule->full_marks > 0
                        ? ($m->marks_obtained / $m->examSchedule->full_marks) * 100 : 0), 1));
                $perfSummary = "Recent scores: " . $perf->map(fn ($v, $k) => "{$k}: {$v}%")->join(', ');
            }

            $subjects = $section?->classLevel?->subjects?->pluck('name')->join(', ') ?? 'General';

            $studentInfo = "

STUDENT CONTEXT:
- Class: {$className}
- Subjects: {$subjects}
- {$perfSummary}";
        }

        $subjectFocus = $chat->subject_context
            ? "\nThe student wants to focus on: {$chat->subject_context}"
            : '';

        return "You are a friendly, patient, and encouraging personal AI teacher named ClassMatrix AI. You are helping a school student learn and solve academic problems.

YOUR PERSONALITY:
- Warm, supportive, and encouraging — like a favorite teacher
- Break down complex topics into simple, easy-to-understand explanations
- Use examples, analogies, and step-by-step solutions
- Ask follow-up questions to check understanding
- Celebrate when the student gets something right
- If the student is struggling, try a different approach instead of repeating
- Use markdown for formatting (bold, lists, code blocks for math/science)

YOUR CAPABILITIES:
- Explain any school subject concept
- Solve math, science, and other problems step-by-step
- Help with homework and assignments
- Quiz the student to test understanding
- Suggest study strategies and tips
- Help prepare for exams
- Answer questions in simple language appropriate for a school student
{$studentInfo}{$subjectFocus}

RULES:
- Keep responses concise but thorough
- Always be encouraging even when correcting mistakes
- If asked about non-academic topics, gently redirect to studies
- Never provide harmful, inappropriate, or off-topic content
- If unsure about something, say so honestly";
    }

    protected function generateTitle(string $firstMessage): string
    {
        $title = mb_substr($firstMessage, 0, 50);

        return mb_strlen($firstMessage) > 50 ? $title . '...' : $title;
    }

    protected function cost(string $model, int $in, int $out): float
    {
        $rates = [
            'gpt-4o' => ['in' => 2.50, 'out' => 10.00],
            'gpt-4o-mini' => ['in' => 0.15, 'out' => 0.60],
        ];
        $r = $rates[$model] ?? $rates['gpt-4o-mini'];

        return round(($in / 1e6) * $r['in'] + ($out / 1e6) * $r['out'], 6);
    }
}
