<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Convert appointment and queue status columns from enum to string
     * for flexibility, and rename 'pending' status to 'confirmed'.
     */
    public function up(): void
    {
        // 1. Convert appointment status from enum to string
        Schema::table('appointments', function (Blueprint $table): void {
            $table->string('status')->default('confirmed')->change();
        });

        // 2. Rename existing 'pending' appointments to 'confirmed'
        DB::table('appointments')
            ->where('status', 'pending')
            ->update(['status' => 'confirmed']);

        // 3. Convert queue status from enum to string (adds no_show support)
        Schema::table('queues', function (Blueprint $table): void {
            $table->string('status')->default('waiting')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert 'confirmed' back to 'pending'
        DB::table('appointments')
            ->where('status', 'confirmed')
            ->update(['status' => 'pending']);

        // Revert appointment status back to enum
        Schema::table('appointments', function (Blueprint $table): void {
            $table->enum('status', [
                'pending', 'approved', 'checked_in', 'in_progress',
                'completed', 'cancelled', 'no_show',
            ])->default('pending')->change();
        });

        // Revert queue status back to enum
        Schema::table('queues', function (Blueprint $table): void {
            $table->enum('status', [
                'waiting', 'called', 'serving', 'completed', 'skipped', 'cancelled',
            ])->default('waiting')->change();
        });
    }
};
