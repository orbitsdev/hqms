<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('billing_transaction_id')->constrained()->onDelete('cascade');

            // Item Details
            $table->enum('item_type', [
                'professional_fee',
                'service',
                'drug',
                'procedure',
                'other'
            ]);
            $table->string('item_description');

            // Service Reference
            $table->foreignId('service_id')->nullable()->constrained()->onDelete('set null');

            // Pricing
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);

            // For hospital drugs
            $table->foreignId('hospital_drug_id')->nullable()->constrained()->onDelete('set null');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_items');
    }
};
