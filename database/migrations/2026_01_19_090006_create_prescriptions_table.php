<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('medical_record_id')->constrained()->onDelete('cascade');
            $table->foreignId('prescribed_by')->constrained('users')->onDelete('cascade');

            // Medication Details
            $table->string('medication_name');
            $table->string('dosage')->nullable();
            $table->string('frequency')->nullable();
            $table->string('duration')->nullable();
            $table->text('instructions')->nullable();
            $table->integer('quantity')->nullable();

            // Hospital Pharmacy
            $table->foreignId('hospital_drug_id')->nullable()->constrained()->onDelete('set null');
            $table->boolean('is_hospital_drug')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
