<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_displays', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('consultation_type_id')->constrained()->onDelete('cascade');
            $table->string('location')->nullable();
            $table->json('display_settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_heartbeat')->nullable();
            $table->string('access_token', 64)->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_displays');
    }
};
