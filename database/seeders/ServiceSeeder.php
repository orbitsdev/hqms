<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get category IDs
        $categories = ServiceCategory::pluck('id', 'code')->toArray();

        $order = 0;

        // Professional/Consultation Fees (must be first in display order)
        $professionalFees = [
            'Professional Fee - OB' => 500,
            'Professional Fee - Pediatrics' => 500,
            'Professional Fee - General' => 400,
        ];

        foreach ($professionalFees as $name => $price) {
            Service::create([
                'service_name' => $name,
                'category' => 'consultation',
                'service_category_id' => $categories['consultation'] ?? null,
                'description' => 'Doctor consultation fee',
                'base_price' => $price,
                'is_active' => true,
                'display_order' => ++$order,
            ]);
        }

        // General Ultrasound Services
        $ultrasoundServices = [
            'Whole Abdomen' => 1500,
            'Adrenal Gland' => 1200,
            'Breast' => 1200,
            'Chest' => 1200,
            'Cranial' => 1200,
            'Extremities' => 1200,
            'Gallbladder/Liver' => 1200,
            'Hepatobiliary' => 1200,
            'Inguinal' => 1200,
            'Kidneys' => 1200,
            'Kidneys, Ureter, Bladder (KUB)' => 1200,
            'KUB/Liver' => 1200,
            'KUB/Pelvis' => 1200,
            'KUB/Prostate' => 1200,
            'Liver' => 1200,
            'Neck' => 1200,
            'Pelvis' => 1200,
            'Prostate' => 1200,
            'Scrotum' => 1200,
            'Thyroid' => 1200,
            'Upper Abdomen' => 1200,
            'Lower Abdomen' => 1200,
        ];

        foreach ($ultrasoundServices as $name => $price) {
            Service::create([
                'service_name' => $name,
                'category' => 'ultrasound',
                'service_category_id' => $categories['ultrasound'] ?? null,
                'description' => $name.' ultrasound examination',
                'base_price' => $price,
                'is_active' => true,
                'display_order' => ++$order,
            ]);
        }

        // OB Consultation Services
        $obServices = [
            'Pelvic' => 1000,
            'TVS - OB' => 1500,
            'TVS - GYNE' => 1500,
            'TRS' => 1500,
            'BPS (Biophysical Profile Score)' => 1200,
            'CAS (Congenital Anomaly Scan)' => 3500,
            'Gyne Doppler' => 1800,
            '3D - 4D' => 3500,
            'SISH (Saline Infusion Sonohysterogram)' => 3000,
            'HSSG (Hysterosalpingosonogram)' => 3000,
            'Twins' => 2000,
        ];

        foreach ($obServices as $name => $price) {
            Service::create([
                'service_name' => $name,
                'category' => 'consultation',
                'service_category_id' => $categories['consultation'] ?? null,
                'description' => 'OB-Gyne service: '.$name,
                'base_price' => $price,
                'is_active' => true,
                'display_order' => ++$order,
            ]);
        }

        // Laboratory Services
        $laboratoryServices = [
            'Complete Blood Count (CBC)' => 250,
            'Urinalysis' => 150,
            'Fasting Blood Sugar (FBS)' => 200,
            'Blood Typing' => 150,
            'Pregnancy Test' => 200,
        ];

        foreach ($laboratoryServices as $name => $price) {
            Service::create([
                'service_name' => $name,
                'category' => 'laboratory',
                'service_category_id' => $categories['laboratory'] ?? null,
                'description' => 'Laboratory test: '.$name,
                'base_price' => $price,
                'is_active' => true,
                'display_order' => ++$order,
            ]);
        }

        // Procedures
        $procedures = [
            'Circumcision' => 3500,
            'Ear Piercing' => 500,
            'Wound Dressing' => 300,
            'Suturing' => 1000,
            'IV Insertion' => 500,
        ];

        foreach ($procedures as $name => $price) {
            Service::create([
                'service_name' => $name,
                'category' => 'procedure',
                'service_category_id' => $categories['procedure'] ?? null,
                'description' => 'Medical procedure: '.$name,
                'base_price' => $price,
                'is_active' => true,
                'display_order' => ++$order,
            ]);
        }
    }
}
