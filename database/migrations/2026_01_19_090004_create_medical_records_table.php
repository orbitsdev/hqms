<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('consultation_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('queue_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('doctor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('nurse_id')->nullable()->constrained('users')->onDelete('set null');

            // === PATIENT PERSONAL INFORMATION (Self-Contained) ===
            $table->string('patient_first_name');
            $table->string('patient_middle_name')->nullable();
            $table->string('patient_last_name');
            $table->date('patient_date_of_birth')->nullable();
            $table->enum('patient_gender', ['male', 'female'])->nullable();
            $table->enum('patient_marital_status', ['child', 'single', 'married', 'widow'])->nullable();

            // Patient Address
            $table->string('patient_province')->nullable();
            $table->string('patient_municipality')->nullable();
            $table->string('patient_barangay')->nullable();
            $table->text('patient_street')->nullable();

            // Patient Contact
            $table->string('patient_contact_number', 20)->nullable();
            $table->string('patient_occupation')->nullable();

            // Patient Medical Background
            $table->enum('patient_blood_type', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])->nullable();
            $table->text('patient_allergies')->nullable();
            $table->text('patient_chronic_conditions')->nullable();

            // Emergency Contact
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();

            // Visit Information
            $table->date('visit_date');
            $table->enum('visit_type', ['new', 'old', 'revisit']);
            $table->enum('service_type', ['checkup', 'admission']);

            // Chief Complaints
            $table->text('chief_complaints_initial')->nullable();
            $table->text('chief_complaints_updated')->nullable();

            // === VITAL SIGNS (Nurse Input) ===
            $table->decimal('temperature', 4, 1)->nullable();
            $table->string('blood_pressure', 20)->nullable();
            $table->integer('cardiac_rate')->nullable();
            $table->integer('respiratory_rate')->nullable();

            // PEDIA / GENERAL Specific
            $table->decimal('weight', 5, 2)->nullable();
            $table->decimal('height', 5, 2)->nullable();
            $table->decimal('head_circumference', 5, 2)->nullable();
            $table->decimal('chest_circumference', 5, 2)->nullable();

            // OB Specific
            $table->integer('fetal_heart_tone')->nullable();
            $table->decimal('fundal_height', 5, 2)->nullable();
            $table->date('last_menstrual_period')->nullable();

            // Vital Signs Timing
            $table->timestamp('vital_signs_recorded_at')->nullable();

            // === DIAGNOSIS (Doctor Input) ===
            $table->text('pertinent_hpi_pe')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('plan')->nullable();
            $table->text('procedures_done')->nullable();
            $table->text('prescription_notes')->nullable();

            // Examination Timing
            $table->timestamp('examined_at')->nullable();
            $table->enum('examination_time', ['am', 'pm'])->nullable();

            // Status
            $table->enum('status', ['in_progress', 'for_billing', 'for_admission', 'completed'])->default('in_progress');

            $table->text('notes')->nullable();

            $table->timestamps();

            // Index for patient lookup
            $table->index(['patient_first_name', 'patient_last_name', 'patient_date_of_birth'], 'patient_lookup_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
