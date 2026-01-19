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

            // Operating Hours
            $table->time('start_time'); // 08:00
            $table->time('end_time'); // 17:00

            // Queue Settings
            $table->integer('avg_duration')->default(30); // Average minutes per patient
            $table->integer('max_daily_patients')->default(50); // Max appointments per day

            // Display
            $table->string('color_code', 7)->nullable(); // #FF5733 for UI
            $table->integer('display_order')->default(0); // Sort order in lists

            // Status
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
