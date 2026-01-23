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

            // Relations (user_id = account owner for notifications)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('consultation_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->nullable()->constrained('users')->onDelete('set null');

            // Patient Information (actual patient - may differ from account owner)
            $table->string('patient_first_name');
            $table->string('patient_middle_name')->nullable();
            $table->string('patient_last_name');
            $table->date('patient_date_of_birth');
            $table->enum('patient_gender', ['male', 'female']);
            $table->string('patient_phone', 20)->nullable();
            $table->string('patient_province')->nullable();
            $table->string('patient_municipality')->nullable();
            $table->string('patient_barangay')->nullable();
            $table->text('patient_street')->nullable();
            $table->enum('relationship_to_account', ['self', 'child', 'spouse', 'parent', 'sibling', 'other'])->default('self');

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
                'no_show',
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
