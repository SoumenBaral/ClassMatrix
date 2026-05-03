<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routine_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->unique();
            $table->time('wake_up_time')->default('06:00');
            $table->time('sleep_time')->default('22:00');
            $table->unsignedSmallInteger('focus_minutes')->default(45);
            $table->string('peak_focus', 20)->default('morning');
            $table->json('blocked_times')->nullable();
            $table->json('learning_goals')->nullable();
            $table->json('subject_priorities')->nullable();
            $table->boolean('include_weekend')->default(true);
            $table->timestamps();
        });

        Schema::create('routines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained();
            $table->date('week_start_date');
            $table->string('type', 10)->default('weekly'); // weekly or monthly
            $table->string('status', 20)->default('generating');
            $table->json('weekly_goals')->nullable();
            $table->json('study_tips')->nullable();
            $table->text('ai_summary')->nullable();
            $table->json('blocks')->nullable();
            $table->string('model_used')->nullable();
            $table->unsignedInteger('total_tokens')->nullable();
            $table->decimal('cost_usd', 8, 6)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'week_start_date']);
        });

        Schema::create('ai_chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title')->default('New Chat');
            $table->string('subject_context')->nullable(); // optional subject focus
            $table->timestamps();

            $table->index(['user_id', 'updated_at']);
        });

        Schema::create('ai_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained('ai_chats')->cascadeOnDelete();
            $table->string('role', 10); // user, assistant
            $table->text('content');
            $table->unsignedInteger('tokens')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->string('feature', 30);
            $table->string('model', 50);
            $table->unsignedInteger('prompt_tokens')->default(0);
            $table->unsignedInteger('completion_tokens')->default(0);
            $table->decimal('cost_usd', 8, 6)->default(0);
            $table->unsignedSmallInteger('latency_ms')->default(0);
            $table->string('status', 20)->default('success');
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['feature', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_logs');
        Schema::dropIfExists('ai_messages');
        Schema::dropIfExists('ai_chats');
        Schema::dropIfExists('routines');
        Schema::dropIfExists('routine_preferences');
    }
};
