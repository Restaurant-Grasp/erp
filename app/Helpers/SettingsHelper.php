<?php

namespace App\Helpers;

use App\Models\CrmSetting;
use Illuminate\Support\Facades\Cache;

class SettingsHelper
{
    /**
     * Get all company settings
     */
    public static function getCompanySettings()
    {
        return Cache::remember('company_settings', 3600, function () {
            return CrmSetting::where('category', 'company')->pluck('setting_value', 'setting_key')->toArray();
        });
    }

    /**
     * Get specific company setting
     */
    public static function getCompanySetting($key, $default = null)
    {
        $settings = self::getCompanySettings();
        return $settings[$key] ?? $default;
    }

    /**
     * Get company logo URL
     */
    public static function getCompanyLogo()
    {
        $logo = self::getCompanySetting('company_logo');
        return $logo ? asset('assets/' . $logo) : null;
    }

    /**
     * Get company sub logo URL
     */
    public static function getCompanySubLogo()
    {
        $logo = self::getCompanySetting('company_sub_logo');
        return $logo ? asset('assets/' . $logo) : null;
    }

    /**
     * Get complete company information
     */
    public static function getCompanyInfo()
    {
        $settings = self::getCompanySettings();
        
        return [
            'name' => $settings['company_name'] ?? '',
            'address' => $settings['company_address'] ?? '',
            'pincode' => $settings['company_pincode'] ?? '',
            'state' => $settings['company_state'] ?? '',
            'registration_number' => $settings['company_registration_number'] ?? '',
            'phone' => $settings['company_phone'] ?? '',
            'email' => $settings['company_email'] ?? '',
            'website' => $settings['company_website'] ?? '',
            'logo' => self::getCompanyLogo(),
            'sub_logo' => self::getCompanySubLogo(),
        ];
    }

    /**
     * Clear settings cache
     */
    public static function clearCache()
    {
        Cache::forget('company_settings');
        Cache::forget('general_settings');
        Cache::forget('all_settings');
    }

    /**
     * Get setting by category and key
     */
    public static function get($category, $key, $default = null)
    {
        return CrmSetting::getValue($category, $key, $default);
    }

    /**
     * Set setting by category and key
     */
    public static function set($category, $key, $value, $type = 'text', $description = null)
    {
        self::clearCache();
        return CrmSetting::setValue($category, $key, $value, $type, $description);
    }
}