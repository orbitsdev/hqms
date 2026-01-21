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
        Schema::create('consultation_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // 'ob', 'pedia', 'general'
            $table->string('name'); // 'Obstetrics', 'Pediatrics', 'General Medicine'
            $table->string('short_name', 5); // 'O', 'P', 'G' (for queue display)
            $table->text('description')->nullable();

            // Queue Settings
            $table->integer('avg_duration')->default(30); // Average minutes per patient
            $table->integer('max_daily_patients')->default(50); // Max appointments per day

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultation_types');
    }
};
