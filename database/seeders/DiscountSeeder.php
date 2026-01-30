<?php

namespace Database\Seeders;

use App\Models\Discount;
use Illuminate\Database\Seeder;

class DiscountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $discounts = [
            [
                'name' => 'Senior Citizen',
                'code' => 'senior',
                'percentage' => 20.00,
                'description' => 'Philippine law mandates 20% discount for senior citizens (60 years and above)',
                'sort_order' => 1,
            ],
            [
                'name' => 'PWD',
                'code' => 'pwd',
                'percentage' => 20.00,
                'description' => 'Philippine law mandates 20% discount for persons with disability',
                'sort_order' => 2,
            ],
            [
                'name' => 'Employee',
                'code' => 'employee',
                'percentage' => 15.00,
                'description' => 'Discount for hospital employees and their dependents',
                'sort_order' => 3,
            ],
            [
                'name' => 'Family/Relative',
                'code' => 'family',
                'percentage' => 10.00,
                'description' => 'Courtesy discount for family and relatives of staff',
                'sort_order' => 4,
            ],
            [
                'name' => 'Other',
                'code' => 'other',
                'percentage' => 0.00,
                'description' => 'Custom discount - percentage set by cashier',
                'sort_order' => 99,
            ],
        ];

        foreach ($discounts as $discount) {
            Discount::updateOrCreate(
                ['code' => $discount['code']],
                $discount
            );
        }
    }
}
