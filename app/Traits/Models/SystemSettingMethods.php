<?php
namespace App\Traits\Models;

trait SystemSettingMethods
{
    public static function get(string $key, $default = null)
    {
        $setting = self::where('setting_key', $key)->first();

        if (!$setting) return $default;

        return match($setting->setting_type) {
            'integer' => (int) $setting->setting_value,
            'boolean' => $setting->setting_value === 'true',
            'json' => json_decode($setting->setting_value, true),
            default => $setting->setting_value,
        };
    }

    public static function set(string $key, $value): ?self
    {
        $setting = self::where('setting_key', $key)->first();

        if ($setting) {
            $setting->setting_value = is_array($value) ? json_encode($value) : (string) $value;
            $setting->save();
        }

        return $setting;
    }
}
