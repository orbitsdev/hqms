<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Consultation / Professional Fee',
                'code' => 'consultation',
                'description' => 'Medical consultation and professional fees',
                'icon' => 'user-circle',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Ultrasound',
                'code' => 'ultrasound',
                'description' => 'Ultrasound imaging services',
                'icon' => 'photo',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Procedure',
                'code' => 'procedure',
                'description' => 'Medical procedures and treatments',
                'icon' => 'beaker',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Laboratory',
                'code' => 'laboratory',
                'description' => 'Laboratory tests and diagnostics',
                'icon' => 'clipboard-document-list',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Other',
                'code' => 'other',
                'description' => 'Other hospital services and supplies',
                'icon' => 'squares-plus',
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($categories as $category) {
            ServiceCategory::updateOrCreate(
                ['code' => $category['code']],
                $category
            );
        }
    }
}
