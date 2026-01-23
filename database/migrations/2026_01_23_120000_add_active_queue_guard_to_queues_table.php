<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            $table->boolean('is_active_queue')
                ->virtualAs("status in ('waiting','called','serving','skipped')")
                ->after('status');

            $table->unique(['appointment_id', 'is_active_queue'], 'unique_active_queue_per_appointment');
        });
    }

    public function down(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            $table->dropUnique('unique_active_queue_per_appointment');
            $table->dropColumn('is_active_queue');
        });
    }
};
