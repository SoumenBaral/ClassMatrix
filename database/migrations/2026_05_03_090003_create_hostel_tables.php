<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hostels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 10)->default('boys');
            $table->foreignId('warden_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedSmallInteger('total_rooms')->default(0);
            $table->timestamps();
        });

        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hostel_id')->constrained()->cascadeOnDelete();
            $table->string('room_no', 10);
            $table->unsignedSmallInteger('capacity')->default(2);
            $table->string('type', 10)->default('non-ac');
            $table->decimal('rent', 8, 2)->default(0);
            $table->timestamps();

            $table->unique(['hostel_id', 'room_no']);
        });

        Schema::create('hostel_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->date('allocated_at');
            $table->date('vacated_at')->nullable();
            $table->decimal('fee', 8, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostel_allocations');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('hostels');
    }
};
