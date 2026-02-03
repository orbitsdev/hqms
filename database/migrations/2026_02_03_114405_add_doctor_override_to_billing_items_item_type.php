<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL requires raw SQL to modify ENUM columns
        DB::statement("ALTER TABLE billing_items MODIFY COLUMN item_type ENUM('professional_fee', 'service', 'drug', 'procedure', 'other', 'doctor_override') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE billing_items MODIFY COLUMN item_type ENUM('professional_fee', 'service', 'drug', 'procedure', 'other') NOT NULL");
    }
};
