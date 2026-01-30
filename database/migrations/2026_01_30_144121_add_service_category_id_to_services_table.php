<?php

use App\Models\Service;
use App\Models\ServiceCategory;
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
        Schema::table('services', function (Blueprint $table) {
            $table->foreignId('service_category_id')
                ->nullable()
                ->after('id')
                ->constrained('service_categories')
                ->nullOnDelete();
        });

        // Migrate existing data from category enum to service_category_id
        $this->migrateExistingCategories();
    }

    /**
     * Migrate existing category enum values to service_category_id.
     */
    protected function migrateExistingCategories(): void
    {
        // Get all category codes
        $categoryMap = ServiceCategory::pluck('id', 'code')->toArray();

        // Update services with their corresponding category_id
        foreach ($categoryMap as $code => $categoryId) {
            Service::where('category', $code)->update(['service_category_id' => $categoryId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign(['service_category_id']);
            $table->dropColumn('service_category_id');
        });
    }
};
