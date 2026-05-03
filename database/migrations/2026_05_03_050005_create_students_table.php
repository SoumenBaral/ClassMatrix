<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('admission_no', 30)->unique();
            $table->string('roll_no', 20)->nullable();
            $table->date('dob')->nullable();
            $table->string('gender', 10)->nullable();
            $table->string('blood_group', 5)->nullable();
            $table->string('religion', 30)->nullable();
            $table->string('nationality', 30)->default('Indian');
            $table->string('mother_tongue', 30)->nullable();
            $table->foreignId('current_section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->date('admission_date')->nullable();
            $table->string('previous_school')->nullable();
            $table->string('photo')->nullable();
            $table->text('address_permanent')->nullable();
            $table->text('address_current')->nullable();
            $table->string('emergency_contact', 20)->nullable();
            $table->text('medical_notes')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->string('roll_no', 20)->nullable();
            $table->string('promotion_status', 20)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_enrollments');
        Schema::dropIfExists('students');
    }
};
