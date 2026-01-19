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
        Schema::create('doctor_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Doctor
            $table->foreignId('consultation_type_id')->constrained()->onDelete('cascade');

            // Schedule Type
            $table->enum('schedule_type', ['regular', 'specific_date', 'leave']);

            // For Regular Schedule (weekly)
            $table->tinyInteger('day_of_week')->nullable(); // 0=Sun, 1=Mon, ... 6=Sat

            // For Specific Date or Leave
            $table->date('date')->nullable();

            // Time Slots
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            // Capacity
            $table->integer('max_patients')->default(20);

            // Status
            $table->boolean('is_available')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_schedules');
    }
};
