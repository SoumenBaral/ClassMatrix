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

            // Auto-title: use AI to generate a concise title
            if ($chat->title === 'New Chat' && $chat->messages()->count() <= 3) {
                $chat->update(['title' => $this->generateTitle($message)]);
            }

            // Touch chat so it sorts to top
            $chat->touch();

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

            // Save error as assistant message so user sees feedback
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
                    'max_tokens' => 4096,
                    'temperature' => 0.7,
                    'messages' => $messages,
                ]);
            } catch (\OpenAI\Exceptions\RateLimitException $e) {
                $lastException = $e;
                if ($attempt < $maxRetries) {
                    sleep(pow(2, $attempt + 1));
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
            return "⚠️ **Rate Limit Reached**\n\nI'm getting too many requests right now. Please wait 30-60 seconds and try again.\n\n*If this keeps happening, the school admin may need to check the AI service billing/quota.*";
        }

        if (str_contains($msg, 'API Key') || str_contains($msg, 'api_key') || str_contains($msg, 'Incorrect API') || str_contains($msg, 'invalid_api_key')) {
            return "⚠️ **Connection Error**\n\nI can't connect to the AI service right now. The API key may need to be updated.\n\n*Please ask your school admin to check the OpenAI API key in the system settings.*";
        }

        if (str_contains($msg, 'insufficient_quota') || str_contains($msg, 'billing')) {
            return "⚠️ **Quota Exceeded**\n\nThe AI service has run out of credits. Your school admin needs to add billing to the OpenAI account.\n\n*Go to platform.openai.com/account/billing to add credits.*";
        }

        if (str_contains($msg, 'timeout') || str_contains($msg, 'Timeout') || str_contains($msg, 'timed out')) {
            return "⚠️ **Timeout**\n\nMy response took too long. Try asking a shorter or simpler question.\n\n*Tip: Break complex questions into smaller parts.*";
        }

        if (str_contains($msg, 'model_not_found') || str_contains($msg, 'does not exist')) {
            return "⚠️ **Model Error**\n\nThe AI model configured is not available. Please ask your admin to check the OPENAI_CHAT_MODEL setting.";
        }

        return "⚠️ **Something went wrong**\n\nI couldn't process your question. Please try again.\n\n*Error: " . mb_substr($msg, 0, 150) . "*";
    }

    /**
     * Check if a message is an error placeholder (not a real AI response).
     */
    protected function isErrorPlaceholder(string $content): bool
    {
        // Only match our exact error format — never filter legitimate AI responses
        return str_starts_with($content, '⚠️ **')
            || str_starts_with($content, "⚠️ **Rate Limit")
            || str_starts_with($content, "⚠️ **Connection Error")
            || str_starts_with($content, "⚠️ **Quota Exceeded")
            || str_starts_with($content, "⚠️ **Timeout")
            || str_starts_with($content, "⚠️ **Model Error")
            || str_starts_with($content, "⚠️ **Something went wrong")
            || str_starts_with($content, "⚠️ **Request Failed");
    }

    protected function buildMessages(User $user, AiChat $chat): array
    {
        $systemPrompt = $this->buildSystemPrompt($user, $chat);

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        // Load last 30 messages for context
        $history = $chat->messages()
            ->orderByDesc('created_at')
            ->take(30)
            ->get()
            ->reverse()
            ->values();

        foreach ($history as $msg) {
            // Skip error placeholder messages only — never skip real AI responses
            if ($msg->role === 'assistant' && $this->isErrorPlaceholder($msg->content)) {
                continue;
            }

            $messages[] = [
                'role' => $msg->role,
                'content' => $msg->content,
            ];
        }

        // Fix alternation: if filtering created consecutive same-role messages,
        // keep only the LAST user message (don't merge — merging confuses the AI)
        $cleaned = [$messages[0]]; // system message
        for ($i = 1; $i < count($messages); $i++) {
            $last = end($cleaned);
            if ($messages[$i]['role'] === 'user' && $last['role'] === 'user') {
                // Replace previous user message with this newer one
                // (the newer question is what the student actually wants answered)
                $cleaned[count($cleaned) - 1] = $messages[$i];
            } else {
                $cleaned[] = $messages[$i];
            }
        }

        return $cleaned;
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

                $strong = $perf->filter(fn ($v) => $v >= 75)->keys()->join(', ');
                $weak = $perf->filter(fn ($v) => $v < 60)->keys()->join(', ');

                $perfSummary = "Recent scores: " . $perf->map(fn ($v, $k) => "{$k}: {$v}%")->join(', ');
                if ($strong) $perfSummary .= "\nStrong subjects: {$strong}";
                if ($weak) $perfSummary .= "\nNeeds improvement: {$weak} (give extra attention to these)";
            }

            $subjects = $section?->classLevel?->subjects?->pluck('name')->join(', ') ?? 'General';

            $studentInfo = "

STUDENT CONTEXT (use this to personalize your responses):
- Name: {$user->name}
- Class: {$className}
- Subjects: {$subjects}
- {$perfSummary}";
        }

        $subjectFocus = $chat->subject_context
            ? "\nCURRENT FOCUS: The student wants to focus on **{$chat->subject_context}**. Prioritize this subject in your responses."
            : '';

        return "You are **ClassMatrix AI** — a brilliant, friendly, and patient personal teacher for school students. You are their favorite teacher who makes learning fun and easy.

CRITICAL RULE — READ FIRST:
Each message from the student is a NEW question. Answer ONLY their latest message. If they asked about math before but now ask about history, answer about history — do NOT mention math. Treat every message as its own independent question unless the student explicitly says \"continue\" or \"tell me more\" or refers back to the previous topic.

PERSONALITY:
- Warm, encouraging, and enthusiastic about learning
- You celebrate when students understand something (\"Great job! 🎉\", \"Exactly right! ⭐\")
- If a student is struggling, you never repeat the same explanation — try a completely different angle, analogy, or visual approach
- You speak at the student's level — avoid jargon, use relatable examples from daily life
- You have a slight sense of humor to keep things engaging

RESPONSE FORMAT (follow strictly):
- Use **bold** for key terms and important concepts
- Use bullet points and numbered lists for steps
- Use `code blocks` for formulas, equations, and code
- Use headings (## or ###) to organize longer explanations
- For math: write equations clearly, show every step, explain WHY each step happens
- For science: use real-world analogies (\"Think of electrons like planets orbiting the sun...\")
- Keep paragraphs short (2-3 sentences max)
- End complex explanations with a quick summary or \"In simple terms: ...\"

TEACHING APPROACH:
1. **Start simple** — give the core concept in 1-2 sentences
2. **Build up** — add detail with examples and analogies
3. **Show steps** — for problems, number every step and explain the reasoning
4. **Check understanding** — occasionally ask \"Does this make sense?\" or give a quick practice question
5. **Connect ideas** — relate new concepts to things they already know

WHAT YOU CAN DO:
- Explain ANY school subject concept clearly
- Solve math, physics, chemistry problems step-by-step
- Help write essays, summaries, and creative pieces
- Quiz and test understanding with practice questions
- Create study plans and exam strategies
- Explain difficult topics with simple analogies
- Help debug code and explain programming concepts
- Translate explanations to simpler language if asked
{$studentInfo}{$subjectFocus}

RULES:
- ALWAYS provide complete, helpful answers — never say \"I can't help with that\" for academic topics
- If a question is ambiguous, make a reasonable assumption and answer (don't ask for clarification unless truly necessary)
- For math/science: ALWAYS show your work step-by-step
- Keep responses focused and concise but thorough — typically 150-400 words
- Be encouraging even when correcting mistakes
- For non-academic topics, gently redirect: \"That's interesting! But let's focus on your studies — what subject can I help with?\"";
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
            'gpt-4.1-mini' => ['in' => 0.40, 'out' => 1.60],
            'gpt-4.1-nano' => ['in' => 0.10, 'out' => 0.40],
        ];
        $r = $rates[$model] ?? $rates['gpt-4o-mini'];

        return round(($in / 1e6) * $r['in'] + ($out / 1e6) * $r['out'], 6);
    }
}
