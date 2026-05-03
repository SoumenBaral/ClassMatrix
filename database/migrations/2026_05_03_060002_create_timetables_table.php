<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 1=Mon, 7=Sun
            $table->foreignId('period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('room', 20)->nullable();
            $table->timestamps();

            $table->unique(['section_id', 'day_of_week', 'period_id']);
            $table->index(['teacher_id', 'day_of_week', 'period_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetables');
    }
};
