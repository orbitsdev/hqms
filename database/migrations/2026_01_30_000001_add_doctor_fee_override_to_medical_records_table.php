<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            // Doctor can override the total fee (e.g., for empathy/charity cases)
            // This is the base amount before cashier adds items or applies discounts
            $table->decimal('doctor_fee_override', 10, 2)->nullable()->after('suggested_discount_reason');
        });
    }

    public function down(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropColumn('doctor_fee_override');
        });
    }
};
