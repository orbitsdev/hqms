<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queues', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('appointment_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('consultation_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->nullable()->constrained('users')->onDelete('set null');

            // Queue Information
            $table->integer('queue_number');
            $table->date('queue_date');
            $table->unsignedTinyInteger('session_number')->default(1);

            // Timing
            $table->time('estimated_time')->nullable();

            // Priority
            $table->enum('priority', ['normal', 'urgent', 'emergency'])->default('normal');

            // Status
            $table->enum('status', [
                'waiting',
                'called',
                'serving',
                'completed',
                'skipped',
                'cancelled',
            ])->default('waiting');

            // Tracking
            $table->timestamp('called_at')->nullable();
            $table->timestamp('serving_started_at')->nullable();
            $table->timestamp('serving_ended_at')->nullable();
            $table->foreignId('served_by')->nullable()->constrained('users')->onDelete('set null');

            // Source
            $table->enum('source', ['online', 'walk-in'])->default('online');

            $table->text('notes')->nullable();

            $table->timestamps();

            // Unique constraint per type per day per session
            $table->unique(['queue_number', 'queue_date', 'consultation_type_id', 'session_number'], 'unique_queue_per_type_date_session');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queues');
    }
};
