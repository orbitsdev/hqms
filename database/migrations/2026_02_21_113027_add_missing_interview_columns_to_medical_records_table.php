<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->string('patient_email')->nullable()->after('patient_contact_number');
            $table->string('patient_zip_code', 10)->nullable()->after('patient_street');
            $table->string('emergency_contact_number', 20)->nullable()->after('emergency_contact_phone');
            $table->string('emergency_contact_relationship')->nullable()->after('emergency_contact_number');
            $table->text('patient_current_medications')->nullable()->after('patient_chronic_conditions');
            $table->text('patient_past_medical_history')->nullable()->after('patient_current_medications');
            $table->text('patient_family_medical_history')->nullable()->after('patient_past_medical_history');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropColumn([
                'patient_email',
                'patient_zip_code',
                'emergency_contact_number',
                'emergency_contact_relationship',
                'patient_current_medications',
                'patient_past_medical_history',
                'patient_family_medical_history',
            ]);
        });
    }
};
