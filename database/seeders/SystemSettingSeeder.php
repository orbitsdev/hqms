<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Queue Settings
            [
                'setting_key' => 'queue_reset_time',
                'setting_value' => '00:00',
                'setting_type' => 'time',
                'category' => 'queue',
                'description' => 'Time when daily queues reset',
            ],
            [
                'setting_key' => 'queue_nearby_threshold',
                'setting_value' => '3',
                'setting_type' => 'integer',
                'category' => 'queue',
                'description' => 'Notify when this many patients away',
            ],

            // Billing Settings
            [
                'setting_key' => 'emergency_fee_amount',
                'setting_value' => '500',
                'setting_type' => 'integer',
                'category' => 'billing',
                'description' => 'Emergency/after-hours additional fee',
            ],
            [
                'setting_key' => 'apply_emergency_fee_after',
                'setting_value' => '17:00',
                'setting_type' => 'time',
                'category' => 'billing',
                'description' => 'Apply emergency fee after this time',
            ],

            // Notification Settings
            [
                'setting_key' => 'appointment_reminder_1day',
                'setting_value' => 'true',
                'setting_type' => 'boolean',
                'category' => 'notification',
                'description' => 'Send reminder 1 day before',
            ],
            [
                'setting_key' => 'appointment_reminder_1hour',
                'setting_value' => 'true',
                'setting_type' => 'boolean',
                'category' => 'notification',
                'description' => 'Send reminder 1 hour before',
            ],

            // Appointment Settings
            [
                'setting_key' => 'max_advance_booking_days',
                'setting_value' => '30',
                'setting_type' => 'integer',
                'category' => 'appointment',
                'description' => 'How far in advance can book',
            ],
            [
                'setting_key' => 'allow_same_day_booking',
                'setting_value' => 'true',
                'setting_type' => 'boolean',
                'category' => 'appointment',
                'description' => 'Allow booking for same day',
            ],

            // Hospital Information
            [
                'setting_key' => 'hospital_name',
                'setting_value' => 'Guardiano Maternity & Children Clinic and Hospital',
                'setting_type' => 'string',
                'category' => 'general',
                'description' => 'Hospital display name',
            ],
            [
                'setting_key' => 'hospital_address',
                'setting_value' => 'Tacurong City, Sultan Kudarat',
                'setting_type' => 'string',
                'category' => 'general',
                'description' => 'Hospital address',
            ],
            [
                'setting_key' => 'hospital_phone',
                'setting_value' => '(064) 200-1234',
                'setting_type' => 'string',
                'category' => 'general',
                'description' => 'Hospital contact number',
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::create($setting);
        }
    }
}
