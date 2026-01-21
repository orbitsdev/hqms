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
        Schema::table('appointments', function (Blueprint $table) {
            // Patient type: 'self' or 'dependent'
            $table->enum('patient_type', ['self', 'dependent'])->default('self')->after('chief_complaints');

            /// Dependent/Patient info (nullable for 'self'  type)
            $table->string('patient_first_name')->nullable()->after('patient_type');
            $table->string('patient_middle_name')->nullable()->after('patient_first_name');
            $table->string('patient_last_name')->nullable()->after('patient_middle_name');
            $table->date('patient_date_of_birth')->nullable()->after('patient_last_name');
            $table->enum('patient_gender', ['male', 'female'])->nullable()->after('patient_date_of_birth');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn([
                'patient_type',
                'patient_first_name',
                'patient_middle_name',
                'patient_last_name',
                'patient_date_of_birth',
                'patient_gender',
            ]);
        });
    }
};
