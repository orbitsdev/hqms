<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            // Patient age at time of visit (frozen, for medical records)
            // Important for pediatrics: months matter for infants
            $table->unsignedTinyInteger('patient_age_years')->nullable()->after('patient_date_of_birth');
            $table->unsignedTinyInteger('patient_age_months')->nullable()->after('patient_age_years');
        });
    }

    public function down(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropColumn(['patient_age_years', 'patient_age_months']);
        });
    }
};
