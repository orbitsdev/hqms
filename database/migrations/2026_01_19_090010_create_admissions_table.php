<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admissions', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('medical_record_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('admitted_by')->constrained('users')->onDelete('cascade');

            // Admission Details
            $table->string('admission_number', 50)->unique();
            $table->dateTime('admission_date');
            $table->dateTime('discharge_date')->nullable();

            // Room/Bed
            $table->string('room_number', 50)->nullable();
            $table->string('bed_number', 50)->nullable();

            // Medical
            $table->text('reason_for_admission');
            $table->text('discharge_summary')->nullable();

            // Status
            $table->enum('status', ['active', 'discharged'])->default('active');

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admissions');
    }
};
