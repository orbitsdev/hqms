<?php

namespace Database\Seeders;

use App\Models\HospitalDrug;
use Illuminate\Database\Seeder;

class HospitalDrugSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $drugs = [
            // Common Pain/Fever
            ['drug_name' => 'Paracetamol 500mg', 'generic_name' => 'Paracetamol', 'unit_price' => 5.00],
            ['drug_name' => 'Paracetamol 250mg (Pedia)', 'generic_name' => 'Paracetamol', 'unit_price' => 4.00],
            ['drug_name' => 'Ibuprofen 400mg', 'generic_name' => 'Ibuprofen', 'unit_price' => 8.00],
            ['drug_name' => 'Mefenamic Acid 500mg', 'generic_name' => 'Mefenamic Acid', 'unit_price' => 7.00],

            // Antibiotics
            ['drug_name' => 'Amoxicillin 500mg', 'generic_name' => 'Amoxicillin', 'unit_price' => 10.00],
            ['drug_name' => 'Amoxicillin 250mg (Pedia)', 'generic_name' => 'Amoxicillin', 'unit_price' => 8.00],
            ['drug_name' => 'Co-Amoxiclav 625mg', 'generic_name' => 'Amoxicillin + Clavulanic Acid', 'unit_price' => 35.00],
            ['drug_name' => 'Cefalexin 500mg', 'generic_name' => 'Cefalexin', 'unit_price' => 15.00],
            ['drug_name' => 'Azithromycin 500mg', 'generic_name' => 'Azithromycin', 'unit_price' => 45.00],
            ['drug_name' => 'Metronidazole 500mg', 'generic_name' => 'Metronidazole', 'unit_price' => 8.00],

            // OB-Related
            ['drug_name' => 'Ferrous Sulfate 325mg', 'generic_name' => 'Ferrous Sulfate', 'unit_price' => 3.00],
            ['drug_name' => 'Folic Acid 5mg', 'generic_name' => 'Folic Acid', 'unit_price' => 2.00],
            ['drug_name' => 'Calcium Carbonate 500mg', 'generic_name' => 'Calcium Carbonate', 'unit_price' => 5.00],
            ['drug_name' => 'Multivitamins Prenatal', 'generic_name' => 'Prenatal Vitamins', 'unit_price' => 15.00],

            // Cough & Cold
            ['drug_name' => 'Salbutamol 2mg', 'generic_name' => 'Salbutamol', 'unit_price' => 5.00],
            ['drug_name' => 'Carbocisteine 500mg', 'generic_name' => 'Carbocisteine', 'unit_price' => 8.00],
            ['drug_name' => 'Loratadine 10mg', 'generic_name' => 'Loratadine', 'unit_price' => 6.00],
            ['drug_name' => 'Cetirizine 10mg', 'generic_name' => 'Cetirizine', 'unit_price' => 5.00],

            // GI Related
            ['drug_name' => 'Omeprazole 20mg', 'generic_name' => 'Omeprazole', 'unit_price' => 10.00],
            ['drug_name' => 'Ranitidine 150mg', 'generic_name' => 'Ranitidine', 'unit_price' => 6.00],
            ['drug_name' => 'Loperamide 2mg', 'generic_name' => 'Loperamide', 'unit_price' => 5.00],
            ['drug_name' => 'Oral Rehydration Salts', 'generic_name' => 'ORS', 'unit_price' => 15.00],

            // Hypertension/Cardiac
            ['drug_name' => 'Amlodipine 5mg', 'generic_name' => 'Amlodipine', 'unit_price' => 8.00],
            ['drug_name' => 'Losartan 50mg', 'generic_name' => 'Losartan', 'unit_price' => 10.00],
            ['drug_name' => 'Metoprolol 50mg', 'generic_name' => 'Metoprolol', 'unit_price' => 8.00],

            // Diabetes
            ['drug_name' => 'Metformin 500mg', 'generic_name' => 'Metformin', 'unit_price' => 5.00],
            ['drug_name' => 'Glibenclamide 5mg', 'generic_name' => 'Glibenclamide', 'unit_price' => 4.00],
        ];

        foreach ($drugs as $drug) {
            HospitalDrug::create([
                'drug_name' => $drug['drug_name'],
                'generic_name' => $drug['generic_name'],
                'unit_price' => $drug['unit_price'],
                'is_active' => true,
            ]);
        }
    }
}
