<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_transactions', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('medical_record_id')->nullable()->constrained()->onDelete('set null');

            // Transaction Details
            $table->string('transaction_number', 50)->unique();
            $table->date('transaction_date');

            // Emergency/After Hours Charges
            $table->boolean('is_emergency')->default(false);
            $table->boolean('is_holiday')->default(false);
            $table->boolean('is_sunday')->default(false);
            $table->boolean('is_after_5pm')->default(false);
            $table->decimal('emergency_fee', 10, 2)->default(0);

            // Amounts
            $table->decimal('subtotal', 10, 2);

            // Discount
            $table->enum('discount_type', [
                'none',
                'family',
                'senior',
                'pwd',
                'employee',
                'other'
            ])->default('none');
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->text('discount_reason')->nullable();

            $table->decimal('total_amount', 10, 2);

            // Payment
            $table->enum('payment_status', ['pending', 'partial', 'paid', 'cancelled'])->default('pending');
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('balance', 10, 2);
            $table->enum('payment_method', ['cash', 'gcash', 'card', 'bank_transfer', 'philhealth'])->nullable();

            // Timing
            $table->timestamp('received_in_billing_at')->nullable();
            $table->timestamp('ended_in_billing_at')->nullable();

            // Staff
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('discount_approved_by')->nullable()->constrained('users')->onDelete('set null');

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_transactions');
    }
};
