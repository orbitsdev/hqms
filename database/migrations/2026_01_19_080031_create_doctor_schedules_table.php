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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('consultation_type_id')->constrained()->onDelete('cascade');

            // 'regular' = weekly schedule, 'exception' = specific date override
            $table->enum('schedule_type', ['regular', 'exception']);

            // For regular weekly schedule (0=Sun, 1=Mon, ... 6=Sat)
            $table->tinyInteger('day_of_week')->nullable();

            // For exception (leave, half-day, extra clinic day)
            $table->date('date')->nullable();
            $table->boolean('is_available')->default(true);

            // Time range (null = full day from system settings)
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            $table->string('reason')->nullable();
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
