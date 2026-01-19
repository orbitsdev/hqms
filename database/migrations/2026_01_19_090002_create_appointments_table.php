<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('consultation_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->nullable()->constrained('users')->onDelete('set null');

            // Appointment Details
            $table->date('appointment_date');
            $table->time('appointment_time')->nullable();

            // Initial Symptoms
            $table->text('chief_complaints')->nullable();

            // Status Flow
            $table->enum('status', [
                'pending',
                'approved',
                'checked_in',
                'in_progress',
                'completed',
                'cancelled',
                'no_show'
            ])->default('pending');

            // Tracking
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('checked_in_at')->nullable();

            // Decline Handling
            $table->text('decline_reason')->nullable();
            $table->date('suggested_date')->nullable();

            // Source
            $table->enum('source', ['online', 'walk-in'])->default('online');

            // Notes
            $table->text('notes')->nullable();
            $table->text('cancellation_reason')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
