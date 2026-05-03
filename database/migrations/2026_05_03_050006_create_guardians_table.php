<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardians', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('relation', 30);
            $table->string('phone', 20);
            $table->string('email')->nullable();
            $table->string('occupation')->nullable();
            $table->string('photo')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        Schema::create('student_guardian', function (Blueprint $table) {
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guardian_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->boolean('can_pickup')->default(true);

            $table->primary(['student_id', 'guardian_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_guardian');
        Schema::dropIfExists('guardians');
    }
};
